<?php

namespace Mordheim\Classic\Rule;

use Mordheim\Classic\Battle;
use Mordheim\Classic\Equipment;
use Mordheim\Classic\Fighter;
use Mordheim\Classic\SpecialRule;
use Mordheim\Classic\Status;

class Injuries
{
    /**
     * Most warriors have a Wounds characteristic of 1, but
     * some have a value of 2 or more. If the target has more
     * than 1 wound then deduct 1 from his total each time
     * he suffers a wound. Make a note on the roster sheet.
     * So long as the model has at least 1 wound remaining
     * he may continue to fight.
     * As soon as a fighter’s Wounds are reduced to zero, roll
     * to determine the extent of his injuries. The player
     * who inflicted the wound rolls a D6 for the wound that
     * reduced the model to zero wounds and for every
     * wound the model receives after that. If a model
     * suffers several wounds in one turn, roll once for each
     * of them and apply the highest result.
     * 1-2 Knocked down (The force of the blow knocks the warrior down)
     * 3-4 Stunned (The target falls to the ground where he lies wounded and barely conscious)
     * 5-6 Out of action (The target has been badly hurt and falls to the ground unconscious. He takes no further
     * part in the game and is immediately removed from the battle)
     * @param Battle $battle
     * @param Fighter $source
     * @param Fighter $target
     * @param Equipment|null $weapon
     * @param bool $isCritical
     * @return bool
     */
    public static function roll(Battle $battle, Fighter $source, Fighter $target, ?Equipment $weapon = null, bool $isCritical = false): bool
    {
        // Critical: если woundRoll=6 и есть Critical, сразу OutOfAction
        if ($weapon && $isCritical) {
            \Mordheim\BattleLogger::add("[DEBUG][InjuryRoll] Крит! killFighter");
            $battle->killFighter($target);
            \Mordheim\BattleLogger::add("[DEBUG][InjuryRoll] После killFighter: статус цели=" . $target->getState()->getStatus()->name);
            return true;
        }
        $roll = \Mordheim\Dice::roll(6);
        \Mordheim\BattleLogger::add("Бросок на травму: $roll");
        \Mordheim\BattleLogger::add("[DEBUG][InjuryRoll] injuryRoll=$roll, isCritical=" . ($isCritical ? 'true' : 'false'));
        if ($roll < 1) $roll = 1;
        if ($roll > 6) $roll = 6;
        // Club/Mace/Hammer/Concussion: 1 — выбыл, 2 — knockdown, 3-6 — stun
        if ($weapon && $weapon->hasSpecialRule(SpecialRule::CONCUSSION)
            && !$target->hasSpecialRule(SpecialRule::NO_PAIN)
            && !$target->hasSpecialRule(SpecialRule::HARD_HEAD)
        ) {
            if ($roll == 1) {
                $battle->killFighter($target);
                \Mordheim\BattleLogger::add("[DEBUG][InjuryRoll] Club/Concussion: После killFighter: статус цели=" . $target->getState()->getStatus()->name);
            } elseif ($roll == 2) {
                $target->getState()->setStatus(Status::KNOCKED_DOWN);
                \Mordheim\BattleLogger::add("[DEBUG][InjuryRoll] Club/Concussion: После setStatus KNOCKED_DOWN: статус цели=" . $target->getState()->getStatus()->name);
            } else {
                $target->getState()->setStatus(AvoidStun::roll($target) ? Status::KNOCKED_DOWN : Status::STUNNED);
                \Mordheim\BattleLogger::add("[DEBUG][InjuryRoll] Club/Concussion: После setStatus STUNNED/KNOCKED_DOWN: статус цели=" . $target->getState()->getStatus()->name);
            }
            return true;
        }

        if ($target->hasSpecialRule(SpecialRule::NO_PAIN)) {
            if ($roll == 6) {
                $battle->killFighter($target);
                \Mordheim\BattleLogger::add("[DEBUG][InjuryRoll] NO_PAIN: После killFighter: статус цели=" . $target->getState()->getStatus()->name);
            }
            return true;
        }

        // Обычная таблица
        if ($roll == 1 || $roll == 2) {
            $target->getState()->setStatus(Status::KNOCKED_DOWN);
            \Mordheim\BattleLogger::add("[DEBUG][InjuryRoll] Обычная таблица: После setStatus KNOCKED_DOWN: статус цели=" . $target->getState()->getStatus()->name);
        } elseif ($roll == 3 || $roll == 4) {
            $target->getState()->setStatus(AvoidStun::roll($target) ? Status::KNOCKED_DOWN : Status::STUNNED);
            \Mordheim\BattleLogger::add("[DEBUG][InjuryRoll] Обычная таблица: После setStatus STUNNED/KNOCKED_DOWN: статус цели=" . $target->getState()->getStatus()->name);
        } else {
            if ($roll == 5 && $target->hasSpecialRule(SpecialRule::HARD_TO_KILL)) {
                $target->getState()->setStatus( Status::STUNNED);
                \Mordheim\BattleLogger::add("[DEBUG][InjuryRoll] HARD_TO_KILL proc: После setStatus STUNNED: статус цели=" . $target->getState()->getStatus()->name);
            } else {
                $battle->killFighter($target);
                \Mordheim\BattleLogger::add("[DEBUG][InjuryRoll] Обычная таблица: После killFighter: статус цели=" . $target->getState()->getStatus()->name);
            }
        }
        return true;
    }

    public static function rollIfNoWounds(Battle $battle, Fighter $source, Fighter $target, ?Equipment $weapon = null, bool $isCritical = false): bool
    {
        if ($target->getState()->getWounds() <= 0) {
            return self::roll($battle, $source, $target, $weapon, $isCritical);
        }
        return false;
    }
}