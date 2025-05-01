<?php

namespace Mordheim\Strategy;

use Mordheim\Battle;
use Mordheim\Exceptions\FighterAbnormalStateException;
use Mordheim\Fighter;
use Mordheim\FighterState;
use Mordheim\GameField;

abstract class BaseBattleStrategy implements BattleStrategyInterface
{
    public $spentMove = false;
    public $spentCharge = false;
    public $spentShoot = false;
    public $spentMagic = false;
    public $spentCloseCombat = false;


    public function resetOnTurn(): static
    {
        $this->spentMove = false;
        $this->spentCharge = false;
        $this->spentShoot = false;
        $this->spentMagic = false;
        $this->spentCloseCombat = false;
        return $this;
    }

    /**
     * Найти ближайшего врага
     */
    protected function getNearestEnemy(Fighter $fighter, array $enemies): ?Fighter
    {
        if (empty($enemies)) return null;
        usort($enemies, fn($a, $b) => $fighter->distance($a) <=> $fighter->distance($b));
        return $enemies[0];
    }

    /**
     * Проверить страх/ужас. Вернёт true если можно действовать
     */
    protected function canActAgainst(Battle $battle, Fighter $fighter, Fighter $target): bool
    {
        $allies = $battle->getAlliesFor($fighter);
        if ($target->hasSkill('Terror'))
            return \Mordheim\Rule\Psychology::testTerror($fighter, $allies);
        if ($target->hasSkill('Fear'))
            return \Mordheim\Rule\Psychology::testFear($fighter, $target, $allies);
        return true;
    }

    /**
     * Получить стрелковое оружие и его радиус
     */
    protected function getRangedWeapon(Fighter $fighter): ?\Mordheim\Weapon
    {
        foreach ($fighter->equipmentManager->getWeapons() as $weapon) {
            if ($weapon->damageType === 'Ranged') {
                return $weapon;
            }
        }
        return null;
    }

    public function movePhase(Battle $battle, Fighter $fighter, array $enemies): void
    {
        try {
            $fighter->state->canAct();
        } catch (FighterAbnormalStateException $e) {
            if ($e->getState() === FighterState::KNOCKED_DOWN) {
                if ($this->spentMove = \Mordheim\Rule\StandUp::apply($fighter)) {
                    // потратили вставание по общим правилам
                    $this->spentCharge = true;
                    $this->spentCloseCombat = true;
                }
            } else {
                \Mordheim\BattleLogger::add("{$fighter->name} не может действовать из-за состояния {$fighter->state->value}.");
                return;
            }
        }

        if ($battle->getActiveCombats()->isFighterInCombat($fighter))
            return;

        if (!$this->spentMove)
            $this->onMovePhase($battle, $fighter, $enemies);

        $this->spentMove = true;
    }

    public function shootPhase(Battle $battle, Fighter $fighter, array $enemies): void
    {
        try {
            $fighter->state->canAct();
        } catch (FighterAbnormalStateException $e) {
            \Mordheim\BattleLogger::add("{$fighter->name} не может действовать из-за состояния {$fighter->state->value}.");
            return;
        }

        if ($battle->getActiveCombats()->isFighterInCombat($fighter))
            return;

        if (!$this->spentShoot)
            $this->onShootPhase($battle, $fighter, $enemies);

        $this->spentShoot = true;
    }

    public function magicPhase(Battle $battle, Fighter $fighter, array $enemies): void
    {
        try {
            $fighter->state->canAct();
        } catch (FighterAbnormalStateException $e) {
            \Mordheim\BattleLogger::add("{$fighter->name} не может действовать из-за состояния {$fighter->state->value}.");
            return;
        }

        if (!$this->spentMagic)
            $this->onMagicPhase($battle, $fighter, $enemies);

        $this->spentMagic = true;
    }

    public function closeCombatPhase(Battle $battle, Fighter $fighter, array $enemies): void
    {
        try {
            $fighter->state->canAct();
        } catch (FighterAbnormalStateException $e) {
            \Mordheim\BattleLogger::add("{$fighter->name} не может действовать из-за состояния {$fighter->state->value}.");
            return;
        }

        if (!$this->spentCloseCombat)
            $this->onCloseCombatPhase($battle, $fighter, $enemies);

        $this->spentCloseCombat = true;
    }

    abstract protected function onMovePhase(Battle $battle, Fighter $fighter, array $enemies): void;
    abstract protected function onShootPhase(Battle $battle, Fighter $fighter, array $enemies): void;
    abstract protected function onMagicPhase(Battle $battle, Fighter $fighter, array $enemies): void;
    abstract protected function onCloseCombatPhase(Battle $battle, Fighter $fighter, array $enemies): void;
}
