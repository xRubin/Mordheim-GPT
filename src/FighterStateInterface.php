<?php

namespace Mordheim;

interface FighterStateInterface
{
    public function getPosition(): array;

    public function setPosition(array $position): static;

    public function getBattleStrategy(): BattleStrategyInterface;

    public function setBattleStrategy(BattleStrategyInterface $battleStrategy): static;

    public function isAlive(): bool;

    public function getStatus(): Status;

    public function setStatus(Status $status): static;

    public function getWounds(): int;
    public function decreaseWounds(int $step = 1): static;
}