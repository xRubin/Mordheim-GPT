<?php

namespace Mordheim\Spells\Necromancy;

use Mordheim\Battle;
use Mordheim\Characteristics;
use Mordheim\Data\Blank;
use Mordheim\Data\Equipment;
use Mordheim\Data\Spell;
use Mordheim\EquipmentManager;
use Mordheim\Fighter;
use Mordheim\FighterAdvancement;
use Mordheim\FighterState;
use Mordheim\SpecialRule;
use Mordheim\Spells\BaseSpellProcessor;
use Mordheim\Strategy\AggressiveStrategy;

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
            Blank::REIKLAND_CHAMPION, // TODO other heroes rand?
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
            new EquipmentManager([Equipment::SWORD, Equipment::HEAVY_ARMOR]),
            new FighterState($cell, new AggressiveStrategy(), 2)
        );
        $battle->addFighter($zombieHero);
        $battle->addActiveSpell($caster, $this->spell);
        \Mordheim\BattleLogger::add("{$caster->getName()} поднимает героя-зомби ({$zombieHero->getName()}) с помощью {$this->spell->name}!");
        return true;
    }
}