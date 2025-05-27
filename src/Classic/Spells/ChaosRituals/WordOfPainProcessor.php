<?php

namespace Mordheim\Classic\Spells\ChaosRituals;

use Mordheim\BattleLogger;
use Mordheim\Classic\Battle;
use Mordheim\Classic\Equipment;
use Mordheim\Classic\Fighter;
use Mordheim\Classic\Rule\Attack;
use Mordheim\Classic\Ruler;
use Mordheim\Classic\Spell;
use Mordheim\Classic\Spells\BaseSpellProcessor;

class WordOfPainProcessor extends BaseSpellProcessor
{
    public Spell $spell = Spell::WORD_OF_PAIN;
    public Equipment $weapon = Equipment::WORD_OF_PAIN;

    public function __construct(
        public int $difficulty = 7
    )
    {

    }

    public function onPhaseMagic(Battle $battle, Fighter $caster): ?bool
    {
        if (!count(iterator_to_array($this->findTargets($battle, $caster)))) {
            BattleLogger::add("Нет подходящей цели для {$this->spell->name}.");
            return null;
        }

        if (!parent::rollSpellApplied($battle, $caster))
            return false;

        foreach ($this->findTargets($battle, $caster) as $target) {
            BattleLogger::add("{$target->getName()} получает урон от {$this->spell->name} (без сейва).");
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