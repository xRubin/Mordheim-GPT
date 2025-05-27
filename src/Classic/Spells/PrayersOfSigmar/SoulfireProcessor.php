<?php

namespace Mordheim\Classic\Spells\PrayersOfSigmar;

use Mordheim\Classic\Battle;
use Mordheim\Classic\Equipment;
use Mordheim\Classic\Fighter;
use Mordheim\Classic\Rule\Attack;
use Mordheim\Classic\Rule\Injuries;
use Mordheim\Classic\Ruler;
use Mordheim\Classic\Spell;
use Mordheim\Classic\Spells\BaseSpellProcessor;
use Mordheim\Classic\Warband;

class SoulfireProcessor extends BaseSpellProcessor
{
    public Spell $spell = Spell::SOULFIRE;

    public function __construct(
        public int $difficulty = 9
    )
    {

    }

    public function onPhaseMagic(Battle $battle, Fighter $caster): ?bool
    {
        if (!count(iterator_to_array($this->findTargets($battle, $caster)))) {
            \Mordheim\BattleLogger::add("Нет подходящей цели для {$this->spell->name}.");
            return null;
        }

        if (!parent::rollSpellApplied($battle, $caster))
            return false;

        foreach ($this->findTargets($battle, $caster) as $target) {
            $weapon = in_array($target->getBlank()->getWarband(), [Warband::UNDEAD, Warband::CULT_OF_THE_POSSESSED]) ? Equipment::SOULFIRE_5 : Equipment::SOULFIRE_3;
            Attack::magic($battle, $caster, $target, $weapon, false);
            \Mordheim\BattleLogger::add("{$target->getName()} получает атаку {$weapon->name} урона от {$this->spell->name} (без сейва).");
            Injuries::rollIfNoWounds($battle, $caster, $target, null);
        }
        return true;
    }

    private function findTargets(Battle $battle, Fighter $caster): \Generator
    {
        foreach ($battle->getEnemiesFor($caster) as $target) {
            if (Ruler::distance($caster, $target) <= 4)
                yield $target;
        }
    }
}