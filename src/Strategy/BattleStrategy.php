<?php
namespace Mordheim\Strategy;

use Mordheim\Fighter;
use Mordheim\GameField;

interface BattleStrategy
{
    /**
     * Фаза движения
     */
    public function movePhase(Fighter $self, array $enemies, GameField $field): void;
    /**
     * Фаза стрельбы
     */
    public function shootPhase(Fighter $self, array $enemies, GameField $field): void;
    /**
     * Фаза магии
     */
    public function magicPhase(Fighter $self, array $enemies, GameField $field): void;
    /**
     * Фаза рукопашного боя
     */
    public function closeCombatPhase(Fighter $self, array $enemies, GameField $field): void;
}
