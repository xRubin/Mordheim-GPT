<?php

namespace Mordheim\Spells\PrayersOfSigmar;

use Mordheim\Battle;
use Mordheim\Data\Spell;
use Mordheim\Fighter;
use Mordheim\Spells\BaseSpellProcessor;

class ArmourOfRighteousnessProcessor extends BaseSpellProcessor
{
    public Spell $spell = Spell::ARMOUR_OF_RIGHTEOUSNESS;

    public function __construct(
        public int $difficulty = 9
    )
    {

    }

    public function onPhaseShoot(Battle $battle, Fighter $caster): void
    {
        \Mordheim\BattleLogger::add("Эффект Armour of Righteousness спадает с {$caster->getName()} в фазе стрельбы.");
        $battle->removeActiveSpell($caster, $this->spell);
    }

    public function onPhaseMagic(Battle $battle, Fighter $caster): ?bool
    {
        if ($caster->getState()->hasActiveSpell($this->spell))
            return null; // not allowed

        if (!parent::rollSpellApplied($battle, $caster))
            return false;

        $battle->addActiveSpell($caster, $this->spell);
        \Mordheim\BattleLogger::add("{$caster->getName()} получает сейв 2+, внушает страх и становится невосприимчив к страху (через {$this->spell->name}).");
        return true;
    }
}