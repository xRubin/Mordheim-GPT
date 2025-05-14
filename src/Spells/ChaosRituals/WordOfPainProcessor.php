<?php

namespace Mordheim\Spells\ChaosRituals;

use Mordheim\Battle;
use Mordheim\Data\Equipment;
use Mordheim\Data\Spell;
use Mordheim\EquipmentInterface;
use Mordheim\Fighter;
use Mordheim\Rule\Attack;
use Mordheim\Ruler;
use Mordheim\Spells\BaseSpellProcessor;

class WordOfPainProcessor extends BaseSpellProcessor
{
    public Spell $spell = Spell::WORD_OF_PAIN;
    public EquipmentInterface $weapon = Equipment::WORD_OF_PAIN;

    public function __construct(
        public int $difficulty = 7
    )
    {

    }

    public function onPhaseMagic(Battle $battle, Fighter $caster): ?bool
    {
        if (!count(iterator_to_array($this->findTargets($battle, $caster)))) {
            \Mordheim\BattleLogger::add("Нет подходящей цели для {$this->spell->name}.");
            return null;
        }

        if (!parent::rollSpellApplied($battle, $caster))
            return false;

        foreach ($this->findTargets($battle, $caster) as $target) {
            \Mordheim\BattleLogger::add("{$target->getName()} получает урон от {$this->spell->name} (без сейва).");
            Attack::magic($battle, $caster, $target, $this->weapon, false);
        }
        return true;
    }

    private function findTargets(Battle $battle, Fighter $caster): \Generator
    {
        $fighters = $battle->getFighters();
        foreach ($fighters as $target) {
            if (Ruler::distance($caster, $target) <= 3) {
                if ($target === $caster)
                    continue;
                yield $target;
            }
        }
    }
}