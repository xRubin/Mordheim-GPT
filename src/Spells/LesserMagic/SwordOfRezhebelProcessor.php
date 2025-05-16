<?php

namespace Mordheim\Spells\LesserMagic;

use Mordheim\Battle;
use Mordheim\Dice;
use Mordheim\Fighter;
use Mordheim\Spell;
use Mordheim\Spells\BaseSpellProcessor;

class SwordOfRezhebelProcessor extends BaseSpellProcessor
{
    public Spell $spell = Spell::SWORD_OF_REZHEBEL;

    public function __construct(
        public int $difficulty = 8
    )
    {

    }

    public function onPhaseRecovery(Battle $battle, Fighter $owner): void
    {
        $roll = Dice::roll(6) + Dice::roll(6);
        $success = $roll <= $owner->getLeadership();
        if ($success) {
            \Mordheim\BattleLogger::add("{$owner->getName()} проходит тест Лидерства {$this->spell->name} (бросок $roll против {$owner->getLeadership()}).");
        } else {
            $battle->removeActiveSpell($owner, $this->spell);
            \Mordheim\BattleLogger::add("{$owner->getName()} не прошёл тест Лидерства и теряет огненный меч {$this->spell->name} (бросок $roll против {$owner->getLeadership()}).");
        }
    }

    public function onPhaseMagic(Battle $battle, Fighter $caster): ?bool
    {
        if ($caster->getState()->hasActiveSpell($this->spell))
            return null; // not allowed

        if (!parent::rollSpellApplied($battle, $caster))
            return false;

        $battle->addActiveSpell($caster, $this->spell);
        \Mordheim\BattleLogger::add("{$caster->getName()} получает огненный меч (через {$this->spell->name}).");

        return true;
    }
}