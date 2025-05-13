<?php

namespace Mordheim\Spells\PrayersOfSigmar;

use Mordheim\Battle;
use Mordheim\Data\Spell;
use Mordheim\Fighter;
use Mordheim\Spells\BaseSpellProcessor;

class HammerOfSigmarProcessor extends BaseSpellProcessor
{
    public Spell $spell = Spell::HAMMER_OF_SIGMAR;

    public function __construct(
        public int $difficulty = 7
    )
    {

    }

    public function onPhaseShoot(Battle $battle, Fighter $caster): void
    {
        parent::rollSpellDiffused($battle, $caster);
    }

    public function onPhaseMagic(Battle $battle, Fighter $caster): ?bool
    {
        if ($caster->getState()->hasActiveSpell($this->spell))
            return null; // not allowed

        if (!parent::rollSpellApplied($battle, $caster))
            return false;

        $battle->addActiveSpell($caster, $this->spell);
        \Mordheim\BattleLogger::add("{$caster->getName()} получает +2 к силе и двойной урон в рукопашном бою (через {$this->spell->name}).");
        return true;
    }
}