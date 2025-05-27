<?php

namespace Mordheim\Classic;

use Mordheim\Classic\Battle;

interface BattleStrategyInterface
{
    public function resetOnTurn(): static;
    public function spentAll(): static;

    /**
     * Фаза движения
     */
    public function movePhase(Battle $battle, Fighter $fighter, array $enemies): void;

    /**
     * Фаза стрельбы
     */
    public function shootPhase(Battle $battle, Fighter $fighter, array $enemies): void;

    /**
     * Фаза магии
     */
    public function magicPhase(Battle $battle, Fighter $fighter, array $enemies): void;

    /**
     * Фаза рукопашного боя
     */
    public function closeCombatPhase(Battle $battle, Fighter $fighter, array $enemies): void;
}
