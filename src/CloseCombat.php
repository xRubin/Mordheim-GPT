<?php

namespace Mordheim;

use Mordheim\Exceptions\CloseCombatOutOfBoundsException;

/**
 * Класс для хранения информации о рукопашной схватке между двумя бойцами
 * Хранит ссылки на бойцов, бонусы (charge, парирование и др.), счетчик раундов и статусы
 */
class CloseCombat
{
    const BONUS_TO_HIT = 'toHit';
    const BONUS_CHARGED = 'charged';

    /** @var FighterInterface[] */
    public array $fighters;
    /** @var int */
    public int $rounds = 0;
    /** @var array<string, array> */
    public array $bonuses = [];

    /**
     * @param FighterInterface $fighter1
     * @param FighterInterface $fighter2
     */
    public function __construct(FighterInterface $fighter1, FighterInterface $fighter2)
    {
        $this->fighters = [$fighter1, $fighter2];
        $this->rounds = 1;
        $this->addBonus($fighter1, self::BONUS_TO_HIT, 1);
        $this->addBonus($fighter1, self::BONUS_CHARGED);
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
    public function addBonus(FighterInterface $fighter, string $bonus, $value = true): static
    {
        $this->bonuses[$this->getIndex($fighter)][$bonus] = $value;
        return $this;
    }

    /**
     * Получить бонусы бойца
     */
    public function getBonuses(FighterInterface $fighter): array
    {
        return $this->bonuses[$this->getIndex($fighter)] ?? [];
    }

    /**
     * Получить указанный бонус бойца
     */
    public function getBonus(FighterInterface $fighter, string $bonusName): mixed
    {
        return $this->getBonuses($fighter)[$bonusName] ?? null;
    }

    /**
     * Получить индекс бойца
     */
    public function getIndex(FighterInterface $fighter): int
    {
        $result = array_search($fighter, $this->fighters);
        if (false === $result)
            throw new CloseCombatOutOfBoundsException();
        return $result;
    }
}
