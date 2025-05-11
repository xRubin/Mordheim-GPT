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
        // TODO: check active skills
        $spells = $fighter->getAdvancement()->getSpells();
        foreach ($spells as $wizardSpell) {
            $difficulty = $wizardSpell->getDifficulty();
            $spell = $wizardSpell->getSpell();
            $roll = \Mordheim\Dice::roll(6) + \Mordheim\Dice::roll(6);
            \Mordheim\BattleLogger::add("{$fighter->getName()} бросает 2d6=$roll для заклинания {$spell->name} (сложность {$difficulty})");
            if ($roll < $difficulty) {
                \Mordheim\BattleLogger::add("{$fighter->getName()} не смог применить заклинание {$spell->name}.");
                continue;
            }
            \Mordheim\BattleLogger::add("{$fighter->getName()} применяет заклинание {$spell->name}!");
            if ($spell->onPhaseMagic($battle, $fighter))
                return;
        }
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
