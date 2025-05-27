<?php

namespace Mordheim\Classic\Spells\Necromancy;

use Mordheim\Characteristics;
use Mordheim\Classic\Battle;
use Mordheim\Classic\Blank;
use Mordheim\Classic\Equipment;
use Mordheim\Classic\EquipmentManager;
use Mordheim\Classic\Fighter;
use Mordheim\Classic\FighterAdvancement;
use Mordheim\Classic\FighterState;
use Mordheim\Classic\Rule\Random;
use Mordheim\Classic\SpecialRule;
use Mordheim\Classic\Spell;
use Mordheim\Classic\Spells\BaseSpellProcessor;
use Mordheim\Classic\Strategy\AggressiveStrategy;

class SpellOfAwakeningProcessor extends BaseSpellProcessor
{
    public Spell $spell = Spell::SPELL_OF_AWAKENING;

    public function __construct(
        public int $difficulty = 0
    )
    {

    }

    public function onPhaseMagic(Battle $battle, Fighter $caster): ?bool
    {
        if ($caster->getState()->hasActiveSpell($this->spell))
            return null;

        $cell = $battle->findUnoccupiedPosition($caster, 6);
        if (!$cell) {
            \Mordheim\BattleLogger::add("Нет свободной клетки для появления зомби ({$this->spell->name}).");
            return null;
        }

        if (!parent::rollSpellApplied($battle, $caster))
            return false;

        $zombieHero = new Fighter(
            Random::fromArray([
                Blank::REIKLAND_CHAMPION,
                Blank::MARIENBURG_CHAMPION,
                Blank::MIDDENHEIM_CHAMPION,
                Blank::WITCH_HUNTER
            ]),
            new FighterAdvancement(
                new Characteristics(),
                [
                    SpecialRule::CAUSE_FEAR,
                    SpecialRule::MAY_NOT_RUN,
                    SpecialRule::IMMUNE_TO_PSYCHOLOGY,
                    SpecialRule::IMMUNE_TO_POISON,
                    SpecialRule::NO_PAIN,
                    SpecialRule::NO_BRAIN
                ]
            ),
            new EquipmentManager([Equipment::SWORD, Equipment::HEAVY_ARMOUR]),
            new FighterState($cell, new AggressiveStrategy(), 2)
        );
        $battle->addFighter($zombieHero);
        $battle->addActiveSpell($caster, $this->spell);
        \Mordheim\BattleLogger::add("{$caster->getName()} поднимает героя-зомби ({$zombieHero->getName()}) с помощью {$this->spell->name}!");
        return true;
    }
}