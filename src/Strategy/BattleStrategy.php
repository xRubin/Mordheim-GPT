<?php
namespace Mordheim\Strategy;

use Mordheim\Fighter;
use Mordheim\GameField;

interface BattleStrategy
{
    /**
     * Выполнить ход бойца согласно стратегии
     * @param Fighter $self
     * @param Fighter[] $enemies
     * @param GameField $field
     */
    public function executeTurn(Fighter $self, array $enemies, GameField $field): void;
}
