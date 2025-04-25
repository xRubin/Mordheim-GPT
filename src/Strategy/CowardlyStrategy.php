<?php

namespace Mordheim\Strategy;

use Mordheim\Fighter;
use Mordheim\FighterState;
use Mordheim\GameField;

class CowardlyStrategy extends BaseBattleStrategy implements BattleStrategy
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
        // Ищем лидера в радиусе 6" (Ld bubble)
        $leader = null;
        foreach ($field->getAllies($self) as $ally) {
            if (method_exists($ally, 'isLeader') && $ally->isLeader() && $self->distance($ally) <= 6) {
                $leader = $ally;
                break;
            }
        }
        if (!$leader) {
            // Нет лидера рядом — двигаемся к ближайшему лидеру
            $nearest = null;
            $minDist = PHP_INT_MAX;
            foreach ($field->getAllies($self) as $ally) {
                if (method_exists($ally, 'isLeader') && $ally->isLeader()) {
                    $dist = $self->distance($ally);
                    if ($dist < $minDist) {
                        $minDist = $dist;
                        $nearest = $ally;
                    }
                }
            }
            if ($nearest) {
                $self->moveTowards($nearest->position, $field);
                return;
            }
        }
        if (empty($enemies)) return;
        $target = $this->getNearestEnemy($self, $enemies);
        if (!$target) return;
        $canAct = $this->canActAgainst($self, $target);
        if (!$canAct) {
            // Не прошёл тест — не действует
            return;
        }
        if ($self->distance($target) < 4) {
            // Уходит от врага
            [$fx, $fy, $fz] = $self->position;
            [$tx, $ty, $tz] = $target->position;
            $dx = $fx - $tx;
            $dy = $fy - $ty;
            $dz = $fz - $tz;
            $move = [$fx + ($dx !== 0 ? ($dx > 0 ? 1 : -1) : 0), $fy + ($dy !== 0 ? ($dy > 0 ? 1 : -1) : 0), $fz + ($dz !== 0 ? ($dz > 0 ? 1 : -1) : 0)];
            if ($move[0] >= 0 && $move[1] >= 0 && $move[2] >= 0 && $move[0] < 64 && $move[1] < 64 && $move[2] < 4 && !$field->getCell($move[0], $move[1], $move[2])->obstacle) {
                $self->position = $move;
            }
        } else {
            // Проверяем наличие стрелкового оружия и его дальность
            $ranged = $this->getRangedWeapon($self);
            if ($ranged && $self->distance($target) <= $ranged->range) {
                \Mordheim\Rule\Shoot::apply($self, $target, $movedThisTurn);
            } else if ($ranged) {
                // Если не в радиусе, двигаемся и пробуем стрелять после движения
                $self->moveTowards($target->position, $field);
                $movedThisTurn = true;
                if ($self->distance($target) <= $ranged->range) {
                    \Mordheim\Rule\Shoot::apply($self, $target, $movedThisTurn);
                }
            }
        }
    }
}
