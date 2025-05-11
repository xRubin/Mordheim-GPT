<?php

namespace Mordheim\Strategy;

use Mordheim\Battle;
use Mordheim\BattleStrategyInterface;
use Mordheim\FighterInterface;
use Mordheim\Ruler;
use Mordheim\Slot;
use Mordheim\SpecialRule;
use Mordheim\Status;

abstract class BaseBattleStrategy implements BattleStrategyInterface
{
    public $spentMove = false;
    public $spentCharge = false;
    public $spentShoot = false;
    public $spentMagic = false;
    public $spentCloseCombat = false;

    public float $aggressiveness = 0.6;

    public function resetOnTurn(): static
    {
        $this->spentMove = false;
        $this->spentCharge = false;
        $this->spentShoot = false;
        $this->spentMagic = false;
        $this->spentCloseCombat = false;
        return $this;
    }

    public function spentAll(): static
    {
        $this->spentMove = true;
        $this->spentCharge = true;
        $this->spentShoot = true;
        $this->spentMagic = true;
        $this->spentCloseCombat = true;
        return $this;
    }

    /**
     * Найти ближайшего врага
     */
    protected function getNearestEnemy(FighterInterface $fighter, array $enemies): ?FighterInterface
    {
        if (empty($enemies)) return null;
        usort($enemies, fn($a, $b) => $this->getDistance($fighter, $a) <=> $this->getDistance($fighter, $b));
        return $enemies[0];
    }

    /**
     * Проверить страх/ужас. Вернёт true если можно действовать
     */
    protected function canActAgainst(Battle $battle, FighterInterface $fighter, FighterInterface $target): bool
    {
        if ($fighter->getState()->getStatus() === Status::FRENZY && ($this->getDistance($fighter, $target) < $fighter->getRunRange())) {
            return true;
        } else {
            $allies = $battle->getAlliesFor($fighter);
            if ($target->hasSpecialRule(SpecialRule::CAUSE_FEAR))
                return \Mordheim\Rule\Psychology::testFear($fighter, $target, $allies);
        }
        return true;
    }

    public function movePhase(Battle $battle, FighterInterface $fighter, array $enemies): void
    {
        if ($fighter->getState()->getStatus() === Status::KNOCKED_DOWN) {
            if ($this->spentMove = \Mordheim\Rule\StandUp::apply($fighter)) {
                // потратили вставание по общим правилам
                $this->spentCharge = true;
                $this->spentCloseCombat = true;
            }
        }

        if (!$fighter->getState()->getStatus()->canAct()) {
            \Mordheim\BattleLogger::add("{$fighter->getName()} не может действовать из-за состояния {$fighter->getState()->getStatus()->value}.");
            return;
        }

        if ($battle->getActiveCombats()->isFighterInCombat($fighter))
            return;

        if (!$this->spentMove)
            $this->onMovePhase($battle, $fighter, $enemies);

        $this->spentMove = true;
    }

    public function shootPhase(Battle $battle, FighterInterface $fighter, array $enemies): void
    {
        if (!$fighter->getState()->getStatus()->canAct()) {
            \Mordheim\BattleLogger::add("{$fighter->getName()} не может действовать из-за состояния {$fighter->getState()->getStatus()->value}.");
            return;
        }

        if (!count($fighter->getEquipmentManager()->getItemsBySlot(SLOT::RANGED)))
            return;

        if ($battle->getActiveCombats()->isFighterInCombat($fighter))
            return;

        if (!$this->spentShoot)
            $this->onShootPhase($battle, $fighter, $enemies);

        $this->spentShoot = true;
    }

    public function magicPhase(Battle $battle, FighterInterface $fighter, array $enemies): void
    {
        if (!$fighter->getState()->getStatus()->canAct()) {
            \Mordheim\BattleLogger::add("{$fighter->getName()} не может действовать из-за состояния {$fighter->getState()->getStatus()->value}.");
            return;
        }

        if (!$this->spentMagic)
            $this->onMagicPhase($battle, $fighter, $enemies);

        $this->spentMagic = true;
    }

    public function closeCombatPhase(Battle $battle, FighterInterface $fighter, array $enemies): void
    {
        if (!$fighter->getState()->getStatus()->canAct()) {
            \Mordheim\BattleLogger::add("{$fighter->getName()} не может действовать из-за состояния {$fighter->getState()->getStatus()->value}.");
            return;
        }

        if (!count($fighter->getEquipmentManager()->getItemsBySlot(Slot::MELEE)))
            return;

        if (!$this->spentCloseCombat)
            $this->onCloseCombatPhase($battle, $fighter, $enemies);

        $this->spentCloseCombat = true;
    }

    abstract protected function onMovePhase(Battle $battle, FighterInterface $fighter, array $enemies): void;

    abstract protected function onShootPhase(Battle $battle, FighterInterface $fighter, array $enemies): void;

    abstract protected function onMagicPhase(Battle $battle, FighterInterface $fighter, array $enemies): void;

    abstract protected function onCloseCombatPhase(Battle $battle, FighterInterface $fighter, array $enemies): void;

    public function isAdjacent(FighterInterface $source, FighterInterface $target): bool
    {
        return Ruler::isAdjacent($source->getState()->getPosition(), $target->getState()->getPosition());
    }

    public function getDistance(FighterInterface $source, FighterInterface $target): float
    {
        return Ruler::distance($source->getState()->getPosition(), $target->getState()->getPosition());
    }
}
