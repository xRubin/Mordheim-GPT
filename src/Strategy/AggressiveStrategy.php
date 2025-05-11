<?php

namespace Mordheim\Strategy;

use Mordheim\Battle;
use Mordheim\Exceptions\ChargeFailedException;
use Mordheim\Exceptions\MoveRunDeprecatedException;
use Mordheim\FighterInterface;
use Mordheim\Rule\Charge;
use Mordheim\Slot;

class AggressiveStrategy extends BaseBattleStrategy
{
    public float $aggressiveness = 1.0;

    protected function onMovePhase(Battle $battle, FighterInterface $fighter, array $enemies): void
    {
        if (empty($enemies)) return;

        $target = $this->getNearestEnemy($fighter, $enemies);

        if ($this->isAdjacent($fighter, $target))
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

        try {
            \Mordheim\Rule\Move::run($battle, $fighter, $target->getState()->getPosition(), $this->aggressiveness);
            $this->spentShoot = true;
        } catch (MoveRunDeprecatedException $e) {
            \Mordheim\Rule\Move::common($battle, $fighter, $target->getState()->getPosition(), $this->aggressiveness);
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
        if ($target && $this->isAdjacent($fighter, $target)) {
            $canAttack = $this->canActAgainst($battle, $fighter, $target);
            if ($canAttack) {
                \Mordheim\Rule\Attack::melee($battle, $fighter, $target);
            }
        }
    }
}
