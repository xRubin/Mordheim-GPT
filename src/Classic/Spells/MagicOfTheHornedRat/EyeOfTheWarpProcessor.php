<?php

namespace Mordheim\Classic\Spells\MagicOfTheHornedRat;

use Mordheim\Classic\Battle;
use Mordheim\Classic\Fighter;
use Mordheim\Classic\Ruler;
use Mordheim\Classic\Spell;
use Mordheim\Classic\Spells\BaseSpellProcessor;

class EyeOfTheWarpProcessor extends BaseSpellProcessor
{
    public Spell $spell = Spell::EYE_OF_THE_WARP;

    public function __construct(
        public int $difficulty = 8
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
            $this->runFromCaster($battle, $caster, $target);
        }
        return true;
    }

    private function findTargets(Battle $battle, Fighter $caster): \Generator
    {
        foreach ($battle->getFighters() as $target) {
            if ($target === $caster)
                continue;
            if (!Ruler::isAdjacent($target, $caster))
                continue;
            yield $target;
        }
    }
}