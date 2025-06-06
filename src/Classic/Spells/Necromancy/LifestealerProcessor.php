<?php

namespace Mordheim\Classic\Spells\Necromancy;

use Mordheim\Classic\Battle;
use Mordheim\Classic\Blank;
use Mordheim\Classic\Fighter;
use Mordheim\Classic\Rule\Injuries;
use Mordheim\Classic\Ruler;
use Mordheim\Classic\Spell;
use Mordheim\Classic\Spells\BaseSpellProcessor;
use Mordheim\Classic\Warband;

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