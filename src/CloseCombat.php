<?php

namespace Mordheim;

/**
 * Класс для хранения информации о рукопашной схватке между двумя бойцами
 * Хранит ссылки на бойцов, бонусы (charge, парирование и др.), счетчик раундов и статусы
 */
class CloseCombat
{
    /** @var Fighter */
    public Fighter $attacker;
    /** @var Fighter */
    public Fighter $defender;
    /** @var bool */
    public bool $attackerCharged = false;
    /** @var bool */
    public bool $defenderCharged = false;
    /** @var int */
    public int $rounds = 0;
    /** @var array */
    public array $bonuses = [];

    public function __construct(Fighter $attacker, Fighter $defender, bool $attackerCharged = false, bool $defenderCharged = false)
    {
        $this->attacker = $attacker;
        $this->defender = $defender;
        $this->attackerCharged = $attackerCharged;
        $this->defenderCharged = $defenderCharged;
        $this->rounds = 1;
        $this->bonuses = [
            $attacker->name => [],
            $defender->name => [],
        ];
    }

    /**
     * Увеличить счетчик раундов схватки
     */
    public function nextRound(): void
    {
        $this->rounds++;
    }

    /**
     * Добавить бонус бойцу (например, за charge)
     */
    public function addBonus(Fighter $fighter, string $bonus, $value = true): void
    {
        $this->bonuses[$fighter->name][$bonus] = $value;
    }

    /**
     * Получить бонусы бойца
     */
    public function getBonuses(Fighter $fighter): array
    {
        return $this->bonuses[$fighter->name] ?? [];
    }

    /**
     * Проверить, был ли charge у бойца
     */
    public function isCharged(Fighter $fighter): bool
    {
        if ($this->attacker === $fighter) return $this->attackerCharged;
        if ($this->defender === $fighter) return $this->defenderCharged;
        return false;
    }
}
