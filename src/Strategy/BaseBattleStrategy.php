<?php
namespace Mordheim\Strategy;

use Mordheim\Fighter;
use Mordheim\FighterState;
use Mordheim\GameField;

abstract class BaseBattleStrategy implements BattleStrategy
{
    public $movedThisTurn = false;


    public function resetOnTurn(): static
    {
        $this->movedThisTurn = false;
        return $this;
    }

    /**
     * Найти ближайшего врага
     */
    protected function getNearestEnemy(Fighter $fighter, array $enemies): ?Fighter
    {
        if (empty($enemies)) return null;
        usort($enemies, fn($a, $b) => $fighter->distance($a) <=> $fighter->distance($b));
        return $enemies[0];
    }

    /**
     * Проверить страх/ужас. Вернёт true если можно действовать
     */
    protected function canActAgainst(Fighter $fighter, Fighter $target, GameField $field = null): bool
    {
        if ($field !== null && method_exists($field, 'getAllies')) {
            $allies = $field->getAllies($fighter);
            if (method_exists($target, 'causesTerror') && $target->causesTerror()) {
                return \Mordheim\Rule\Psychology::testTerror($fighter, $allies);
            } elseif (method_exists($target, 'causesFear') && $target->causesFear()) {
                return \Mordheim\Rule\Psychology::testFear($fighter, $target, $allies);
            }
        } else {
            // Backward compatibility: no allies context
            if (method_exists($target, 'causesTerror') && $target->causesTerror()) {
                return \Mordheim\Rule\Psychology::testTerror($fighter);
            } elseif (method_exists($target, 'causesFear') && $target->causesFear()) {
                return \Mordheim\Rule\Psychology::testFear($fighter, $target);
            }
        }
        return true;
    }

    /**
     * Получить стрелковое оружие и его радиус
     */
    protected function getRangedWeapon(Fighter $fighter): ?\Mordheim\Weapon
    {
        foreach ($fighter->equipmentManager->getWeapons() as $weapon) {
            if ($weapon->damageType === 'Ranged') {
                return $weapon;
            }
        }
        return null;
    }
}
