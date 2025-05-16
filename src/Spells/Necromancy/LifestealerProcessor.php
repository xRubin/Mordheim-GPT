<?php

namespace Mordheim\Spells\Necromancy;

use Mordheim\Battle;
use Mordheim\Blank;
use Mordheim\Data\Warband;
use Mordheim\Fighter;
use Mordheim\Rule\Injuries;
use Mordheim\Ruler;
use Mordheim\Spell;
use Mordheim\Spells\BaseSpellProcessor;

class LifestealerProcessor extends BaseSpellProcessor
{
    public Spell $spell = Spell::LIFESTEALER;

    public function __construct(
        public int $difficulty = 10
    )
    {

    }

    public function onPhaseMagic(Battle $battle, Fighter $caster): ?bool
    {
        $target = $this->findEnemy($battle, $caster);
        if (!$target) {
            \Mordheim\BattleLogger::add("Нет подходящей цели для {$this->spell->name}.");
            return null;
        }

        if (!parent::rollSpellApplied($battle, $caster))
            return false;

        $target->getState()->modifyWounds(-1);
        $caster->getState()->modifyWounds(+1);
        \Mordheim\BattleLogger::add("{$caster->getName()} высасывает жизнь у {$target->getName()} ({$this->spell->name}).");
        Injuries::rollIfNoWounds($battle, $caster, $target);
        return true;
    }

    private function findEnemy(Battle $battle, Fighter $caster): ?Fighter
    {
        foreach ($battle->getEnemiesFor($caster) as $enemy) {
            if (Ruler::distance($caster, $enemy) > 6)
                continue;
            if ($enemy->getBlank() === Blank::CULT_POSSESSED)
                continue;
            if ($enemy->getBlank()->getWarband() === Warband::UNDEAD)
                continue;
            return $enemy;
        }
        return null;
    }
}