<?php

namespace Mordheim;

class FighterState implements FighterStateInterface
{
    /**
     * Активные заклинания на персонаже
     * @var SpellInterface[]
     */
    private array $activeSpells = [];

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

    public function setWounds(int $wounds): static
    {
        $this->wounds = $wounds;
        return $this;
    }

    /**
     * Получить активные заклинания
     * @return SpellInterface[]
     */
    public function getActiveSpells(): array
    {
        return $this->activeSpells;
    }

    public function hasActiveSpell(SpellInterface $spell): bool
    {
        return in_array($spell, $this->activeSpells, true);
    }

    /**
     * Установить активные заклинания
     * @param SpellInterface[] $spells
     * @return $this
     */
    public function setActiveSpells(array $spells): static
    {
        $this->activeSpells = $spells;
        return $this;
    }

    /**
     * Добавить заклинание
     * @param SpellInterface $spell
     * @return $this
     */
    public function addActiveSpell(SpellInterface $spell): static
    {
        if (!in_array($spell, $this->activeSpells, true)) {
            $this->activeSpells[] = $spell;
        }
        return $this;
    }

    /**
     * Удалить заклинание
     * @param SpellInterface $spell
     * @return $this
     */
    public function removeActiveSpell(SpellInterface $spell): static
    {
        $this->activeSpells = array_filter(
            $this->activeSpells,
            fn($s) => $s !== $spell
        );
        return $this;
    }

    public function hasSpecialRule(SpecialRule $specialRule): bool
    {
        foreach ($this->getActiveSpells() as $spell) {
            if (in_array($specialRule, $spell->getStateRules()))
                return true;
        }
        return false;
    }
}