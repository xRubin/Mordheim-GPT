<?php

namespace Mordheim\Strategy;

use Mordheim\Fighter;
use Mordheim\FighterState;
use Mordheim\GameField;

class AggressiveStrategy extends BaseBattleStrategy implements BattleStrategy
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
        if ($self->state === FighterState::KNOCKED_DOWN) {
            \Mordheim\Rule\StandUp::apply($self);
        }
        if (empty($enemies)) return;
        $target = $this->getNearestEnemy($self, $enemies);
        if (!$target) return;
        if (!$self->isAdjacent($target)) {
            \Mordheim\Rule\Move::apply($field, $self, $target->position);
        }
    }

    public function shootPhase(Fighter $self, array $enemies, GameField $field): void
    {
        $ranged = $this->getRangedWeapon($self);
        if (!$ranged || empty($enemies)) return;
        $target = $this->getNearestEnemy($self, $enemies);
        if ($target && $self->distance($target) <= $ranged->range) {
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
        if ($target && $self->isAdjacent($target)) {
            $canAttack = $this->canActAgainst($self, $target, $field);
            if ($canAttack) {
                \Mordheim\Rule\Attack::apply($self, $target);
            }
        }
    }
}
