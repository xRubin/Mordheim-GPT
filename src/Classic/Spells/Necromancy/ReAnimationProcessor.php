<?php

namespace Mordheim\Classic\Spells\Necromancy;

use Mordheim\BattleLogger;
use Mordheim\Classic\Battle;
use Mordheim\Classic\Blank;
use Mordheim\Classic\Fighter;
use Mordheim\Classic\Spell;
use Mordheim\Classic\Spells\BaseSpellProcessor;
use Mordheim\Classic\Status;

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
            BattleLogger::add("Нет подходящей цели для {$this->spell->name}.");
            return null;
        }

        if (!parent::rollSpellApplied($battle, $caster))
            return false;

        $cell = $battle->findUnoccupiedPosition($caster, 6);
        if (!$cell) {
            BattleLogger::add("Нет свободной клетки для появления зомби ({$this->spell->name}).");
            return null;
        }

        $target->getState()->setWounds($target->getWounds());
        $target->getState()->setStatus(Status::STANDING);
        $target->getState()->setPosition($cell);
        BattleLogger::add("{$caster->getName()} возвращает зомби {$target->getName()} в бой ({$this->spell->name}) на клетку [" . implode(",", $cell) . "].");
        return true;
    }

    private function findAlly(Battle $battle, Fighter $caster): ?Fighter
    {
        foreach ($battle->getFighters() as $target) {
            if ($target->getState()->isAlive())
                continue;
            if ($target->getBlank() !== Blank::UNDEAD_ZOMBIE)
                continue;
            return $target;
        }
        return null;
    }
}