<?php

namespace Mordheim;

interface BattleStrategyInterface
{
    public function resetOnTurn(): static;

    /**
     * Фаза движения
     */
    public function movePhase(Battle $battle, FighterInterface $fighter, array $enemies): void;

    /**
     * Фаза стрельбы
     */
    public function shootPhase(Battle $battle, FighterInterface $fighter, array $enemies): void;

    /**
     * Фаза магии
     */
    public function magicPhase(Battle $battle, FighterInterface $fighter, array $enemies): void;

    /**
     * Фаза рукопашного боя
     */
    public function closeCombatPhase(Battle $battle, FighterInterface $fighter, array $enemies): void;
}
