<?php

namespace Mordheim\Classic\Spells\PrayersOfSigmar;

use Mordheim\Classic\Battle;
use Mordheim\Classic\Fighter;
use Mordheim\Classic\Ruler;
use Mordheim\Classic\Spell;
use Mordheim\Classic\Spells\BaseSpellProcessor;

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
        if (in_array($target->getState()->getStatus(), [\Mordheim\Classic\Status::STUNNED, \Mordheim\Classic\Status::KNOCKED_DOWN])) {
            $target->getState()->setStatus(\Mordheim\Classic\Status::STANDING);
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