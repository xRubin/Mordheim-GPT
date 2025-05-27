<?php

namespace Mordheim\Classic\Spells\Necromancy;

use Mordheim\Classic\Battle;
use Mordheim\Classic\Fighter;
use Mordheim\Classic\Rule\Injuries;
use Mordheim\Classic\Ruler;
use Mordheim\Classic\Spell;
use Mordheim\Classic\Spells\BaseSpellProcessor;
use Mordheim\Dice;

class SpellOfDoomProcessor extends BaseSpellProcessor
{
    public Spell $spell = Spell::SPELL_OF_DOOM;

    public function __construct(
        public int $difficulty = 9
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

        $roll = Dice::roll(6);
        if ($roll > $target->getStrength()) {
            \Mordheim\BattleLogger::add("{$target->getName()} не прошёл проверку силы ({$this->spell->name}), бросок: $roll.");
            Injuries::roll($battle, $caster, $target);
        } else {
            \Mordheim\BattleLogger::add("{$target->getName()} устоял против {$this->spell->name}, бросок: $roll.");
        }
        return true;
    }

    private function findEnemy(Battle $battle, Fighter $caster): ?Fighter
    {
        foreach ($battle->getEnemiesFor($caster) as $enemy) {
            if (Ruler::distance($caster, $enemy) > 12)
                continue;
            return $enemy;
        }
        return null;
    }
}