<?php

namespace Mordheim\Classic\Spells\Necromancy;

use Mordheim\Classic\Battle;
use Mordheim\Classic\Fighter;
use Mordheim\Classic\Spell;
use Mordheim\Classic\Spells\BaseSpellProcessor;

class DeathVisionProcessor extends BaseSpellProcessor
{
    public Spell $spell = Spell::DEATH_VISION;

    public function __construct(
        public int $difficulty = 6
    )
    {
    }

    public function onPhaseMagic(Battle $battle, Fighter $caster): ?bool
    {
        if ($caster->getState()->hasActiveSpell($this->spell))
            return null;

        if (!parent::rollSpellApplied($battle, $caster))
            return false;

        $battle->addActiveSpell($caster, $this->spell);
        \Mordheim\BattleLogger::add("{$caster->getName()} внушает страх до конца боя ({$this->spell->name}).");
        return true;
    }
}