<?php

namespace Mordheim;

use Mordheim\Strategy\BattleStrategyInterface;

class FighterState implements FighterStateInterface
{
    public function __construct(
        private array                   $position,
        private BattleStrategyInterface $battleStrategy,
        private int                     $wounds,
        private Status                  $status = Status::STANDING,
    )
    {

    }

    public function getPosition(): array
    {
        return $this->position;
    }

    public function setPosition(array $position): static
    {
        $this->position = $position;
        return $this;
    }

    public function getBattleStrategy(): BattleStrategyInterface
    {
        return $this->battleStrategy;
    }

    public function setBattleStrategy(BattleStrategyInterface $battleStrategy): static
    {
        $this->battleStrategy = $battleStrategy;
        return $this;
    }

    public function getStatus(): Status
    {
        return $this->status;
    }

    public function setStatus(Status $status): static
    {
        $this->status = $status;
        if ($status == Status::OUT_OF_ACTION) {
            $this->wounds = 0;
        }
        return $this;
    }

    public function isAlive(): bool
    {
        return $this->getStatus() !== Status::OUT_OF_ACTION;
    }

    public function getWounds(): int
    {
        if (!$this->isAlive())
            return 0;
        return $this->wounds;
    }

    public function decreaseWounds(int $step = 1): static
    {
        $this->wounds -= $step;
        return $this;
    }
}