<?php
namespace Mordheim\Strategy;

use Mordheim\Battle;
use Mordheim\Fighter;
use Mordheim\GameField;

interface BattleStrategyInterface
{
    public function resetOnTurn(): static;
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
