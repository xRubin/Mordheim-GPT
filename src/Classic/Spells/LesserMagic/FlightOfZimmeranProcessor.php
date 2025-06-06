<?php

namespace Mordheim\Classic\Spells\LesserMagic;

use Mordheim\Classic\Battle;
use Mordheim\Classic\CloseCombat;
use Mordheim\Classic\Fighter;
use Mordheim\Classic\Rule\Charge;
use Mordheim\Classic\Rule\Injuries;
use Mordheim\Classic\Rule\Move;
use Mordheim\Classic\Ruler;
use Mordheim\Classic\Spell;
use Mordheim\Classic\Spells\BaseSpellProcessor;
use Mordheim\Classic\Status;

class FlightOfZimmeranProcessor extends BaseSpellProcessor
{
    public Spell $spell = Spell::FLIGHT_OF_ZIMMERAN;

    public function __construct(
        public int $difficulty = 7
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

        $adj = Move::getNearestChargePosition($battle, $caster, $target);
        if (empty($adj)) {
            \Mordheim\BattleLogger::add("Нет свободных клеток рядом с врагом для {$this->spell->name}.");
            return null;
        }

        if (!parent::rollSpellApplied($battle, $caster))
            return false;

        $caster->getState()->setPosition($adj);
        \Mordheim\BattleLogger::add("{$caster->getName()} перемещается к {$target->getName()} с помощью {$this->spell->name}.");
        // Charge/Close Combat
        if ($target->getState()->getStatus() === Status::PANIC) {
            \Mordheim\BattleLogger::add("{$caster->getName()} наносит автоматический удар по бегущему {$target->getName()} ({$this->spell->name}).");
            $target->getState()->modifyWounds(-1); // Пример: 1 урон
            Injuries::rollIfNoWounds($battle, $caster, $target);
        } else {
            $battle->getActiveCombats()->add(new CloseCombat($caster, $target));
        }
        return true;
    }

    private function findEnemy(Battle $battle, Fighter $caster): ?Fighter
    {
        $enemies = $battle->getEnemiesFor($caster);
        if (empty($enemies))
            return null;
        usort($enemies, fn($a, $b) => Ruler::distance($caster, $a) <=> Ruler::distance($caster, $b));
        $closest = $enemies[0];
        if (Ruler::distance($caster, $closest) > 12)
            return null;
        return $closest;
    }
}