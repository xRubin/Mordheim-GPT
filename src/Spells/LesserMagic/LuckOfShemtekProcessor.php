<?php

namespace Mordheim\Spells\LesserMagic;

use Mordheim\Battle;
use Mordheim\Fighter;
use Mordheim\Spell;
use Mordheim\Spells\BaseSpellProcessor;

class LuckOfShemtekProcessor extends BaseSpellProcessor
{
    public Spell $spell = Spell::LUCK_OF_SHEMTEK;

    public function __construct(
        public int $difficulty = 6
    )
    {

    }

    public function onPhaseRecovery(Battle $battle, Fighter $owner): void
    {
        \Mordheim\BattleLogger::add("{$this->spell->name} спадает с {$owner->getName()}.");
        $battle->removeActiveSpell($owner, $this->spell);
    }

    public function onPhaseMagic(Battle $battle, Fighter $caster): ?bool
    {
        if ($caster->getState()->hasActiveSpell($this->spell))
            return null; // not allowed

        if (!parent::rollSpellApplied($battle, $caster))
            return false;

        $battle->addActiveSpell($caster, $this->spell);
        \Mordheim\BattleLogger::add("{$caster->getName()} получает реролл проваленных бросков (через {$this->spell->name}).");

        return true;
    }
}