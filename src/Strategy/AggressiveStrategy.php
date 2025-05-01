<?php

namespace Mordheim\Strategy;

use Mordheim\Battle;
use Mordheim\Fighter;
use Mordheim\Rule\Charge;

class AggressiveStrategy extends BaseBattleStrategy implements BattleStrategyInterface
{
    protected function onMovePhase(Battle $battle, Fighter $fighter, array $enemies): void
    {
        if (empty($enemies)) return;

        $target = $this->getNearestEnemy($fighter, $enemies);

        if (!$fighter->isAdjacent($target)) {
            if (!$this->spentCharge && ($closeCombat = Charge::attempt($battle, $fighter, $target))) {
                $battle->getActiveCombats()->add($closeCombat);
                $this->spentCharge = true;
                $this->spentShoot = true;
                $this->spentMagic = true;
            } else {
                \Mordheim\Rule\Move::apply($battle, $fighter, $target->position);
            }
        }
    }

    protected function onShootPhase(Battle $battle, Fighter $fighter, array $enemies): void
    {
        $ranged = $this->getRangedWeapon($fighter);
        if (!$ranged || empty($enemies)) return;
        $target = $this->getNearestEnemy($fighter, $enemies);
        if ($target && $fighter->distance($target) <= $ranged->range) {
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
        if ($target && $fighter->isAdjacent($target)) {
            $canAttack = $this->canActAgainst($battle, $fighter, $target);
            if ($canAttack) {
                \Mordheim\Rule\Attack::apply($battle, $fighter, $target);
            }
        }
    }
}
