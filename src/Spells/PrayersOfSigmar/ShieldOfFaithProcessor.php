<?php

namespace Mordheim\Spells\PrayersOfSigmar;

use Mordheim\Battle;
use Mordheim\Data\Spell;
use Mordheim\Dice;
use Mordheim\Fighter;
use Mordheim\Spells\BaseSpellProcessor;

class ShieldOfFaithProcessor extends BaseSpellProcessor
{
    public Spell $spell = Spell::SHIELD_OF_FAITH;

    public function __construct(
        public int $difficulty = 6
    )
    {

    }

    public function onPhaseRecovery(Battle $battle, Fighter $caster): void
    {
        $roll = Dice::roll(6);
        if ($roll < 3) {
            \Mordheim\BattleLogger::add("{$this->spell->name} спадает с {$caster->getName()} (бросок: $roll)");
            $battle->removeActiveSpell($caster, $this->spell);
        } else {
            \Mordheim\BattleLogger::add("{$this->spell->name} остаётся на {$caster->getName()} (бросок: $roll)");
        }
    }

    public function onPhaseMagic(Battle $battle, Fighter $caster): ?bool
    {
        if ($caster->getState()->hasActiveSpell($this->spell))
            return null; // not allowed

        if (!parent::rollSpellApplied($battle, $caster))
            return false;

        $battle->addActiveSpell($caster, $this->spell);
        \Mordheim\BattleLogger::add("{$caster->getName()} становится невосприимчив к заклинаниям (через {$this->spell->name}).");
        return true;
    }
}