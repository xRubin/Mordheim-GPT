<?php

namespace Mordheim\Classic\Spells\LesserMagic;

use Mordheim\Classic\Battle;
use Mordheim\Classic\Fighter;
use Mordheim\Classic\Ruler;
use Mordheim\Classic\SpecialRule;
use Mordheim\Classic\Spell;
use Mordheim\Classic\Spells\BaseSpellProcessor;
use Mordheim\Classic\Warband;

class DreadOfAramarProcessor extends BaseSpellProcessor
{
    public Spell $spell = Spell::DREAD_OF_ARAMAR;

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

        $this->runFromCaster($battle, $caster, $target);

        return true;
    }

    private function findEnemy(Battle $battle, Fighter $caster): ?Fighter
    {
        foreach ($battle->getEnemiesFor($caster) as $enemy) {
            if (Ruler::distance($caster, $enemy) > 12)
                continue;
            if ($enemy->getBlank()->getWarband() == Warband::UNDEAD)
                continue;
            if ($enemy->hasSpecialRule(SpecialRule::FEARSOME))
                continue;
            return $enemy;
        }
        return null;
    }
}