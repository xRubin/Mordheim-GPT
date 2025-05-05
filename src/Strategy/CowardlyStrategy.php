<?php

namespace Mordheim\Strategy;

use Mordheim\Battle;
use Mordheim\Fighter;
use Mordheim\Ruler;

class CowardlyStrategy extends BaseBattleStrategy
{
    public float $aggressiveness = 0.6;
    protected function onMovePhase(Battle $battle, Fighter $fighter, array $enemies): void
    {
        if ($battle->getActiveCombats()->isFighterInCombat($fighter))
            return;

        // Ищем лидера в радиусе 6" (Ld bubble)
        $leader = null;
        foreach ($battle->getAlliesFor($fighter) as $ally) {
            if ($ally->hasSkill('Leader') && Ruler::distance($fighter->position, $ally->position) <= 6) {
                $leader = $ally;
                break;
            }
        }
        if (!$leader) {
            // Нет лидера рядом — двигаемся к ближайшему лидеру
            $nearest = null;
            $minDist = PHP_INT_MAX;
            foreach ($battle->getAlliesFor($fighter) as $ally) {
                if ($ally->hasSkill('Leader')) {
                    $dist = Ruler::distance($fighter->position, $ally->position);
                    if ($dist < $minDist) {
                        $minDist = $dist;
                        $nearest = $ally;
                    }
                }
            }
            if ($nearest) {
                \Mordheim\Rule\Move::apply($battle, $fighter, $nearest->position, $this->aggressiveness, [], true);
                return;
            }
        }
        if (empty($enemies)) return;
        $target = $this->getNearestEnemy($fighter, $enemies);
        if (!$target) return;
        $canAct = $this->canActAgainst($battle, $fighter, $target);
        if (!$canAct) {
            // Не прошёл тест — не действует
            return;
        }
        if (Ruler::distance($fighter->position, $target->position) < 4) {
            // Уходит от врага
            [$fx, $fy, $fz] = $fighter->position; // остальная логика ниже
            [$tx, $ty, $tz] = $target->position;
            $dx = $fx - $tx;
            $dy = $fy - $ty;
            $dz = $fz - $tz;
            $move = [$fx + ($dx !== 0 ? ($dx > 0 ? 1 : -1) : 0), $fy + ($dy !== 0 ? ($dy > 0 ? 1 : -1) : 0), $fz + ($dz !== 0 ? ($dz > 0 ? 1 : -1) : 0)];
            if ($move[0] >= 0 && $move[1] >= 0 && $move[2] >= 0 && $move[0] < 64 && $move[1] < 64 && $move[2] < 4 && !$battle->getField()->getCell($move[0], $move[1], $move[2])->obstacle) {
                $fighter->position = $move;
            }
        } else {
            // Проверяем наличие стрелкового оружия и его дальность
            $ranged = $this->getRangedWeapon($fighter);
            if ($ranged && Ruler::distance($fighter->position, $target->position) > $ranged->range) {
                // Если не в радиусе, двигаемся к цели
                \Mordheim\Rule\Move::apply($battle, $fighter, $target->position, $this->aggressiveness, [], true);
            }
        }
    }

    protected function onShootPhase(Battle $battle, Fighter $fighter, array $enemies): void
    {
        if (empty($enemies)) return;
        $target = $this->getNearestEnemy($fighter, $enemies);
        $ranged = $this->getRangedWeapon($fighter);
        if ($ranged && $target && Ruler::distance($fighter->position, $target->position) > 6) {
            \Mordheim\Rule\Shoot::apply($battle, $fighter, $target, false);
        }
    }

    protected function onMagicPhase(Battle $battle, Fighter $fighter, array $enemies): void
    {
        // TODO: реализовать заклинания
    }

    protected function onCloseCombatPhase(Battle $battle, Fighter $fighter, array $enemies): void
    {
        // Обычно не атакует в рукопашную, если только не окружён
    }
}
