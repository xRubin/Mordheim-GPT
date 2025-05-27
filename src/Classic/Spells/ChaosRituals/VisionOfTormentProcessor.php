<?php

namespace Mordheim\Classic\Spells\ChaosRituals;

use Mordheim\Classic\Battle;
use Mordheim\Classic\Fighter;
use Mordheim\Classic\Rule\AvoidStun;
use Mordheim\Classic\Ruler;
use Mordheim\Classic\Spell;
use Mordheim\Classic\Spells\BaseSpellProcessor;
use Mordheim\Classic\Status;

class VisionOfTormentProcessor extends BaseSpellProcessor
{
    public Spell $spell = Spell::VISION_OF_TORMENT;

    public function __construct(
        public int $difficulty = 10
    )
    {

    }

    public function onPhaseMagic(Battle $battle, Fighter $caster): ?bool
    {
        $target = $this->findEnemy($battle, $caster);
        if (!$target) {
            \Mordheim\BattleLogger::add("Нет подходящей цели для {$this->spell->name}.");
            return null;
        }

        if (!parent::rollSpellApplied($battle, $caster))
            return false;

        if (AvoidStun::roll($target)) {
            \Mordheim\BattleLogger::add("{$target->getName()} сбит с ног {$this->spell->name}.");
            $target->getState()->setStatus(Status::KNOCKED_DOWN);
        } else {
            \Mordheim\BattleLogger::add("{$target->getName()} оглушён {$this->spell->name}.");
            $target->getState()->setStatus(Status::STUNNED);
        }
        return true;
    }

    private function findEnemy(Battle $battle, Fighter $caster): ?Fighter
    {
        $enemies = $battle->getEnemiesFor($caster);
        if (empty($enemies))
            return null;
        // К ближайшему врагу
        usort($enemies, fn($a, $b) => Ruler::distance($caster, $a) <=> Ruler::distance($caster, $b));
        $closest = $enemies[0];
        if (Ruler::distance($caster, $closest) > 6)
            return null;

        if ($battle->getActiveCombats()->isFighterInCombat($caster)) {
            $inBase = array_filter($enemies, fn($enemy) => Ruler::isAdjacent($caster, $enemy));
            if (count($inBase)) {
                $closest = reset($inBase);
            }
        }
        return $closest;
    }
}