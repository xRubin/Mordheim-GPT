<?php

namespace Mordheim\Strategy;

use Mordheim\Fighter;
use Mordheim\FighterState;
use Mordheim\GameField;

class AggressiveStrategy extends BaseBattleStrategy implements BattleStrategy
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
        $movedThisTurn = false;
        if ($self->state === FighterState::KNOCKED_DOWN) {
            $movedThisTurn = \Mordheim\Rule\StandUp::apply($self);
        }

        if (empty($enemies)) return;
        $target = $this->getNearestEnemy($self, $enemies);
        if (!$target) return;
        if ($self->isAdjacent($target)) {
            // Проверка страха и ужаса
            $canAttack = $this->canActAgainst($self, $target, $field);
            if ($canAttack) {
                \Mordheim\Rule\Attack::apply($self, $target);
            } else {
                // Не прошёл тест страха/ужаса — отступает
                $self->moveTowards([$self->position[0] + 1, $self->position[1] + 1, $self->position[2]], $field);
            }
        } else {
            // Проверяем наличие стрелкового оружия и его дальность
            $ranged = $this->getRangedWeapon($self);
            // Если не в радиусе стрельбы, сначала двигается
            if ($ranged && $self->distance($target) <= $ranged->range) {
                \Mordheim\Rule\Shoot::apply($self, $target, $movedThisTurn);
            } else {
                $self->moveTowards($target->position, $field);
                $movedThisTurn = true;
                // После движения пробуем стрелять, если в радиусе
                if ($ranged && $self->distance($target) <= $ranged->range) {
                    \Mordheim\Rule\Shoot::apply($self, $target, $movedThisTurn);
                }
            }
        }
    }
}
