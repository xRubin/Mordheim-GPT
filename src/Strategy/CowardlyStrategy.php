<?php

namespace Mordheim\Strategy;

use Mordheim\Battle;
use Mordheim\Exceptions\FighterAbnormalStateException;
use Mordheim\Fighter;
use Mordheim\FighterState;
use Mordheim\GameField;

class CowardlyStrategy extends BaseBattleStrategy implements BattleStrategy
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

        // Ищем лидера в радиусе 6" (Ld bubble)
        $leader = null;
        foreach ($battle->getAlliesFor($fighter) as $ally) {
            if (method_exists($ally, 'isLeader') && $ally->isLeader() && $fighter->distance($ally) <= 6) {
                $leader = $ally;
                break;
            }
        }
        if (!$leader) {
            // Нет лидера рядом — двигаемся к ближайшему лидеру
            $nearest = null;
            $minDist = PHP_INT_MAX;
            foreach ($battle->getAlliesFor($fighter) as $ally) {
                if (method_exists($ally, 'isLeader') && $ally->isLeader()) {
                    $dist = $fighter->distance($ally);
                    if ($dist < $minDist) {
                        $minDist = $dist;
                        $nearest = $ally;
                    }
                }
            }
            if ($nearest) {
                \Mordheim\Rule\Move::apply($field, $fighter, $nearest->position, [], true);
                return;
            }
        }
        if (empty($enemies)) return;
        $target = $this->getNearestEnemy($fighter, $enemies);
        if (!$target) return;
        $canAct = $this->canActAgainst($fighter, $target);
        if (!$canAct) {
            // Не прошёл тест — не действует
            return;
        }
        if ($fighter->distance($target) < 4) {
            // Уходит от врага
            [$fx, $fy, $fz] = $fighter->position; // остальная логика ниже
            [$tx, $ty, $tz] = $target->position;
            $dx = $fx - $tx;
            $dy = $fy - $ty;
            $dz = $fz - $tz;
            $move = [$fx + ($dx !== 0 ? ($dx > 0 ? 1 : -1) : 0), $fy + ($dy !== 0 ? ($dy > 0 ? 1 : -1) : 0), $fz + ($dz !== 0 ? ($dz > 0 ? 1 : -1) : 0)];
            if ($move[0] >= 0 && $move[1] >= 0 && $move[2] >= 0 && $move[0] < 64 && $move[1] < 64 && $move[2] < 4 && !$field->getCell($move[0], $move[1], $move[2])->obstacle) {
                $fighter->position = $move;
            }
        } else {
            // Проверяем наличие стрелкового оружия и его дальность
            $ranged = $this->getRangedWeapon($fighter);
            if ($ranged && $fighter->distance($target) > $ranged->range) {
                // Если не в радиусе, двигаемся к цели
                \Mordheim\Rule\Move::apply($field, $fighter, $target->position, [], true);
                $this->movedThisTurn = true;
            }
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
        if ($ranged && $target && $fighter->distance($target) > 6) {
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
        // Обычно не атакует в рукопашную, если только не окружён
    }
}
