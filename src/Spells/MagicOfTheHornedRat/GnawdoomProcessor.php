<?php

namespace Mordheim\Spells\MagicOfTheHornedRat;

use Mordheim\Battle;
use Mordheim\Data\Equipment;
use Mordheim\Data\Spell;
use Mordheim\Dice;
use Mordheim\Fighter;
use Mordheim\Rule\Attack;
use Mordheim\Ruler;
use Mordheim\Spells\BaseSpellProcessor;

class GnawdoomProcessor extends BaseSpellProcessor
{
    public Spell $spell = Spell::GNAWDOOM;
    public Equipment $weapon = Equipment::GNAWDOOM;

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

        $hits = Dice::roll(6) + Dice::roll(6); // 2D6
        \Mordheim\BattleLogger::add("{$target->getName()} получает {$hits} попаданий силой 1 от {$this->spell->name}.");
        for ($i = 0; $i < $hits; $i++) {
            Attack::magic($battle, $caster, $target, $this->weapon);
        }
        return true;
    }

    private function findEnemy(Battle $battle, Fighter $caster): ?Fighter
    {
        foreach ($battle->getEnemiesFor($caster) as $enemy) {
            if (Ruler::distance($caster, $enemy) > 8)
                continue;
            return $enemy;
        }
        return null;
    }
}