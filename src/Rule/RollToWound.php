<?php

namespace Mordheim\Rule;

use Mordheim\FighterInterface;
use Mordheim\Weapon;

class RollToWound
{
    /**
     * Обычный бросок на ранение и сейв
     * @param FighterInterface $source
     * @param FighterInterface $target
     * @param Weapon|null $weapon
     * @return array
     */
    public static function roll(FighterInterface $source, FighterInterface $target, ?Weapon $weapon): array
    {
        $attackerS = $source->getStrength($weapon);
        $resilientMod = $source->getEquipmentManager()->getResilientModifier($target);
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
            return ['success' => false, 'woundRoll' => $woundRoll];
        }
        return ['success' => true, 'woundRoll' => $woundRoll];
    }
}