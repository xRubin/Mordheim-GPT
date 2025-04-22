<?php
namespace Mordheim;

use Mordheim\GameField;
use Mordheim\Warband;

class MordheimGame
{
    private GameField $field;
    /** @var Warband[] */
    private array $warbands = [];
    private int $currentTurn = 0;

    public function __construct(GameField $field, array $warbands)
    {
        $this->field = $field;
        $this->warbands = $warbands;
    }

    public function getField(): GameField
    {
        return $this->field;
    }

    public function getWarbands(): array
    {
        return $this->warbands;
    }

    /**
     * Выполнить следующий ход для всех бойцов
     * Учитывает стратегию, движение, атаку, стрельбу и правила
     */
    public function nextTurn(): void
    {
        $this->currentTurn++;
        $field = $this->field;
        $allFighters = [];
        foreach ($this->warbands as $warband) {
            foreach ($warband->fighters as $fighter) {
                if ($fighter->alive) {
                    $allFighters[] = $fighter;
                }
            }
        }
        // Для простоты: каждый боец делает ход
        foreach ($allFighters as $fighter) {
            // Находим врагов
            $enemies = array_filter($allFighters, fn($f) => $f !== $fighter && $f->alive && $this->getWarbandOfFighter($f) !== $this->getWarbandOfFighter($fighter));
            if (empty($enemies)) continue;
            // Паттерн Strategy: каждый боец сам решает, что делать
            $fighter->battleStrategy->executeTurn($fighter, array_values($enemies), $field);
        }
    }

    /**
     * Найти банду, к которой принадлежит боец
     */
    private function getWarbandOfFighter($fighter): ?Warband
    {
        foreach ($this->warbands as $warband) {
            if (in_array($fighter, $warband->fighters, true)) {
                return $warband;
            }
        }
        return null;
    }

    public function getCurrentTurn(): int
    {
        return $this->currentTurn;
    }
}
