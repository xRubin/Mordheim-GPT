<?php

namespace Mordheim\Classic\Spells\PrayersOfSigmar;

use Mordheim\Classic\Battle;
use Mordheim\Classic\Fighter;
use Mordheim\Classic\Spell;
use Mordheim\Classic\Spells\BaseSpellProcessor;
use Mordheim\Dice;

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