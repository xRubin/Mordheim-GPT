<?php

namespace Mordheim\Strategy;

use Mordheim\Battle;
use Mordheim\Exceptions\FighterAbnormalStateException;
use Mordheim\Fighter;
use Mordheim\FighterState;
use Mordheim\GameField;

class CarefulStrategy extends BaseBattleStrategy implements BattleStrategy
{
    public function movePhase(Battle $battle, Fighter $fighter, array $enemies, GameField $field): void
    {
        try {
            $fighter->state->validate();
        } catch (FighterAbnormalStateException $e) {
            if ($e->getState() === FighterState::KNOCKED_DOWN) {
                $this->movedThisTurn = \Mordheim\Rule\StandUp::apply($fighter);
            } else {
                \Mordheim\BattleLogger::add("{$fighter->name} не может действовать из-за состояния {$fighter->state->value}.");
                return;
            }
        }

        if (empty($enemies)) return;
        $target = $this->getNearestEnemy($fighter, $enemies);
        if (!$target) return;
        $canAct = $this->canActAgainst($fighter, $target, $field);
        if (!$canAct) return;
        if (!$fighter->isAdjacent($target)) {
            // Держит дистанцию
            \Mordheim\Rule\Move::apply($field, $fighter, [$fighter->position[0] + 1, $fighter->position[1] + 1, $fighter->position[2]], [], true);
            $this->movedThisTurn = true;
        }
    }

    public function shootPhase(Battle $battle, Fighter $fighter, array $enemies, GameField $field): void
    {
        try {
            $fighter->state->validate();
        } catch (FighterAbnormalStateException $e) {
            \Mordheim\BattleLogger::add("{$fighter->name} не может действовать из-за состояния {$fighter->state->value}.");
            return;
        }
        if (empty($enemies)) return;
        $target = $this->getNearestEnemy($fighter, $enemies);
        $ranged = $this->getRangedWeapon($fighter);
        if ($ranged && $target && $fighter->distance($target) <= $ranged->range && !$fighter->isAdjacent($target)) {
            \Mordheim\Rule\Shoot::apply($fighter, $target, false);
        }
    }

    public function magicPhase(Battle $battle, Fighter $fighter, array $enemies, GameField $field): void
    {
        try {
            $fighter->state->validate();
        } catch (FighterAbnormalStateException $e) {
            \Mordheim\BattleLogger::add("{$fighter->name} не может действовать из-за состояния {$fighter->state->value}.");
            return;
        }
        // TODO: реализовать заклинания
    }

    public function closeCombatPhase(Battle $battle, Fighter $fighter, array $enemies, GameField $field): void
    {
        try {
            $fighter->state->validate();
        } catch (FighterAbnormalStateException $e) {
            \Mordheim\BattleLogger::add("{$fighter->name} не может действовать из-за состояния {$fighter->state->value}.");
            return;
        }
        if (empty($enemies)) return;
        $target = $this->getNearestEnemy($fighter, $enemies);
        $canAct = $this->canActAgainst($fighter, $target, $field);
        if ($target && $fighter->isAdjacent($target) && $canAct) {
            \Mordheim\Rule\Attack::apply($fighter, $target);
        }
    }
}
