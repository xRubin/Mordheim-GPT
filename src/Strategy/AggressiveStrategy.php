<?php
namespace Mordheim\Strategy;

use Mordheim\Fighter;
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
        if (empty($enemies)) return;
        $target = $this->getNearestEnemy($self, $enemies);
        if (!$target) return;
        if ($self->isAdjacent($target)) {
            // Проверка страха и ужаса
            $canAttack = $this->canActAgainst($self, $target, $field);
            if ($canAttack) {
                $self->attack($target);
            } else {
                // Не прошёл тест страха/ужаса — отступает
                $self->moveTowards([$self->position[0]+1, $self->position[1]+1, $self->position[2]], $field);
            }
        } else {
            // Проверяем наличие стрелкового оружия и его дальность
            $ranged = $this->getRangedWeapon($self);
            $movedThisTurn = false;
            // Если не в радиусе стрельбы, сначала двигается
            if ($ranged && $self->distance($target) <= $ranged->range) {
                $self->shoot($target, $movedThisTurn);
            } else {
                $self->moveTowards($target->position, $field);
                $movedThisTurn = true;
                // После движения пробуем стрелять, если в радиусе
                if ($ranged && $self->distance($target) <= $ranged->range) {
                    $self->shoot($target, $movedThisTurn);
                }
            }
        }
    }
}
