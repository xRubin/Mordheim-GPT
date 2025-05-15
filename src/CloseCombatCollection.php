<?php

namespace Mordheim;

use Mordheim\Exceptions\CloseCombatCollectionOutOfBoundsException;
use Mordheim\Exceptions\CloseCombatOutOfBoundsException;

/**
 * Класс для управления коллекцией рукопашных схваток
 * Позволяет добавлять/удалять схватки и находить все схватки для бойца
 */
class CloseCombatCollection
{
    /** @var CloseCombat[] */
    private array $combats = [];

    /**
     * Добавить новую схватку в коллекцию
     * @param CloseCombat $combat
     * @return static
     */
    public function add(CloseCombat $combat): static
    {
        $this->combats[] = $combat;
        \Mordheim\BattleLogger::add("[DEBUG][CloseCombat] добавление боя (" . implode(', ', array_map(fn ($fighter)=> $fighter->getName(), $combat->fighters)). ")");
        return $this;
    }

    /**
     * Удалить схватку из коллекции
     * @param CloseCombat $combat
     * @return static
     */
    public function remove(CloseCombat $combat): static
    {
        $index = array_search($combat, $this->combats);
        if (false === $index)
            throw new CloseCombatCollectionOutOfBoundsException();
        \Mordheim\BattleLogger::add("[DEBUG][CloseCombat] удаление боя (" . implode(', ', array_map(fn ($fighter)=> $fighter->getName(), $combat->fighters)). ")");
        unset($this->combats[$index]);
        $this->combats = array_values($this->combats);
        return $this;
    }

    /**
     * Получить все схватки для бойца
     * @param Fighter $fighter
     * @return CloseCombat[]
     */
    public function getByFighter(Fighter $fighter): array
    {
        return array_values(
            array_filter(
                $this->combats,
                function (CloseCombat $combat) use ($fighter) {
                    try {
                        $combat->getIndex($fighter);
                        return true;
                    } catch (CloseCombatOutOfBoundsException $e) {
                        return false;
                    }
                }
            )
        );
    }

    /**
     * Получить все активные схватки
     * @return CloseCombat[]
     */
    public function getAll(): array
    {
        return $this->combats;
    }

    /**
     * Проверить, участвует ли боец в какой-либо схватке
     * @param Fighter $fighter
     * @return bool
     */
    public function isFighterInCombat(Fighter $fighter): bool
    {
        return !empty($this->getByFighter($fighter));
    }

    /**
     * Очистить коллекцию
     * @return static
     */
    public function clear(): static
    {
        $this->combats = [];
        return $this;
    }
}
