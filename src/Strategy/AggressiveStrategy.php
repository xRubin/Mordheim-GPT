<?php

namespace Mordheim\Strategy;

use Mordheim\Battle;
use Mordheim\Exceptions\ChargeFailedException;
use Mordheim\FighterInterface;
use Mordheim\Rule\Charge;
use Mordheim\Slot;

class AggressiveStrategy extends BaseBattleStrategy implements BattleStrategyInterface
{
    public float $aggressiveness = 1.0;

    protected function onMovePhase(Battle $battle, FighterInterface $fighter, array $enemies): void
    {
        if (empty($enemies)) return;

        $target = $this->getNearestEnemy($fighter, $enemies);

        if ($fighter->isAdjacent($target))
            return;

        if (!$this->spentCharge) {
            try {
                $battle->getActiveCombats()->add(
                    Charge::attempt($battle, $fighter, $target, $this->aggressiveness)
                );
                $this->spentCharge = true;
                $this->spentShoot = true;
                $this->spentMagic = true;
                return;
            } catch (ChargeFailedException $e) {
            }
        }
        \Mordheim\Rule\Move::apply($battle, $fighter, $target->getState()->getPosition(), $this->aggressiveness);
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
        if ($target && $fighter->isAdjacent($target)) {
            $canAttack = $this->canActAgainst($battle, $fighter, $target);
            if ($canAttack) {
                \Mordheim\Rule\Attack::apply($battle, $fighter, $target);
            }
        }
    }
}
