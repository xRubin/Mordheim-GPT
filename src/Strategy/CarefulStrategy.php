<?php

namespace Mordheim\Strategy;

use Mordheim\Battle;
use Mordheim\Fighter;
use Mordheim\Ruler;
use Mordheim\Slot;

class CarefulStrategy extends BaseBattleStrategy
{
    public float $aggressiveness = 0.8;

    protected function onMovePhase(Battle $battle, Fighter $fighter, array $enemies): void
    {
        if (empty($enemies)) return;

        if ($battle->getActiveCombats()->isFighterInCombat($fighter))
            return;

        $target = $this->getNearestEnemy($fighter, $enemies);
        if (!$target) return;
        $canAct = $this->canActAgainst($battle, $fighter, $target);
        if (!$canAct) return;
        if (Ruler::isAdjacent($fighter, $target))
            return; // Держит дистанцию

        \Mordheim\Rule\Move::common($battle, $fighter, [$fighter->getState()->getPosition()[0] + 1, $fighter->getState()->getPosition()[1] + 1, $fighter->getState()->getPosition()[2]], $this->aggressiveness);
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
            \Mordheim\BattleLogger::add("{$fighter->getName()} применяет заклинание {$wizardSpell->getSpell()->name}!");
            if ($wizardSpell->getSpell()->getProcessor()?->onPhaseMagic($battle, $fighter))
                return;
        }
    }

    protected function onCloseCombatPhase(Battle $battle, Fighter $fighter, array $enemies): void
    {
        if (empty($enemies)) return;
        $target = $this->getNearestEnemy($fighter, $enemies);
        $canAct = $this->canActAgainst($battle, $fighter, $target);
        if ($target && Ruler::isAdjacent($fighter, $target) && $canAct) {
            \Mordheim\Rule\Attack::melee($battle, $fighter, $target);
        }
    }
}
