<?php

namespace Mordheim\Rule;

use Mordheim\Battle;
use Mordheim\CloseCombat;
use Mordheim\Data\Equipment;
use Mordheim\EquipmentInterface;
use Mordheim\FighterInterface;
use Mordheim\Ruler;
use Mordheim\Slot;
use Mordheim\SpecialRule;
use Mordheim\Status;

class Attack
{
    /**
     * Выполнить ближний бой по Mordheim 1999 с поддержкой charge и CloseCombat
     * @param Battle $battle
     * @param FighterInterface $source
     * @param FighterInterface $target
     * @param CloseCombat|null $combat
     * @return bool true если нанесён урон, false если промах/парирование/сейв
     */
    public static function melee(Battle $battle, FighterInterface $source, FighterInterface $target, ?\Mordheim\CloseCombat $combat = null): bool
    {
        if (!self::canAttack($source, $target)) return false;
        if (!Ruler::isAdjacent($source->getState()->getPosition(), $target->getState()->getPosition())) return false;
        \Mordheim\BattleLogger::add("{$source->getName()} атакует melee {$target->getName()}!");
        \Mordheim\BattleLogger::add("[DEBUG] Оружия у атакующего: " . implode(',', array_map(fn($weapon) => $weapon->getName(), $source->getEquipmentManager()->getItemsBySlot(Slot::MELEE))));

        $success = false;
        for ($i = 0; $i < $source->getAttacks(); $i++) {
            $weapon = $source->getEquipmentManager()->getWeaponByAttackIdx(Slot::MELEE, $i);
            \Mordheim\BattleLogger::add("[DEBUG] Атака #" . ($i + 1) . ": до атаки wounds={$target->getState()->getWounds()}, state={$target->getState()->getStatus()->name}, weapon={$weapon->getName()}");

            if ($result = self::handleSpecialStates($battle, $source, $target, $weapon)) {
                $success = $result;
                continue;
            }

            if ($target->getState()->getWounds() <= 0) break;

            if (!self::rollToHit($source, $target, $weapon, $combat, $i, $hitRoll, $parried)) continue;
            if ($parried) continue;
            if (!self::rollToWoundAndSave($battle, $source, $target, $weapon, $success)) continue;

            if (!$target->getState()->getStatus()->isAlive()) break;
        }
        return $success;
    }

    /**
     * Стрельба по другому бойцу по правилам Mordheim
     * Учитывает Ballistic Skill, модификаторы (дальность, движение, укрытие, размер цели и т.д.)
     */
    public static function ranged(Battle $battle, FighterInterface $source, FighterInterface $target, bool $moved = false): bool
    {
        if (!self::canAttack($source, $target)) return false;
        $weapon = self::selectRangedWeapon($source, $target, $moved);
        if (!$weapon) return false;
        \Mordheim\BattleLogger::add("{$source->getName()} атакует ranged {$target->getName()}!");
        \Mordheim\BattleLogger::add("[DEBUG] Оружия у атакующего: " . implode(',', array_map(fn($weapon) => $weapon->getName(), $source->getEquipmentManager()->getItemsBySlot(Slot::MELEE))));

        [$toHit, $shots] = self::calculateRangedParams($battle, $source, $target, $weapon, $moved);

        $hit = false;
        for ($i = 0; $i < $shots; $i++) {
            \Mordheim\BattleLogger::add("[DEBUG] Атака #" . ($i + 1) . ": до атаки wounds={$target->getState()->getWounds()}, state={$target->getState()->getStatus()->name}, weapon={$weapon->getName()}");
            $roll = \Mordheim\Dice::roll(6);
            if ($roll === 6) {
                // Critical
                $target->getState()->decreaseWounds();
                if ($target->getState()->getWounds() <= 0) {
                    $battle->killFighter($target);
                }
                $hit = true;
                continue;
            }
            if ($roll < $toHit) continue;
            if (Dodge::roll($target)) continue;
            if (!self::tryArmorSaveRanged($source, $target, $weapon)) {
                $target->getState()->decreaseWounds();
                if ($target->getState()->getWounds() <= 0) {
                    $battle->killFighter($target);
                }
                $hit = true;
            }
        }
        return $hit;
    }

    /**
     * Обработка атак по целям в особых состояниях (STUNNED, KNOCKED_DOWN)
     * Возвращает true/false если атака обработана, null если нужно продолжать обычный бой
     */
    private static function handleSpecialStates(Battle $battle, FighterInterface $source, FighterInterface $target, $weapon): ?bool
    {
        if ($target->getState()->getStatus() === Status::STUNNED) {
            \Mordheim\BattleLogger::add("Атака по оглушённому (STUNNED): попадание и ранение автоматически успешны, сейв невозможен.");
            return Injuries::roll($battle, $source, $target, $weapon);
        }
        if ($target->getState()->getStatus() === Status::KNOCKED_DOWN) {
            \Mordheim\BattleLogger::add("Атака по сбитому с ног (KNOCKED_DOWN): попадание автоматически успешно.");
            $parried = false;
            StepAside::roll($target, $parried);
            $woundResult = RollToWound::roll($source, $target, $weapon);
            if (!$woundResult['success']) return false;
            if (self::tryArmorSave($source, $target, $weapon)) return false;
            return Injuries::roll($battle, $source, $target, $weapon, $woundResult['isCritical']);
        }
        return null;
    }

    /**
     * Бросок на попадание, обработка парирования
     * Возвращает true если попадание успешно и не парировано
     * $hitRoll и $parried передаются по ссылке
     */
    private static function rollToHit(FighterInterface $source, FighterInterface $target, $weapon, ?CloseCombat $combat, int $i, &$hitRoll, &$parried): bool
    {
        $attackerWS = $source->getWeaponSkill();
        $defenderWS = $target->getWeaponSkill();
        $toHitMod = $source->getHitModifier($weapon);
        $toHit = 4;
        if ($attackerWS > $defenderWS) $toHit = 3;
        if ($attackerWS >= 2 * $defenderWS) $toHit = 2;
        if ($attackerWS < $defenderWS) $toHit = 5;
        if ($attackerWS * 2 <= $defenderWS) $toHit = 6;
        $toHitBonus = ($combat && ($i === 0)) ? $combat->getBonus($source, CloseCombat::BONUS_TO_HIT) : 0;
        $toHit += $toHitMod + $toHitBonus;
        \Mordheim\BattleLogger::add("WS атакующего: $attackerWS, WS защищающегося: $defenderWS, модификаторы: Weapon {$toHitMod}, close combat {$toHitBonus}, итоговое значение для попадания: $toHit+");
        \Mordheim\BattleLogger::add("[DEBUG][Attack] Перед броском на попадание: оружие={$weapon->getName()}, атакующий={$source->getName()}, защищающийся={$target->getName()}");
        $hitRoll = \Mordheim\Dice::roll(6);
        \Mordheim\BattleLogger::add("[DEBUG][Attack] Бросок на попадание: $hitRoll");
        $parried = false;
        $defenderWeapon = $target->getEquipmentManager()->getMainWeapon(Slot::MELEE, Equipment::FIST);
        $canBeParried = $source->getEquipmentManager()->canBeParried($weapon, $defenderWeapon, $hitRoll);
        \Mordheim\BattleLogger::add("[DEBUG][attack] call canBeParried: attackerWeapon={$weapon->getName()}, defenderWeapon={$defenderWeapon->getName()}, hitRoll=$hitRoll");
        \Mordheim\BattleLogger::add("[DEBUG][attack] canBeParried returned: " . ($canBeParried ? 'true' : 'false'));
        if ($canBeParried) {
            $parryRoll = \Mordheim\Dice::roll(6);
            \Mordheim\BattleLogger::add("{$target->getName()} пытается парировать: $parryRoll против $hitRoll");
            if ($parryRoll >= $hitRoll) {
                $parried = true;
                \Mordheim\BattleLogger::add("Парирование удалось!");
            } else {
                \Mordheim\BattleLogger::add("Парирование не удалось.");
            }
        }
        StepAside::roll($target, $parried);
        if ($parried) {
            \Mordheim\BattleLogger::add("Атака парирована!");
            return false;
        }
        if ($hitRoll < $toHit) {
            \Mordheim\BattleLogger::add("Промах!");
            return false;
        }
        return true;
    }

    /**
     * Бросок на ранение, обработка критов, сэйва, уменьшения ран
     * Возвращает true если нанесён урон
     */
    private static function rollToWoundAndSave(Battle $battle, FighterInterface $source, FighterInterface $target, $weapon, &$success): bool
    {
        $woundResult = RollToWound::roll($source, $target, $weapon);
        \Mordheim\BattleLogger::add("[DEBUG][Attack] woundResult: " . json_encode($woundResult));
        if (!$woundResult['success']) return false;
        $armorSave = $target->getArmorSave($weapon);
        $armorSaveMod = $source->getEquipmentManager()->getArmorSaveModifier($weapon);
        $armorSave += $armorSaveMod;
        \Mordheim\BattleLogger::add("Сэйв защищающегося: $armorSave (модификатор: $armorSaveMod)");
        if ($woundResult['isCritical']) {
            \Mordheim\BattleLogger::add("[DEBUG][Attack] Критическое ранение! Перед InjuryRoll");
            $success = Injuries::roll($battle, $source, $target, $weapon, true);
            \Mordheim\BattleLogger::add("[DEBUG][Attack] После InjuryRoll: статус цели=" . $target->getState()->getStatus()->name);
            return true;
        } else {
            if ($armorSave > 0) {
                $saveRoll = \Mordheim\Dice::roll(6);
                \Mordheim\BattleLogger::add("{$target->getName()} бросает на сэйв: $saveRoll (нужно $armorSave+)");
                if ($saveRoll >= $armorSave) {
                    \Mordheim\BattleLogger::add("Сэйв удался! Урон не нанесён.");
                    return false;
                } else {
                    \Mordheim\BattleLogger::add("Сэйв не удался.");
                }
            }
            if ($weapon && $weapon->hasSpecialRule(SpecialRule::CONCUSSION)) {
                \Mordheim\BattleLogger::add("Особое правило: дубина/конкашн — всегда injury table");
                $success = Injuries::roll($battle, $source, $target, $weapon);
            } else {
                $target->getState()->decreaseWounds();
                \Mordheim\BattleLogger::add("У {$target->getName()} осталось {$target->getState()->getWounds()} ран(а/ий)");
                $success = true;
            }
            \Mordheim\BattleLogger::add("[DEBUG][Attack] После InjuryRoll: статус цели=" . $target->getState()->getStatus()->name);
        }
        \Mordheim\BattleLogger::add("[DEBUG] После атаки: wounds={$target->getState()->getWounds()}, state={$target->getState()->getStatus()->name}");
        return true;
    }

    /**
     * Попытка броска на сэйв. Возвращает true если сэйв удался
     */
    private static function tryArmorSave(FighterInterface $source, FighterInterface $target, $weapon): bool
    {
        $armorSave = $target->getArmorSave($weapon);
        if ($armorSave > 0) {
            $armorSaveMod = $source->getEquipmentManager()->getArmorSaveModifier($weapon);
            $armorSave += $armorSaveMod;
            \Mordheim\BattleLogger::add("Сэйв защищающегося: $armorSave (модификатор: $armorSaveMod)");
            $saveRoll = \Mordheim\Dice::roll(6);
            \Mordheim\BattleLogger::add("{$target->getName()} бросает на сэйв: $saveRoll (нужно $armorSave+)");
            \Mordheim\BattleLogger::add("[DEBUG] armorSave={$armorSave}, saveRoll={$saveRoll}");
            if ($saveRoll >= $armorSave) {
                \Mordheim\BattleLogger::add("Сэйв удался! Урон не нанесён.");
                \Mordheim\BattleLogger::add("[DEBUG] result=false (saveRoll >= armorSave)");
                return true;
            } else {
                \Mordheim\BattleLogger::add("Сэйв не удался.");
            }
        }
        return false;
    }

    public static function selectRangedWeapon(FighterInterface $source, FighterInterface $target, bool $moved): ?EquipmentInterface
    {
        $weapons = $source->getEquipmentManager()->getItemsBySlot(Slot::RANGED);
        if (!($weapon = reset($weapons))) return null;
        if (Ruler::distance($source->getState()->getPosition(), $target->getState()->getPosition()) > $weapon->getRange()) return null;
        if ($moved && $weapon->hasSpecialRule(SpecialRule::MOVE_OR_FIRE)) {
            \Mordheim\BattleLogger::add("{$source->getName()} не может стрелять из {$weapon->getName()} после движения (Move or Fire).");
            return null;
        }
        return $weapon;
    }

    public static function calculateRangedParams(Battle $battle, FighterInterface $source, FighterInterface $target, EquipmentInterface $weapon, bool $moved): array
    {
        $bs = $source->getBallisticSkill();
        $toHitBase = 7 - $bs;
        if ($toHitBase < 2) $toHitBase = 2;
        if ($toHitBase > 6) $toHitBase = 6;
        $shots = ($source->hasSpecialRule(SpecialRule::QUICK_SHOT) && !$moved) ? 2 : 1;
        $mod = 0;
        if (Ruler::distance($source->getState()->getPosition(), $target->getState()->getPosition()) > $weapon->getRange() / 2) $mod += 1;
        if ($moved) $mod += 1;
        if ($battle->hasObstacleBetween($source->getState()->getPosition(), $target->getState()->getPosition())) $mod += 1;
        if ($target->hasSpecialRule(SpecialRule::LARGE_TARGET)) $mod -= 1;
        $mod += $source->getHitModifier($weapon);
        $toHit = $toHitBase + $mod;
        if ($toHit > 6) $toHit = 6;
        if ($toHit < 2) $toHit = 2;
        return [$toHit, $shots];
    }

    public static function tryArmorSaveRanged(FighterInterface $source, FighterInterface $target, $weapon): bool
    {
        $armorSave = $target->getArmorSave($weapon);
        $saveRoll = $armorSave > 0 ? \Mordheim\Dice::roll(6) : 7;
        return $saveRoll >= $armorSave;
    }

    /**
     * Проверка, может ли атакующий атаковать и может ли цель быть атакована
     */
    private static function canAttack(FighterInterface $source, FighterInterface $target): bool
    {
        if (!$source->getState()->getStatus()->canAct()) {
            \Mordheim\BattleLogger::add("{$source->getName()} не может атаковать из-за состояния: {$source->getState()->getStatus()->value}.");
            return false;
        }
        if (!$target->getState()->getStatus()->isAlive()) {
            \Mordheim\BattleLogger::add("{$target->getName()} не может быть атакован: состояние {$target->getState()->getStatus()->value}.");
            return false;
        }
        return true;
    }
}