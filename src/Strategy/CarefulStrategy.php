<?php

namespace Mordheim\Strategy;

use Mordheim\Battle;
use Mordheim\FighterInterface;
use Mordheim\Slot;

class CarefulStrategy extends BaseBattleStrategy
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
        if (!$this->isAdjacent($fighter, $target)) {
            // Держит дистанцию
            \Mordheim\Rule\Move::common($battle, $fighter, [$fighter->getState()->getPosition()[0] + 1, $fighter->getState()->getPosition()[1] + 1, $fighter->getState()->getPosition()[2]], $this->aggressiveness);
        }
    }

    protected function onShootPhase(Battle $battle, FighterInterface $fighter, array $enemies): void
    {
        if (empty($enemies)) return;
        $ranged = $fighter->getEquipmentManager()->getMainWeapon(Slot::RANGED);
        if (!$ranged) return;
        $target = $this->getNearestEnemy($fighter, $enemies);
        if ($target && $this->getDistance($fighter, $target) <= $ranged->getRange()) {
            \Mordheim\Rule\Attack::ranged($battle, $fighter, $target, $this->spentMove);
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
        if ($target && $this->isAdjacent($fighter, $target) && $canAct) {
            \Mordheim\Rule\Attack::melee($battle, $fighter, $target);
        }
    }
}
