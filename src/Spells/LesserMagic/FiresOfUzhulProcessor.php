<?php

namespace Mordheim\Spells\LesserMagic;

use Mordheim\Battle;
use Mordheim\Equipment;
use Mordheim\Fighter;
use Mordheim\Rule\Attack;
use Mordheim\Ruler;
use Mordheim\Spell;
use Mordheim\Spells\BaseSpellProcessor;

class FiresOfUzhulProcessor extends BaseSpellProcessor
{
    public Spell $spell = Spell::FIRES_OF_UZHUL;
    public Equipment $weapon = Equipment::FIRE_OF_UZHUL;

    public function __construct(
        public int $difficulty = 7
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

        Attack::magic($battle, $caster, $target, $this->weapon);
        return true;
    }

    private function findEnemy(Battle $battle, Fighter $caster): ?Fighter
    {
        foreach ($battle->getEnemiesFor($caster) as $enemy) {
            if (Ruler::distance($caster, $enemy) > 18)
                continue;
            return $enemy;
        }
        return null;
    }
}