<?php

namespace Mordheim\Spells\ChaosRituals;

use Mordheim\Battle;
use Mordheim\Data\Spell;
use Mordheim\Fighter;
use Mordheim\Ruler;
use Mordheim\Spells\BaseSpellProcessor;

class WordOfPainProcessor extends BaseSpellProcessor
{
    public Spell $spell = Spell::DARK_BLOOD;

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
            $target->getState()->modifyWounds(-3); // S3, но просто -3 ран
            \Mordheim\BattleLogger::add("{$target->getName()} получает урон от {$this->spell->name} (без сейва).");
            if ($target->getState()->getWounds() <= 0) {
                $battle->killFighter($target);
            }
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