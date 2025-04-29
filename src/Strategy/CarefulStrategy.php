<?php

namespace Mordheim\Strategy;

use Mordheim\Fighter;
use Mordheim\FighterState;
use Mordheim\GameField;

class CarefulStrategy extends BaseBattleStrategy implements BattleStrategy
{
    public function movePhase(Fighter $self, array $enemies, GameField $field): void
    {
        if (in_array($self->state, [
            \Mordheim\FighterState::PANIC,
            \Mordheim\FighterState::STUNNED,
            \Mordheim\FighterState::OUT_OF_ACTION
        ], true)) {
            \Mordheim\BattleLogger::add("{$self->name} не может действовать из-за состояния {$self->state->value}.");
            return;
        }
        $movedThisTurn = false;
        if ($self->state === FighterState::KNOCKED_DOWN) {
            $movedThisTurn = \Mordheim\Rule\StandUp::apply($self);
        }

        if (empty($enemies)) return;
        $target = $this->getNearestEnemy($self, $enemies);
        if (!$target) return;
        $canAct = $this->canActAgainst($self, $target, $field);
        if (!$canAct) return;
        if (!$self->isAdjacent($target)) {
            // Держит дистанцию
            \Mordheim\Rule\Move::apply($field, $self, [$self->position[0] + 1, $self->position[1] + 1, $self->position[2]], [], true);
        }
    }

    public function shootPhase(Fighter $self, array $enemies, GameField $field): void
    {
        if (empty($enemies)) return;
        $target = $this->getNearestEnemy($self, $enemies);
        $ranged = $this->getRangedWeapon($self);
        if ($ranged && $target && $self->distance($target) <= $ranged->range && !$self->isAdjacent($target)) {
            \Mordheim\Rule\Shoot::apply($self, $target, false);
        }
    }

    public function magicPhase(Fighter $self, array $enemies, GameField $field): void
    {
        // TODO: реализовать заклинания
    }

    public function closeCombatPhase(Fighter $self, array $enemies, GameField $field): void
    {
        if (empty($enemies)) return;
        $target = $this->getNearestEnemy($self, $enemies);
        $canAct = $this->canActAgainst($self, $target, $field);
        if ($target && $self->isAdjacent($target) && $canAct) {
            \Mordheim\Rule\Attack::apply($self, $target);
        }
    }
}
