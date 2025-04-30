<?php

namespace Mordheim;

use Mordheim\Exceptions\CloseCombatOutOfBoundsException;

/**
 * Класс для хранения информации о рукопашной схватке между двумя бойцами
 * Хранит ссылки на бойцов, бонусы (charge, парирование и др.), счетчик раундов и статусы
 */
class CloseCombat
{
    /** @var Fighter */
    /** @var Fighter[] */
    public array $fighters;
    /** @var int */
    public int $rounds = 0;
    /** @var array<string, array> */
    public array $bonuses = [];

    /**
     * @param Fighter $fighter1
     * @param Fighter $fighter2
     */
    public function __construct(Fighter $fighter1, Fighter $fighter2)
    {
        $this->fighters = [$fighter1, $fighter2];
        $this->rounds = 1;
        $this->addBonus($fighter1, 'toHitMod', 1);
    }

    /**
     * Увеличить счетчик раундов схватки
     */
    public function nextRound(): void
    {
        $this->bonuses = [];
        $this->rounds++;
    }

    /**
     * Добавить бонус бойцу (например, за charge)
     */
    public function addBonus(Fighter $fighter, string $bonus, $value = true): static
    {
        $this->bonuses[$this->getIndex($fighter)][$bonus] = $value;
        return $this;
    }

    /**
     * Получить бонусы бойца
     */
    public function getBonuses(Fighter $fighter): array
    {
        return $this->bonuses[$this->getIndex($fighter)] ?? [];
    }

    /**
     * Получить индекс бойца
     */
    public function getIndex(Fighter $fighter): int
    {
        $result = array_search($fighter, $this->fighters);
        if (false === $result)
            throw new CloseCombatOutOfBoundsException();
        return $result;
    }
}
