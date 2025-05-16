<?php

namespace Mordheim\Spells\Necromancy;

use Mordheim\Battle;
use Mordheim\Fighter;
use Mordheim\Spell;
use Mordheim\Spells\BaseSpellProcessor;

class ReAnimationProcessor extends BaseSpellProcessor
{
    public Spell $spell = Spell::RE_ANIMATION;

    public function __construct(
        public int $difficulty = 5
    )
    {

    }

    public function onPhaseMagic(Battle $battle, Fighter $caster): ?bool
    {
        $target = $this->findAlly($battle, $caster);
        if (!$target) {
            \Mordheim\BattleLogger::add("Нет подходящей цели для {$this->spell->name}.");
            return null;
        }

        if (!parent::rollSpellApplied($battle, $caster))
            return false;

        $cell = $battle->findUnoccupiedPosition($caster, 6);
        if (!$cell) {
            \Mordheim\BattleLogger::add("Нет свободной клетки для появления зомби ({$this->spell->name}).");
            return null;
        }

        $target->getState()->setWounds($target->getWounds());
        $target->getState()->setStatus(\Mordheim\Status::STANDING);
        $target->getState()->setPosition($cell);
        \Mordheim\BattleLogger::add("{$caster->getName()} возвращает зомби {$target->getName()} в бой ({$this->spell->name}) на клетку [" . implode(",", $cell) . "].");
        return true;
    }

    private function findAlly(Battle $battle, Fighter $caster): ?Fighter
    {
        foreach ($battle->getFighters() as $target) {
            if ($target->getState()->isAlive())
                continue;
            if ($target->getBlank() !== \Mordheim\Blank::UNDEAD_ZOMBIE)
                continue;
            return $target;
        }
        return null;
    }
}