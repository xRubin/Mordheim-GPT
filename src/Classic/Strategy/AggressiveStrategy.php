<?php

namespace Mordheim\Classic\Strategy;

use Mordheim\Classic\Battle;
use Mordheim\Classic\Fighter;
use Mordheim\Classic\Rule\Charge;
use Mordheim\Classic\Ruler;
use Mordheim\Slot;

class AggressiveStrategy extends BaseBattleStrategy
{
    public float $aggressiveness = 1.0;

    protected function onMovePhase(Battle $battle, Fighter $fighter, array $enemies): void
    {
        foreach ([
                     new \Mordheim\Classic\Strategy\PhaseMove\ChargeBlock($this, 'nearest', 0.8),
                     new PhaseMove\RunBlock($this, 'nearest', 0.8),
                     new \Mordheim\Classic\Strategy\PhaseMove\MoveBlock($this, 'nearest', 0.8),
                 ] as $block) {
            if ($block()($battle, $fighter))
                break;
        }
    }

    protected function onShootPhase(Battle $battle, Fighter $fighter, array $enemies): void
    {
        if (empty($enemies)) return;
        $ranged = $fighter->getEquipmentManager()->getMainWeapon(Slot::RANGED);
        if (!$ranged) return;
        $target = $this->getNearestEnemy($fighter, $enemies);
        if ($target && Ruler::distance($fighter, $target) <= $ranged->getRange()) {
            \Mordheim\Classic\Rule\Attack::ranged($battle, $fighter, $target, $this->spentMove);
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
                \Mordheim\Classic\Rule\Attack::melee($battle, $fighter, $target);
            }
        }
    }
}
