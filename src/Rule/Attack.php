<?php

namespace Mordheim\Rule;

use Mordheim\CloseCombat;
use Mordheim\Fighter;
use Mordheim\FighterState;

class Attack
{
    /**
     * Ближний бой по правилам Mordheim (https://mordheimer.net/docs/rules/close-combat)
     * Учитывает WS, силу, брони, оружие, навыки, эффекты (оглушение, выбивание, крит, дубины, топоры и др.)
     * Возвращает true если нанесён урон, false если нет
     * Выполняет атаку по правилам Mordheim с учётом спецправил оружия и навыков.
     * @param Fighter $source
     * @param Fighter $target
     * @return bool true если нанесён урон, false если промах/парирование/сейв
     */
    /**
     * Выполнить ближний бой по Mordheim 1999 с поддержкой charge и CloseCombat
     * @param Fighter $source
     * @param Fighter $target
     * @param \Mordheim\CloseCombat|null $combat
     * @return bool
     */
    public static function apply(Fighter $source, Fighter $target, ?\Mordheim\CloseCombat $combat = null): bool
    {
        // Учет психологических и физических состояний
        $invalidStates = [
            FighterState::PANIC,
            FighterState::KNOCKED_DOWN,
            FighterState::STUNNED,
            FighterState::OUT_OF_ACTION
        ];
        if (in_array($source->state, $invalidStates, true)) {
            \Mordheim\BattleLogger::add("{$source->name} не может атаковать из-за состояния: {$source->state->value}.");
            return false;
        }
        if (in_array($target->state, [FighterState::OUT_OF_ACTION], true)) {
            \Mordheim\BattleLogger::add("{$target->name} не может быть атакован: состояние {$target->state->value}.");
            return false;
        }
        if (!$source->alive || !$target->alive || !$source->isAdjacent($target)) return false;
        \Mordheim\BattleLogger::add("{$source->name} атакует {$target->name}!");
        // Диагностика: вывести все оружия у бойца
        \Mordheim\BattleLogger::add("[DEBUG] Оружия у атакующего: " . implode(',', array_map(fn($w) => $w->name, $source->equipmentManager->getWeapons())));

        $success = false;
        for ($i = 0; $i < $source->getAttacks(); $i++) {
            $weapon = $source->equipmentManager->getWeaponByAttackIdx($i);
            \Mordheim\BattleLogger::add("[DEBUG] Атака #" . ($i + 1) . ": до атаки wounds={$target->characteristics->wounds}, state={$target->state->value}, weapon={$weapon?->name}");
            // Особые правила для атак по KNOCKED_DOWN/STUNNED
            if ($target->state === FighterState::STUNNED) {
                \Mordheim\BattleLogger::add("Атака по оглушённому (STUNNED): попадание и ранение автоматически успешны, сейв невозможен.");
                $success = \Mordheim\Rule\InjuryRoll::roll($source, $target, $weapon);
                continue;
            }
            if ($target->state === FighterState::KNOCKED_DOWN) {
                \Mordheim\BattleLogger::add("Атака по сбитому с ног (KNOCKED_DOWN): попадание автоматически успешно.");
                // Пропускаем бросок на попадание, но остальное — как обычно
                // Боец в состоянии "Knocked Down" не может парировать атаку
                if ($target->hasSkill('Step Aside') && !$parried) {
                    $stepAsideRoll = \Mordheim\Dice::roll(6);
                    \Mordheim\BattleLogger::add("{$target->name} использует Step Aside: $stepAsideRoll (нужно 5+)");
                    if ($stepAsideRoll >= 5) {
                        $parried = true;
                        \Mordheim\BattleLogger::add("Step Aside сработал!");
                    } else {
                        \Mordheim\BattleLogger::add("Step Aside не сработал.");
                    }
                }
                // Дальше обычный бросок на ранение и сейв
                $attackerS = $source->characteristics->strength + ($weapon ? $weapon->strength : 0);
                $resilientMod = $source->equipmentManager->getResilientModifier($target);
                $defenderT = $target->characteristics->toughness + $resilientMod;
                $toWound = 4;
                if ($attackerS > $defenderT) $toWound = 3;
                if ($attackerS >= 2 * $defenderT) $toWound = 2;
                if ($attackerS < $defenderT) $toWound = 5;
                if ($attackerS * 2 <= $defenderT) $toWound = 6;
                \Mordheim\BattleLogger::add("Сила атакующего: $attackerS, Стойкость защищающегося: $defenderT, модификатор Resilient: $resilientMod, итоговое значение для ранения: $toWound+");
                $woundRoll = \Mordheim\Dice::roll(6);
                \Mordheim\BattleLogger::add("{$source->name} бросает на ранение: $woundRoll (нужно $toWound+)");
                \Mordheim\BattleLogger::add("[DEBUG] attackerS={$attackerS}, defenderT={$defenderT}, resilientMod={$resilientMod}, toWound={$toWound}, woundRoll={$woundRoll}");
                if ($woundRoll < $toWound) {
                    \Mordheim\BattleLogger::add("Ранение не удалось!");
                    \Mordheim\BattleLogger::add("[DEBUG] result=false (woundRoll < toWound)");
                    continue;
                }
                $armorSave = $target->getArmorSave($weapon);
                $armorSaveMod = $source->equipmentManager->getArmorSaveModifier($weapon);
                $armorSave += $armorSaveMod;
                \Mordheim\BattleLogger::add("Сэйв защищающегося: $armorSave (модификатор: $armorSaveMod)");
                $saveRoll = null;
                if ($armorSave > 0) {
                    $saveRoll = \Mordheim\Dice::roll(6);
                    \Mordheim\BattleLogger::add("{$target->name} бросает на сэйв: $saveRoll (нужно $armorSave+)");
                    \Mordheim\BattleLogger::add("[DEBUG] armorSave={$armorSave}, saveRoll={$saveRoll}");
                    if ($saveRoll >= $armorSave) {
                        \Mordheim\BattleLogger::add("Сэйв удался! Урон не нанесён.");
                        \Mordheim\BattleLogger::add("[DEBUG] result=false (saveRoll >= armorSave)");
                        continue;
                    } else {
                        \Mordheim\BattleLogger::add("Сэйв не удался.");
                    }
                }
                $success = InjuryRoll::roll($source, $target, $weapon);
                \Mordheim\BattleLogger::add("[DEBUG] result=" . (string)$success . " (damage inflicted)");
                // Если rollInjury не перевёл в OUT_OF_ACTION, явно выставить wounds=0
                if ($target->state !== FighterState::OUT_OF_ACTION) {
                    $target->characteristics->wounds = 0;
                }
                return true;
            }

            // --- Обычный бой (по стандартным правилам) ---
            if ($target->characteristics->wounds <= 0) break;
            $attackerWS = $source->characteristics->weaponSkill;
            $defenderWS = $target->characteristics->weaponSkill;
            $toHitMod = $weapon ? $weapon->toHitModifier : 0;
            // 1. Roll to hit (WS vs WS)
            $toHit = 4;
            if ($attackerWS > $defenderWS) $toHit = 3;
            if ($attackerWS >= 2 * $defenderWS) $toHit = 2;
            if ($attackerWS < $defenderWS) $toHit = 5;
            if ($attackerWS * 2 <= $defenderWS) $toHit = 6;
            $toHitBonus = ($combat && ($i === 0)) ? $combat->getBonus($source, CloseCombat::BONUS_TO_HIT) : 0;
            $toHit += $toHitMod + $toHitBonus;
            \Mordheim\BattleLogger::add("WS атакующего: $attackerWS, WS защищающегося: $defenderWS, модификаторы: Weapon {$toHitMod}, close combat {$toHitBonus}, итоговое значение для попадания: $toHit+");
            $hitRoll = \Mordheim\Dice::roll(6);
            \Mordheim\BattleLogger::add("{$source->name} бросает на попадание: $hitRoll (нужно $toHit+)");
            $parried = false;
            $defenderWeapon = $target->equipmentManager->getMainWeapon();
            \Mordheim\BattleLogger::add("[DEBUG][attack] call canBeParried: attackerWeapon=" . ($weapon ? $weapon->name : 'NONE') . ", defenderWeapon=" . ($defenderWeapon ? $defenderWeapon->name : 'NONE') . ", hitRoll=$hitRoll");
            $canBeParried = $source->equipmentManager->canBeParried($weapon, $defenderWeapon, $hitRoll);
            \Mordheim\BattleLogger::add("[DEBUG][attack] canBeParried returned: " . ($canBeParried ? 'true' : 'false'));

            if ($canBeParried) {
                $parryRoll = \Mordheim\Dice::roll(6);
                \Mordheim\BattleLogger::add("{$target->name} пытается парировать: $parryRoll против $hitRoll");
                if ($parryRoll >= $hitRoll) {
                    $parried = true;
                    \Mordheim\BattleLogger::add("Парирование удалось!");
                } else {
                    \Mordheim\BattleLogger::add("Парирование не удалось.");
                }
            }
            if ($target->hasSkill('Step Aside') && !$parried) {
                $stepAsideRoll = \Mordheim\Dice::roll(6);
                \Mordheim\BattleLogger::add("{$target->name} использует Step Aside: $stepAsideRoll (нужно 5+)");
                if ($stepAsideRoll >= 5) {
                    $parried = true;
                    \Mordheim\BattleLogger::add("Step Aside сработал!");
                } else {
                    \Mordheim\BattleLogger::add("Step Aside не сработал.");
                }
            }
            if ($parried) {
                \Mordheim\BattleLogger::add("Атака парирована!");
                continue;
            }
            if ($hitRoll < $toHit) {
                \Mordheim\BattleLogger::add("Промах!");
                continue;
            }
            // 2. Roll to wound (S vs T)
            $attackerS = $source->characteristics->strength + ($weapon ? $weapon->strength : 0);
            $defenderT = $target->characteristics->toughness;
            $resilientMod = $source->equipmentManager->getResilientModifier($target);
            $attackerS -= $resilientMod;
            $toWound = 4;
            if ($attackerS > $defenderT) $toWound = 3;
            if ($attackerS >= 2 * $defenderT) $toWound = 2;
            if ($attackerS < $defenderT) $toWound = 5;
            if ($attackerS * 2 <= $defenderT) $toWound = 6;
            \Mordheim\BattleLogger::add("Сила атакующего: $attackerS, Стойкость защищающегося: $defenderT, модификатор Resilient: $resilientMod, итоговое значение для ранения: $toWound+");
            $woundRoll = \Mordheim\Dice::roll(6);
            \Mordheim\BattleLogger::add("{$source->name} бросает на ранение: $woundRoll (нужно $toWound+)");
            if ($woundRoll < $toWound) {
                \Mordheim\BattleLogger::add("Ранение не удалось!");
                continue;
            }
            $armorSave = $target->getArmorSave($weapon);
            $armorSaveMod = $source->equipmentManager->getArmorSaveModifier($weapon);
            $armorSave += $armorSaveMod;
            \Mordheim\BattleLogger::add("Сэйв защищающегося: $armorSave (модификатор: $armorSaveMod)");
            if ($armorSave > 0) {
                $saveRoll = \Mordheim\Dice::roll(6);
                \Mordheim\BattleLogger::add("{$target->name} бросает на сэйв: $saveRoll (нужно $armorSave+)");
                if ($saveRoll >= $armorSave) {
                    \Mordheim\BattleLogger::add("Сэйв удался! Урон не нанесён.");
                    continue;
                } else {
                    \Mordheim\BattleLogger::add("Сэйв не удался.");
                }
            }
            // Critical: если woundRoll==6, всегда крит (для совместимости с тестом)
            $isCritical = isset($woundRoll) && $woundRoll == 6;
            if ($isCritical) {
                $success = \Mordheim\Rule\InjuryRoll::roll($source, $target, $weapon, true);
            } else {
                if ($weapon && ($weapon->hasRule(\Mordheim\SpecialRule::CLUB) || $weapon->hasRule(\Mordheim\SpecialRule::CONCUSSION))) {
                    \Mordheim\BattleLogger::add("Особое правило: дубина/конкашн — всегда injury table");
                    $success = \Mordheim\Rule\InjuryRoll::roll($source, $target, $weapon);
                } else {
                    $target->characteristics->wounds -= 1;
                    \Mordheim\BattleLogger::add("У {$target->name} осталось {$target->characteristics->wounds} ран(а/ий)");
                    $success = true;
                }
            }
            \Mordheim\BattleLogger::add("[DEBUG] После атаки: wounds={$target->characteristics->wounds}, state={$target->state->value}");
            // Если боец выведен из строя, дальнейшие атаки не нужны
            if ($target->state === FighterState::OUT_OF_ACTION) break;
        }
        return $success;
    }
}