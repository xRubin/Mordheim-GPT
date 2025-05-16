<?php

namespace Mordheim\Strategy;

use Mordheim\Battle;
use Mordheim\Exceptions\ChargeFailedException;
use Mordheim\Exceptions\MoveRunDeprecatedException;
use Mordheim\Fighter;
use Mordheim\Rule\Charge;
use Mordheim\Ruler;
use Mordheim\Slot;

class AggressiveStrategy extends BaseBattleStrategy
{
    public float $aggressiveness = 1.0;

    protected function onMovePhase(Battle $battle, Fighter $fighter, array $enemies): void
    {
        if (empty($enemies)) return;

        $target = $this->getNearestEnemy($fighter, $enemies);

        if (Ruler::isAdjacent($fighter, $target))
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
            \Mordheim\Rule\Move::runIfNoEnemies($battle, $fighter, $target->getState()->getPosition(), $this->aggressiveness);
            $this->spentShoot = true;
        } catch (MoveRunDeprecatedException $e) {
            \Mordheim\Rule\Move::common($battle, $fighter, $target->getState()->getPosition(), $this->aggressiveness);
        }
    }

    protected function onShootPhase(Battle $battle, Fighter $fighter, array $enemies): void
    {
        if (empty($enemies)) return;
        $ranged = $fighter->getEquipmentManager()->getMainWeapon(Slot::RANGED);
        if (!$ranged) return;
        $target = $this->getNearestEnemy($fighter, $enemies);
        if ($target && Ruler::distance($fighter, $target) <= $ranged->getRange()) {
            \Mordheim\Rule\Attack::ranged($battle, $fighter, $target, $this->spentMove);
        }
    }

    protected function onMagicPhase(Battle $battle, Fighter $fighter, array $enemies): void
    {
        foreach ($fighter->getAdvancement()->getSpells() as $wizardSpell) {
            \Mordheim\BattleLogger::add("{$fighter->getName()} проверяет заклинание {$wizardSpell->getSpell()->name}!");
            if ($wizardSpell->getSpell()->getProcessor()?->onPhaseMagic($battle, $fighter))
                return;
        }
    }

    protected function onCloseCombatPhase(Battle $battle, Fighter $fighter, array $enemies): void
    {
        if (empty($enemies)) return;
        $target = $this->getNearestEnemy($fighter, $enemies);
        if ($target && Ruler::isAdjacent($fighter, $target)) {
            $canAttack = $this->canActAgainst($battle, $fighter, $target);
            if ($canAttack) {
                \Mordheim\Rule\Attack::melee($battle, $fighter, $target);
            }
        }
    }
}
