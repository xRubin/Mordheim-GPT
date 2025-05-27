<?php

namespace Mordheim;

abstract class Scenario
{
    protected Band $attackerBand;
    protected Band $defenderBand;
    public function __construct(
        private readonly GameField $gameField,
        private readonly array $warbands,
    )
    {
        $this->attackerBand = $this->isAttacker($warbands[0]) ? $warbands[0] : $this->warbands[1];
        $this->defenderBand = $this->isAttacker($warbands[0]) ? $warbands[1] : $this->warbands[0];
    }

    public function getGameField(): GameField
    {
        return $this->gameField;
    }

    public function getWarbands(): array
    {
        return $this->warbands;
    }

    abstract public function isAttacker(Band $band): bool;
}