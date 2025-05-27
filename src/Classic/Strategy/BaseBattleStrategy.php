<?php

namespace Mordheim\Classic\Strategy;

use Mordheim\Classic\Battle;
use Mordheim\Classic\BattleStrategyInterface;
use Mordheim\Classic\Equipment;
use Mordheim\Classic\Exceptions\PathfinderTargetUnreachableException;
use Mordheim\Classic\Fighter;
use Mordheim\Classic\Ruler;
use Mordheim\Classic\SpecialRule;
use Mordheim\Classic\Status;
use Mordheim\Slot;

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
    protected function getNearestEnemy(Fighter $fighter, array $enemies): ?Fighter
    {
        if (empty($enemies)) return null;
        usort($enemies, fn($a, $b) => Ruler::distance($fighter, $a) <=> Ruler::distance($fighter, $b));
        return $enemies[0];
    }

    /**
     * Проверить страх/ужас. Вернёт true если можно действовать
     */
    protected function canActAgainst(Battle $battle, Fighter $fighter, Fighter $target): bool
    {
        if ($fighter->getState()->getStatus() === Status::FRENZY && (Ruler::distance($fighter, $target) < $fighter->getRunRange())) {
            return true;
        } else {
            $allies = $battle->getAlliesFor($fighter);
            if ($target->hasSpecialRule(SpecialRule::CAUSE_FEAR))
                return \Mordheim\Classic\Rule\Psychology::testFear($fighter, $target, $allies);
        }
        return true;
    }

    public function movePhase(Battle $battle, Fighter $fighter, array $enemies): void
    {
        if ($fighter->getState()->getStatus() === Status::KNOCKED_DOWN) {
            if ($this->spentMove = \Mordheim\Classic\Rule\StandUp::apply($fighter)) {
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

        try {
            if (!$this->spentMove)
                $this->onMovePhase($battle, $fighter, $enemies);
        } catch (PathfinderTargetUnreachableException $e) {
            \Mordheim\BattleLogger::add("{$fighter->getName()} PathfinderTargetUnreachableException (" . implode(', ', $e->getPosition()) . ") -> (" . implode(', ', $e->getTarget()) . ")");
        }

        $this->spentMove = true;
    }

    public function shootPhase(Battle $battle, Fighter $fighter, array $enemies): void
    {
        if (!$fighter->getState()->getStatus()->canAct()) {
            \Mordheim\BattleLogger::add("[Debug][PhaseShoot]{$fighter->getName()} не может стрелять из-за состояния {$fighter->getState()->getStatus()->value}.");
            return;
        }

        if (!count($fighter->getEquipmentManager()->getItemsBySlot(SLOT::RANGED))) {
            \Mordheim\BattleLogger::add("[Debug][PhaseShoot]{$fighter->getName()} не может стрелять так как нет Ranged оружия.");
            return;
        }

        if ($battle->getActiveCombats()->isFighterInCombat($fighter)) {
            \Mordheim\BattleLogger::add("[Debug][PhaseShoot]{$fighter->getName()} не может стрелять так как в Close Combat.");
            return;
        }

        if ($this->spentShoot) {
            \Mordheim\BattleLogger::add("{$fighter->getName()} не может стрелять из-за потраченной фазы.");
        }

        $this->onShootPhase($battle, $fighter, $enemies);
        $this->spentShoot = true;
    }

    public function magicPhase(Battle $battle, Fighter $fighter, array $enemies): void
    {
        if (!$fighter->getState()->getStatus()->canAct()) {
            \Mordheim\BattleLogger::add("{$fighter->getName()} не может колдовать из-за состояния {$fighter->getState()->getStatus()->value}.");
            return;
        }

        if (count($fighter->getEquipmentManager()->getItemsBySlot(Slot::ARMOUR))) {
            \Mordheim\BattleLogger::add("{$fighter->getName()} не может колдовать из-за надетой брони.");
            return;
        }

        if ($fighter->getEquipmentManager()->hasItem(Equipment::SHIELD) || $fighter->getEquipmentManager()->hasItem(Equipment::BUCKLER)) {
            \Mordheim\BattleLogger::add("{$fighter->getName()} не может колдовать из-за надетого щита.");
            return;
        }

        if ($this->spentMagic) {
            \Mordheim\BattleLogger::add("{$fighter->getName()} не может колдовать из-за потраченной фазы.");
            return;

        }
        $this->onMagicPhase($battle, $fighter, $enemies);
        $this->spentMagic = true;
    }

    public function closeCombatPhase(Battle $battle, Fighter $fighter, array $enemies): void
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

    abstract protected function onMovePhase(Battle $battle, Fighter $fighter, array $enemies): void;

    abstract protected function onShootPhase(Battle $battle, Fighter $fighter, array $enemies): void;

    abstract protected function onMagicPhase(Battle $battle, Fighter $fighter, array $enemies): void;

    abstract protected function onCloseCombatPhase(Battle $battle, Fighter $fighter, array $enemies): void;
}
