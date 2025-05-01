<?php

namespace Mordheim\Strategy;

use Mordheim\Battle;
use Mordheim\Fighter;

class CarefulStrategy extends BaseBattleStrategy implements BattleStrategyInterface
{
    protected function onMovePhase(Battle $battle, Fighter $fighter, array $enemies): void
    {
        if (empty($enemies)) return;

        if ($battle->getActiveCombats()->isFighterInCombat($fighter))
            return;

        $target = $this->getNearestEnemy($fighter, $enemies);
        if (!$target) return;
        $canAct = $this->canActAgainst($battle, $fighter, $target);
        if (!$canAct) return;
        if (!$fighter->isAdjacent($target)) {
            // Держит дистанцию
            \Mordheim\Rule\Move::apply($battle, $fighter, [$fighter->position[0] + 1, $fighter->position[1] + 1, $fighter->position[2]], [], true);
        }
    }

    protected function onShootPhase(Battle $battle, Fighter $fighter, array $enemies): void
    {
        if (empty($enemies)) return;
        $target = $this->getNearestEnemy($fighter, $enemies);
        $ranged = $this->getRangedWeapon($fighter);
        if ($ranged && $target && $fighter->distance($target) <= $ranged->range && !$fighter->isAdjacent($target)) {
            \Mordheim\Rule\Shoot::apply($battle, $fighter, $target, false);
        }
    }

    protected function onMagicPhase(Battle $battle, Fighter $fighter, array $enemies): void
    {
        // TODO: реализовать заклинания
    }

    protected function onCloseCombatPhase(Battle $battle, Fighter $fighter, array $enemies): void
    {
        if (empty($enemies)) return;
        $target = $this->getNearestEnemy($fighter, $enemies);
        $canAct = $this->canActAgainst($battle, $fighter, $target);
        if ($target && $fighter->isAdjacent($target) && $canAct) {
            \Mordheim\Rule\Attack::apply($battle, $fighter, $target);
        }
    }
}
