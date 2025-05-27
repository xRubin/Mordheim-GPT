<?php

namespace Mordheim\Classic\Rule;

use Mordheim\Classic\Equipment;
use Mordheim\Classic\Fighter;
use Mordheim\Classic\SpecialRule;

class RollToWound
{
    /**
     * Обычный бросок на ранение и сейв
     * @param Fighter $source
     * @param Fighter $target
     * @param Equipment $weapon
     * @return array
     */
    public static function roll(Fighter $source, Fighter $target, Equipment $weapon): array
    {
        $attackerS = $weapon->getStrength($source->getStrength());
        if ($source->hasSpecialRule(SpecialRule::PLUS_2_STRENGTH))
            $attackerS += 2;
        if ($source->hasSpecialRule(SpecialRule::PLUS_1_STRENGTH))
            $attackerS += 1;
        $resilientMod = (int)$source->getEquipmentManager()->hasSpecialRule(SpecialRule::RESILIENT);
        $defenderT = $target->getToughness() + $resilientMod;
        $toWound = 4;
        if ($attackerS > $defenderT) $toWound = 3;
        if ($attackerS >= 2 * $defenderT) $toWound = 2;
        if ($attackerS < $defenderT) $toWound = 5;
        if ($attackerS * 2 <= $defenderT) $toWound = 6;
        \Mordheim\BattleLogger::add("Сила атакующего: $attackerS, Стойкость защищающегося: $defenderT, модификатор Resilient: $resilientMod, итоговое значение для ранения: $toWound+");
        $woundRoll = \Mordheim\Dice::roll(6);
        \Mordheim\BattleLogger::add("{$source->getName()} бросает на ранение: $woundRoll (нужно $toWound+)");
        \Mordheim\BattleLogger::add("[DEBUG] attackerS={$attackerS}, defenderT={$defenderT}, resilientMod={$resilientMod}, toWound={$toWound}, woundRoll={$woundRoll}");
        if ($woundRoll < $toWound) {
            \Mordheim\BattleLogger::add("Ранение не удалось!");
            \Mordheim\BattleLogger::add("[DEBUG] result=false (woundRoll < toWound)");
            return ['success' => false, 'woundRoll' => $woundRoll, 'isCritical' => false];
        }
        $toCrit = $weapon->hasSpecialRule(SpecialRule::CRITICAL_HIT_ON_5) ? 5 : 6;
        return ['success' => true, 'woundRoll' => $woundRoll, 'isCritical' => $woundRoll >= $toCrit];
    }
}