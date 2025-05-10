<?php

namespace Mordheim\Strategy;

use Mordheim\Battle;
use Mordheim\FighterInterface;
use Mordheim\Slot;

class CarefulStrategy extends BaseBattleStrategy implements BattleStrategyInterface
{
    public float $aggressiveness = 0.8;

    protected function onMovePhase(Battle $battle, FighterInterface $fighter, array $enemies): void
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
            \Mordheim\Rule\Move::apply($battle, $fighter, [$fighter->getState()->getPosition()[0] + 1, $fighter->getState()->getPosition()[1] + 1, $fighter->getState()->getPosition()[2]], $this->aggressiveness, [], true);
        }
    }

    protected function onShootPhase(Battle $battle, FighterInterface $fighter, array $enemies): void
    {
        if (empty($enemies)) return;
        $ranged = $fighter->getEquipmentManager()->getMainWeapon(Slot::RANGED);
        if (!$ranged) return;
        $target = $this->getNearestEnemy($fighter, $enemies);
        if ($target && $fighter->getDistance($target) <= $ranged->getRange()) {
            \Mordheim\Rule\Shoot::apply($battle, $fighter, $target, $this->spentMove);
        }
    }

    protected function onMagicPhase(Battle $battle, FighterInterface $fighter, array $enemies): void
    {
        // TODO: реализовать заклинания
    }

    protected function onCloseCombatPhase(Battle $battle, FighterInterface $fighter, array $enemies): void
    {
        if (empty($enemies)) return;
        $target = $this->getNearestEnemy($fighter, $enemies);
        $canAct = $this->canActAgainst($battle, $fighter, $target);
        if ($target && $fighter->isAdjacent($target) && $canAct) {
            \Mordheim\Rule\Attack::apply($battle, $fighter, $target);
        }
    }
}
