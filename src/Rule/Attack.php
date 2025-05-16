<?php

namespace Mordheim\Rule;

use Mordheim\Battle;
use Mordheim\CloseCombat;
use Mordheim\Equipment;
use Mordheim\Fighter;
use Mordheim\Ruler;
use Mordheim\Slot;
use Mordheim\SpecialRule;
use Mordheim\Spell;
use Mordheim\Status;

class Attack
{
    /**
     * Выполнить ближний бой по Mordheim 1999 с поддержкой charge и CloseCombat
     * @param Battle $battle
     * @param Fighter $source
     * @param Fighter $target
     * @param CloseCombat|null $combat
     * @return bool true если нанесён урон, false если промах/парирование/сейв
     */
    public static function melee(Battle $battle, Fighter $source, Fighter $target, ?\Mordheim\CloseCombat $combat = null): bool
    {
        if (!self::canAttack($source, $target)) return false;
        if (!Ruler::isAdjacent($source, $target)) return false;
        \Mordheim\BattleLogger::add("{$source->getName()} атакует melee {$target->getName()}!");
        \Mordheim\BattleLogger::add("[DEBUG] Оружия у атакующего: " . implode(',', array_map(fn($weapon) => $weapon->getName(), $source->getEquipmentManager()->getItemsBySlot(Slot::MELEE))));

        $success = false;
        for ($i = 0; $i < $source->getAttacks(); $i++) {
            $weapon = $source->getWeaponByAttackIdx(Slot::MELEE, $i);
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
    public static function ranged(Battle $battle, Fighter $source, Fighter $target, bool $moved = false): bool
    {
        if (!self::canAttack($source, $target)) return false;
        $weapon = self::selectRangedWeapon($source, $target, $moved);
        if (!$weapon) return false;
        \Mordheim\BattleLogger::add("{$source->getName()} атакует ranged {$target->getName()}!");
        \Mordheim\BattleLogger::add("[DEBUG] Оружия у атакующего: " . implode(',', array_map(fn($weapon) => $weapon->getName(), $source->getEquipmentManager()->getItemsBySlot(Slot::RANGED))));

        [$toHit, $shots] = self::calculateRangedParams($battle, $source, $target, $weapon, $moved);

        $hit = false;
        for ($i = 0; $i < $shots; $i++) {
            if (!$target->getState()->getStatus()->isAlive()) break;
            \Mordheim\BattleLogger::add("[DEBUG] Атака #" . ($i + 1) . ": до атаки wounds={$target->getState()->getWounds()}, state={$target->getState()->getStatus()->name}, weapon={$weapon->getName()}");
            $roll = \Mordheim\Dice::roll(6);
            \Mordheim\BattleLogger::add("[DEBUG] ToHit: {$toHit} Roll: {$roll}");
            if ($roll >= $toHit && $source->getState()->hasActiveSpell(Spell::SORCERERS_CURSE)) {
                // re-roll any successful to hit rolls
                $roll = \Mordheim\Dice::roll(6);
                \Mordheim\BattleLogger::add("[REROLL] ToHit: {$toHit} Roll: {$roll}");
            }
            if ($roll === 6) {
                \Mordheim\BattleLogger::add("[DEBUG] Critical!");
                $target->getState()->modifyWounds(-1);
                Injuries::rollIfNoWounds($battle, $source, $target, $weapon, true);
                $hit = true;
                continue;
            }
            if ($roll < $toHit) {
                \Mordheim\BattleLogger::add("[DEBUG] Промах");
                continue;
            }
            if (Dodge::roll($target)) {
                \Mordheim\BattleLogger::add("[DEBUG] Dodge");
                continue;
            }
            if (!self::tryArmourSaveRanged($source, $target, $weapon)) {
                \Mordheim\BattleLogger::add("[DEBUG] Попадание");
                $target->getState()->modifyWounds(-1);
                Injuries::rollIfNoWounds($battle, $source, $target, $weapon);
                $hit = true;
            }
        }
        return $hit;
    }

    /**
     * Расчет повреждения от магического снаряда с учетом силы и модификатора сейва цели
     */
    public static function magic(Battle $battle, Fighter $caster, Fighter $target, Equipment $weapon, $useSave = true): bool
    {
        if (!$target->getState()->getStatus()->isAlive())
            return false;

        $woundResult = RollToWound::roll($caster, $target, $weapon);
        if (!$woundResult['success']) {
            \Mordheim\BattleLogger::add("{$target->getName()} не был ранен заклинанием {$weapon->name}.");
            return true;
        }

        if ($useSave) {
            $armourSave = $target->getArmourSave($weapon);
            if ($armourSave <= 0) {
                $target->getState()->modifyWounds(-1);
                \Mordheim\BattleLogger::add("{$target->getName()} получает 1 ранение от {$weapon->name}.");
                Injuries::rollIfNoWounds($battle, $caster, $target, $weapon);
                return true;
            }
            $attackerStrength = $weapon->getStrength($caster->getStrength());
            $strengthMod = self::getStrengthArmourSaveModifier($attackerStrength);
            $armourSaveMod = $target->getEquipmentManager()->getArmourSaveModifier($weapon);
            $armourSave = $armourSave - $strengthMod + $armourSaveMod;
            if ($armourSave > 0) {
                $saveRoll = \Mordheim\Dice::roll(6);
                \Mordheim\BattleLogger::add("{$target->getName()} бросает на сэйв: $saveRoll (нужно $armourSave+)");
                if ($saveRoll >= $armourSave) {
                    \Mordheim\BattleLogger::add("Сэйв удался! Урон не нанесён.");
                    return true;
                } else {
                    \Mordheim\BattleLogger::add("Сэйв не удался.");
                }
            }
        }

        $target->getState()->modifyWounds(-1);
        \Mordheim\BattleLogger::add("{$target->getName()} получает 1 ранение от {$weapon->name}.");
        Injuries::rollIfNoWounds($battle, $caster, $target, $weapon);
        return true;
    }

    /**
     * Обработка атак по целям в особых состояниях (STUNNED, KNOCKED_DOWN)
     * Возвращает true/false если атака обработана, null если нужно продолжать обычный бой
     */
    private static function handleSpecialStates(Battle $battle, Fighter $source, Fighter $target, $weapon): ?bool
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
            if (self::tryArmourSave($target, $weapon)) return false;
            return Injuries::roll($battle, $source, $target, $weapon, $woundResult['isCritical']);
        }
        return null;
    }

    /**
     * Бросок на попадание, обработка парирования
     * Возвращает true если попадание успешно и не парировано
     * $hitRoll и $parried передаются по ссылке
     */
    private static function rollToHit(Fighter $source, Fighter $target, $weapon, ?CloseCombat $combat, int $i, &$hitRoll, &$parried): bool
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
        if ($hitRoll >= 4 && $source->getState()->hasActiveSpell(Spell::SORCERERS_CURSE)) {
            // re-roll any successful to hit rolls
            $hitRoll = \Mordheim\Dice::roll(6);
            \Mordheim\BattleLogger::add("[REROLL] {$target->getName()} Бросок на попадание: $hitRoll");
        }
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
    public static function rollToWoundAndSave(Battle $battle, Fighter $source, Fighter $target, $weapon, &$success): bool
    {
        $woundResult = RollToWound::roll($source, $target, $weapon);
        \Mordheim\BattleLogger::add("[DEBUG][Attack] woundResult: " . json_encode($woundResult));
        if (!$woundResult['success']) return false;
        $armourSave = $target->getArmourSave($weapon);
        if ($armourSave <= 0) {
            // Нет брони — урон проходит автоматически
            $target->getState()->modifyWounds(
                $source->hasSpecialRule(SpecialRule::DOUBLE_DAMAGE) ? -2 : -1
            );
            \Mordheim\BattleLogger::add("У {$target->getName()} осталось {$target->getState()->getWounds()} ран(а/ий)");
            Injuries::rollIfNoWounds($battle, $source, $target, $weapon);
            $success = true;
            return true;
        }
        $attackerStrength = $weapon->getStrength($source->getStrength());
        $strengthMod = self::getStrengthArmourSaveModifier($attackerStrength);
        $armourSaveMod = $target->getEquipmentManager()->getArmourSaveModifier($weapon); // ???
        $armourSave = $armourSave - $strengthMod + $armourSaveMod;
        \Mordheim\BattleLogger::add("Сэйв защищающегося: $armourSave (модификатор по силе: $strengthMod, спецправила: $armourSaveMod)");
        if ($woundResult['isCritical']) {
            \Mordheim\BattleLogger::add("[DEBUG][Attack] Критическое ранение! Перед InjuryRoll");
            $success = Injuries::roll($battle, $source, $target, $weapon, true);
            \Mordheim\BattleLogger::add("[DEBUG][Attack] После InjuryRoll: статус цели=" . $target->getState()->getStatus()->name);
            return true;
        } else {
            if ($armourSave > 0) {
                $saveRoll = \Mordheim\Dice::roll(6);
                \Mordheim\BattleLogger::add("{$target->getName()} бросает на сэйв: $saveRoll (нужно $armourSave+)");
                if ($saveRoll >= $armourSave && $target->getState()->hasActiveSpell(Spell::SORCERERS_CURSE)) {
                    // re-roll any successful armour saves
                    $saveRoll = \Mordheim\Dice::roll(6);
                    \Mordheim\BattleLogger::add("[REROLL] {$target->getName()} бросает на сэйв: $saveRoll (нужно $armourSave+)");
                }
                if ($saveRoll >= $armourSave) {
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
                $target->getState()->modifyWounds(
                    $source->hasSpecialRule(SpecialRule::DOUBLE_DAMAGE) ? -2 : -1
                );
                \Mordheim\BattleLogger::add("У {$target->getName()} осталось {$target->getState()->getWounds()} ран(а/ий)");
                Injuries::rollIfNoWounds($battle, $source, $target, $weapon);
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
    private static function tryArmourSave(Fighter $target, $weapon): bool
    {
        $armourSave = $target->getArmourSave($weapon);
        if ($armourSave > 0) {
            $armourSaveMod = $target->getEquipmentManager()->getArmourSaveModifier($weapon);
            $armourSave += $armourSaveMod;
            \Mordheim\BattleLogger::add("Сэйв защищающегося: $armourSave (модификатор: $armourSaveMod)");
            $saveRoll = \Mordheim\Dice::roll(6);
            \Mordheim\BattleLogger::add("{$target->getName()} бросает на сэйв: $saveRoll (нужно $armourSave+)");
            \Mordheim\BattleLogger::add("[DEBUG] armourSave={$armourSave}, saveRoll={$saveRoll}");
            if ($saveRoll >= $armourSave && $target->getState()->hasActiveSpell(Spell::SORCERERS_CURSE)) {
                // re-roll any successful armour saves
                $saveRoll = \Mordheim\Dice::roll(6);
                \Mordheim\BattleLogger::add("[REROLL] {$target->getName()} бросает на сэйв: $saveRoll (нужно $armourSave+)");
            }
            if ($saveRoll >= $armourSave) {
                \Mordheim\BattleLogger::add("Сэйв удался! Урон не нанесён.");
                \Mordheim\BattleLogger::add("[DEBUG] result=false (saveRoll >= armourSave)");
                return true;
            } else {
                \Mordheim\BattleLogger::add("Сэйв не удался.");
            }
        }
        return false;
    }

    public static function selectRangedWeapon(Fighter $source, Fighter $target, bool $moved): ?Equipment
    {
        $weapons = $source->getEquipmentManager()->getItemsBySlot(Slot::RANGED);
        if (!($weapon = reset($weapons))) return null;
        if (Ruler::distance($source, $target) > $weapon->getRange()) return null;
        if ($moved && $weapon->hasSpecialRule(SpecialRule::MOVE_OR_FIRE)) {
            \Mordheim\BattleLogger::add("{$source->getName()} не может стрелять из {$weapon->getName()} после движения (Move or Fire).");
            return null;
        }
        return $weapon;
    }

    public static function calculateRangedParams(Battle $battle, Fighter $source, Fighter $target, Equipment $weapon, bool $moved): array
    {
        $bs = $source->getBallisticSkill();
        $toHitBase = 7 - $bs;
        if ($toHitBase < 2) $toHitBase = 2;
        if ($toHitBase > 6) $toHitBase = 6;
        $shots = ($source->hasSpecialRule(SpecialRule::QUICK_SHOT) && !$moved) ? 2 : 1;
        $mod = 0;
        if (Ruler::distance($source, $target) > $weapon->getRange() / 2) $mod += 1;
        if ($moved) $mod += 1;
        if ($battle->hasObstacleBetween($source->getState()->getPosition(), $target->getState()->getPosition())) $mod += 1;
        if ($target->hasSpecialRule(SpecialRule::LARGE_TARGET)) $mod -= 1;
        $mod += $source->getHitModifier($weapon);
        $toHit = $toHitBase + $mod;
        if ($toHit > 6) $toHit = 6;
        if ($toHit < 2) $toHit = 2;
        return [$toHit, $shots];
    }

    public static function tryArmourSaveRanged(Fighter $source, Fighter $target, ?Equipment $weapon): bool
    {
        $armourSave = $target->getArmourSave($weapon);
        if ($armourSave <= 0) {
            return false; // Нет сейва — урон проходит!
        }
        $attackerStrength = $weapon ? $weapon->getStrength($source->getStrength()) : $source->getStrength();
        $strengthMod = self::getStrengthArmourSaveModifier($attackerStrength);
        $armourSaveMod = $source->getEquipmentManager()->getArmourSaveModifier($weapon);
        $armourSave = $armourSave - $strengthMod + $armourSaveMod;
        if ($armourSave <= 0) {
            return false; // Нет сейва — урон проходит!
        }
        $saveRoll = \Mordheim\Dice::roll(6);
        if ($saveRoll >= $armourSave && $target->getState()?->hasActiveSpell(Spell::SORCERERS_CURSE)) {
            // re-roll any successful armour saves
            $saveRoll = \Mordheim\Dice::roll(6);
            \Mordheim\BattleLogger::add("[REROLL] {$target->getName()} бросает на сэйв: $saveRoll (нужно $armourSave+)");
        }
        \Mordheim\BattleLogger::add("ArmourSave: {$armourSave}, SaveRoll: {$saveRoll}.");
        return $saveRoll >= $armourSave;
    }

    /**
     * Проверка, может ли атакующий атаковать и может ли цель быть атакована
     */
    public static function canAttack(Fighter $source, Fighter $target): bool
    {
        if (!$source->getState()->getStatus()->canAct()) {
            \Mordheim\BattleLogger::add("{$source->getName()} не может атаковать из-за состояния: {$source->getState()->getStatus()->name}.");
            return false;
        }
        if (!$target->getState()->getStatus()->isAlive()) {
            \Mordheim\BattleLogger::add("{$target->getName()} не может быть атакован: состояние {$target->getState()->getStatus()->name}.");
            return false;
        }
        return true;
    }

    /**
     * Модификатор сейва по силе удара (таблица Mordheim)
     */
    public static function getStrengthArmourSaveModifier(int $strength): int
    {
        if ($strength <= 3) return 0;
        if ($strength == 4) return -1;
        if ($strength == 5) return -2;
        if ($strength == 6) return -3;
        if ($strength == 7) return -4;
        if ($strength == 8) return -5;
        return -6;
    }
}