<?php

namespace Mordheim\Classic\Spells\MagicOfTheHornedRat;

use Mordheim\Classic\Battle;
use Mordheim\Classic\Blank;
use Mordheim\Classic\EquipmentManager;
use Mordheim\Classic\Fighter;
use Mordheim\Classic\FighterAdvancement;
use Mordheim\Classic\FighterState;
use Mordheim\Classic\Spell;
use Mordheim\Classic\Spells\BaseSpellProcessor;
use Mordheim\Classic\Strategy\AggressiveStrategy;
use Mordheim\Dice;

class ChildrenOfTheHornedRatProcessor extends BaseSpellProcessor
{
    public Spell $spell = Spell::CHILDREN_OF_THE_HORNED_RAT;

    public function __construct(
        public int $difficulty = 0
    )
    {

    }

    public function onPhaseMagic(Battle $battle, Fighter $caster): ?bool
    {
        if ($caster->getState()->hasActiveSpell($this->spell))
            return null; // not allowed

        $cells = $this->findUnoccupiedCells($battle, $caster, 3);
        if (null === $cells) {
            \Mordheim\BattleLogger::add("Недостаточно свободных клеток для появления крыс ({$this->spell->name}).");
            return null;
        }

        if (!parent::rollSpellApplied($battle, $caster))
            return false;

        $battle->addActiveSpell($caster, $this->spell);
        $cnt = Dice::roll(3);

        \Mordheim\BattleLogger::add("{$caster->getName()} призывает {$cnt} крыс (через {$this->spell->name}).");
        $cells = array_slice($cells, 0, $cnt);
        foreach ($cells as $cell) {
            $battle->addFighter(new Fighter(
                Blank::SKAVEN_GIANT_RAT,
                FighterAdvancement::empty(),
                new EquipmentManager([]),
                new FighterState($cell, new AggressiveStrategy(), 1)
            ));
        }

        return true;
    }

    private function findUnoccupiedCells(Battle $battle, Fighter $caster, int $cnt): ?array
    {
        $result = [];
        for ($i = 0; $i < $cnt; $i++) {
            $cell = $battle->findUnoccupiedPosition($caster, 6);
            if (!$cell)
                return null;
            $result[] = $cell;
        }
        return $result;
    }
}