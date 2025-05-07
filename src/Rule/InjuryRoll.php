<?php

namespace Mordheim\Rule;

use Mordheim\Battle;
use Mordheim\FighterInterface;
use Mordheim\Status;
use Mordheim\Weapon;

class InjuryRoll
{
    /**
     * Таблица ранений Mordheim
     * @param Battle $battle
     * @param FighterInterface $source
     * @param FighterInterface $target
     * @param Weapon|null $weapon
     * @param bool $isCritical
     * @return bool
     */
    public static function roll(Battle $battle, FighterInterface $source, FighterInterface $target, ?Weapon $weapon = null, bool $isCritical = false): bool
    {
        $injuryRoll = \Mordheim\Dice::roll(6);
        $injuryMod = $source->getEquipmentManager()->getInjuryModifier($weapon);
        $roll = $injuryRoll + $source->getEquipmentManager()->getInjuryModifier($weapon);
        \Mordheim\BattleLogger::add("Бросок на травму: $roll (бросок: $injuryRoll, модификатор: $injuryMod)");
        \Mordheim\BattleLogger::add("[DEBUG][InjuryRoll] injuryRoll=$injuryRoll, injuryMod=$injuryMod, roll=$roll, isCritical=" . ($isCritical ? 'true' : 'false'));
        if ($roll < 1) $roll = 1;
        if ($roll > 6) $roll = 6;
        // Critical: если woundRoll=6 и есть Critical, сразу OutOfAction
        if ($weapon && $isCritical) {
            \Mordheim\BattleLogger::add("[DEBUG][InjuryRoll] Крит! killFighter");
            $battle->killFighter($target);
            \Mordheim\BattleLogger::add("[DEBUG][InjuryRoll] После killFighter: статус цели=" . $target->getState()->getStatus()->value);
            return true;
        }
        // Club/Mace/Hammer/Concussion: 1 — выбыл, 2 — knockdown, 3-6 — stun
        if ($weapon && $weapon->hasRule(\Mordheim\SpecialRule::CLUB) || $weapon->hasRule(\Mordheim\SpecialRule::CONCUSSION)) {
            if ($roll == 1) {
                $battle->killFighter($target);
                \Mordheim\BattleLogger::add("[DEBUG][InjuryRoll] Club/Concussion: После killFighter: статус цели=" . $target->getState()->getStatus()->value);
            } elseif ($roll == 2) {
                $target->getState()->setStatus(Status::KNOCKED_DOWN);
                \Mordheim\BattleLogger::add("[DEBUG][InjuryRoll] Club/Concussion: После setStatus KNOCKED_DOWN: статус цели=" . $target->getState()->getStatus()->value);
            } else {
                $target->getState()->setStatus(AvoidStun::roll($target) ? Status::KNOCKED_DOWN : Status::STUNNED);
                \Mordheim\BattleLogger::add("[DEBUG][InjuryRoll] Club/Concussion: После setStatus STUNNED/KNOKED_DOWN: статус цели=" . $target->getState()->getStatus()->value);
            }
            return true;
        }
        // Обычная таблица
        if ($roll == 1 || $roll == 2) {
            $target->getState()->setStatus(Status::KNOCKED_DOWN);
            \Mordheim\BattleLogger::add("[DEBUG][InjuryRoll] Обычная таблица: После setStatus KNOCKED_DOWN: статус цели=" . $target->getState()->getStatus()->value);
            $target->getState()->setStatus(AvoidStun::roll($target) ? Status::KNOCKED_DOWN : Status::STUNNED);
            \Mordheim\BattleLogger::add("[DEBUG][InjuryRoll] Обычная таблица: После setStatus STUNNED/KNOKED_DOWN: статус цели=" . $target->getState()->getStatus()->value);
        } elseif ($roll == 3 || $roll == 4 || $roll == 5) {
            $target->getState()->setStatus(AvoidStun::roll($target) ? Status::KNOCKED_DOWN : Status::STUNNED);
            \Mordheim\BattleLogger::add("[DEBUG][InjuryRoll] Обычная таблица: После setStatus STUNNED/KNOKED_DOWN: статус цели=" . $target->getState()->getStatus()->value);
        } else {
            $battle->killFighter($target);
            \Mordheim\BattleLogger::add("[DEBUG][InjuryRoll] Обычная таблица: После killFighter: статус цели=" . $target->getState()->getStatus()->value);
        }
        return true;
    }
}