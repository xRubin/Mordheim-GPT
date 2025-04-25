<?php

namespace Mordheim\Rule;

use Mordheim\Fighter;
use Mordheim\FighterState;
use Mordheim\Weapon;

class InjuryRoll
{
    /**
     * Таблица ранений Mordheim
     * @param Fighter $source
     * @param Fighter $target
     * @param Weapon|null $weapon
     * @param bool $isCritical
     * @return bool
     */
    public static function roll(Fighter $source, Fighter $target, ?Weapon $weapon = null, bool $isCritical = false): bool
    {
        $injuryRoll = \Mordheim\Dice::roll(6);
        $injuryMod = $source->equipmentManager->getInjuryModifier($weapon);
        $roll = $injuryRoll + $source->equipmentManager->getInjuryModifier($weapon);
        \Mordheim\BattleLogger::add("Бросок на травму: $roll (бросок: $injuryRoll, модификатор: $injuryMod)");
        if ($roll < 1) $roll = 1;
        if ($roll > 6) $roll = 6;
        // Critical: если woundRoll=6 и есть Critical, сразу OutOfAction
        if ($weapon && $isCritical) {
            $target->state = FighterState::OUT_OF_ACTION;
            $target->alive = false;
            $target->characteristics->wounds = 0;
            return true;
        }
        // Club/Mace/Hammer/Concussion: 1 — выбыл, 2 — knockdown, 3-6 — stun
        if ($weapon && $weapon->hasRule(\Mordheim\SpecialRule::CLUB) || $weapon->hasRule(\Mordheim\SpecialRule::CONCUSSION)) {
            if ($roll == 1) {
                $target->state = FighterState::OUT_OF_ACTION;
                $target->alive = false;
                $target->characteristics->wounds = 0;
            } elseif ($roll == 2) {
                $target->state = FighterState::KNOCKED_DOWN;
            } else {
                $target->state = $target->tryAvoidStun() ? FighterState::KNOCKED_DOWN : FighterState::STUNNED;
            }
            return true;
        }
        // Обычная таблица
        if ($roll == 1 || $roll == 2) {
            $target->state = FighterState::KNOCKED_DOWN;
        } elseif ($roll == 3 || $roll == 4 || $roll == 5) {
            $target->state = $target->tryAvoidStun() ? FighterState::KNOCKED_DOWN : FighterState::STUNNED;
        } else {
            $target->state = FighterState::OUT_OF_ACTION;
            $target->alive = false;
            $target->characteristics->wounds = 0;
        }
        return true;
    }
}