<?php

namespace Mordheim\Spells\PrayersOfSigmar;

use Mordheim\Battle;
use Mordheim\Data\Spell;
use Mordheim\Fighter;
use Mordheim\Ruler;
use Mordheim\Spells\BaseSpellProcessor;

class HealingHandProcessor extends BaseSpellProcessor
{
    public Spell $spell = Spell::HEALING_HAND;

    public function __construct(
        public int $difficulty = 5
    )
    {

    }

    public function onPhaseMagic(Battle $battle, Fighter $caster): ?bool
    {
        $target = $this->findWoundedAlly($battle, $caster);
        if (!$target) {
            \Mordheim\BattleLogger::add("Нет подходящих целей для {$this->spell->name} рядом с {$caster->getName()}.");
            return null;
        }

        if (!parent::rollSpellApplied($battle, $caster))
            return false;

        $target->getState()->setWounds($target->getWounds());
        if (in_array($target->getState()->getStatus(), [\Mordheim\Status::STUNNED, \Mordheim\Status::KNOCKED_DOWN])) {
            $target->getState()->setStatus(\Mordheim\Status::STANDING);
            \Mordheim\BattleLogger::add("{$target->getName()} встаёт после исцеления {$this->spell->name}.");
        }
        \Mordheim\BattleLogger::add("{$target->getName()} полностью исцелен {$this->spell->name}.");
        return true;
    }

    private function findWoundedAlly(Battle $battle, Fighter $caster): ?Fighter
    {
        foreach ([$caster, ...$battle->getAlliesFor($caster)] as $target) {
            if (Ruler::distance($caster, $target) > 2)
                continue;
            if ($target->getState()->getWounds() === $target->getWounds())
                continue;
            return $target;
        }
        return null;
    }
}