<?php

namespace Mordheim\Rule;

use Mordheim\Battle;
use Mordheim\CloseCombat;
use Mordheim\Data\Equipment;
use Mordheim\FighterInterface;
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
    public static function apply(Battle $battle, FighterInterface $source, FighterInterface $target, ?\Mordheim\CloseCombat $combat = null): bool
    {
        // Учет психологических и физических состояний
        if (!$source->getState()->getStatus()->canAct()) {
            \Mordheim\BattleLogger::add("{$source->getName()} не может атаковать из-за состояния: {$source->getState()->getStatus()->value}.");
            return false;
        }
        if (!$target->getState()->getStatus()->isAlive()) {
            \Mordheim\BattleLogger::add("{$target->getName()} не может быть атакован: состояние {$target->getState()->getStatus()->value}.");
            return false;
        }
        if (!$source->isAdjacent($target)) return false;
        \Mordheim\BattleLogger::add("{$source->getName()} атакует {$target->getName()}!");

        // Диагностика: вывести все оружия у бойца
        \Mordheim\BattleLogger::add("[DEBUG] Оружия у атакующего: " . implode(',', array_map(fn($weapon) => $weapon->getName(), $source->getEquipmentManager()->getItemsBySlot(Slot::MELEE))));

        $success = false;
        $parried = false;
        for ($i = 0; $i < $source->getAttacks(); $i++) {
            $weapon = $source->getEquipmentManager()->getWeaponByAttackIdx(Slot::MELEE, $i);
            \Mordheim\BattleLogger::add("[DEBUG] Атака #" . ($i + 1) . ": до атаки wounds={$target->getState()->getWounds()}, state={$target->getState()->getStatus()->value}, weapon={$weapon->getName()}");
            // Особые правила для атак по KNOCKED_DOWN/STUNNED
            if ($target->getState()->getStatus() === Status::STUNNED) {
                \Mordheim\BattleLogger::add("Атака по оглушённому (STUNNED): попадание и ранение автоматически успешны, сейв невозможен.");
                $success = InjuryRoll::roll($battle, $source, $target, $weapon);
                continue;
            }
            if ($target->getState()->getStatus() === Status::KNOCKED_DOWN) {
                \Mordheim\BattleLogger::add("Атака по сбитому с ног (KNOCKED_DOWN): попадание автоматически успешно.");
                // Пропускаем бросок на попадание, но остальное — как обычно
                // Боец в состоянии "Knocked Down" не может парировать атаку
                StepAside::roll($target, $parried);
                // Дальше обычный бросок на ранение и сейв
                $woundResult = RollToWound::roll($source, $target, $weapon);
                if (!$woundResult['success']) {
                    // Попадание всегда успешно, но урона нет
                    return false;
                }
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
                        return false;
                    } else {
                        \Mordheim\BattleLogger::add("Сэйв не удался.");
                    }
                }
                $success = InjuryRoll::roll($battle, $source, $target, $weapon, $woundResult['isCritical']);
                \Mordheim\BattleLogger::add("[DEBUG] result=" . (string)$success . " (damage inflicted)");
                return $success;
            }

            // --- Обычный бой (по стандартным правилам) ---
            if ($target->getState()->getWounds() <= 0) break;
            $attackerWS = $source->getWeaponSkill();
            $defenderWS = $target->getWeaponSkill();
            $toHitMod = $source->getHitModifier($weapon);
            // 1. Roll to hit (WS vs WS)
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
            \Mordheim\BattleLogger::add("[DEBUG][attack] call canBeParried: attackerWeapon={$weapon->getName()}, defenderWeapon={$defenderWeapon->getName()}, hitRoll=$hitRoll");
            $canBeParried = $source->getEquipmentManager()->canBeParried($weapon, $defenderWeapon, $hitRoll);
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
                continue;
            }
            if ($hitRoll < $toHit) {
                \Mordheim\BattleLogger::add("Промах!");
                continue;
            }
            // 2. Roll to wound (S vs T)
            $woundResult = RollToWound::roll($source, $target, $weapon);
            \Mordheim\BattleLogger::add("[DEBUG][Attack] woundResult: " . json_encode($woundResult));
            if (!$woundResult['success'])
                continue;

            $armorSave = $target->getArmorSave($weapon);
            $armorSaveMod = $source->getEquipmentManager()->getArmorSaveModifier($weapon);
            $armorSave += $armorSaveMod;
            \Mordheim\BattleLogger::add("Сэйв защищающегося: $armorSave (модификатор: $armorSaveMod)");
            if ($woundResult['isCritical']) {
                \Mordheim\BattleLogger::add("[DEBUG][Attack] Критическое ранение! Перед InjuryRoll");
                $success = InjuryRoll::roll($battle, $source, $target, $weapon, true);
                \Mordheim\BattleLogger::add("[DEBUG][Attack] После InjuryRoll: статус цели=" . $target->getState()->getStatus()->value);
               continue;
            } else {
                if ($armorSave > 0) {
                    $saveRoll = \Mordheim\Dice::roll(6);
                    \Mordheim\BattleLogger::add("{$target->getName()} бросает на сэйв: $saveRoll (нужно $armorSave+)");
                    if ($saveRoll >= $armorSave) {
                        \Mordheim\BattleLogger::add("Сэйв удался! Урон не нанесён.");
                        continue;
                    } else {
                        \Mordheim\BattleLogger::add("Сэйв не удался.");
                    }
                }
                if ($weapon && $weapon->hasSpecialRule(SpecialRule::CONCUSSION)) {
                    \Mordheim\BattleLogger::add("Особое правило: дубина/конкашн — всегда injury table");
                    $success = InjuryRoll::roll($battle, $source, $target, $weapon);
                } else {
                    $target->getState()->decreaseWounds();
                    \Mordheim\BattleLogger::add("У {$target->getName()} осталось {$target->getState()->getWounds()} ран(а/ий)");
                    $success = true;
                }
                \Mordheim\BattleLogger::add("[DEBUG][Attack] После InjuryRoll: статус цели=" . $target->getState()->getStatus()->value);
            }
            \Mordheim\BattleLogger::add("[DEBUG] После атаки: wounds={$target->getState()->getWounds()}, state={$target->getState()->getStatus()->value}");
            // Если боец выведен из строя, дальнейшие атаки не нужны
            if (!$target->getState()->getStatus()->isAlive()) break;
        }
        return $success;
    }
}