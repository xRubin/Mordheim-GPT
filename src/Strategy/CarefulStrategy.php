<?php
namespace Mordheim\Strategy;

use Mordheim\Fighter;
use Mordheim\GameField;

class CarefulStrategy extends BaseBattleStrategy implements BattleStrategy
{
    public function executeTurn(Fighter $self, array $enemies, GameField $field): void
    {
        if (in_array($self->state, [
            \Mordheim\FighterState::PANIC,
            \Mordheim\FighterState::STUNNED,
            \Mordheim\FighterState::OUT_OF_ACTION
        ], true)) {
            \Mordheim\BattleLogger::add("{$self->name} не может действовать из-за состояния {$self->state->value}.");
            return;
        }
        if (empty($enemies)) return;
        $target = $this->getNearestEnemy($self, $enemies);
        if (!$target) return;
        $canAct = $this->canActAgainst($self, $target, $field);
        if (!$canAct) return;
        $ranged = $this->getRangedWeapon($self);
        $movedThisTurn = false;
        if ($ranged && $self->distance($target) <= $ranged->range && !$self->isAdjacent($target)) {
            $self->shoot($target, $movedThisTurn);
        } elseif (!$self->isAdjacent($target)) {
            // Двигается так, чтобы держать дистанцию
            $self->moveTowards([$self->position[0]+1, $self->position[1]+1, $self->position[2]], $field);
            $movedThisTurn = true;
            // После движения пробуем стрелять, если в радиусе
            if ($ranged && $self->distance($target) <= $ranged->range && !$self->isAdjacent($target)) {
                $self->shoot($target, $movedThisTurn);
            }
        }
    }
}
