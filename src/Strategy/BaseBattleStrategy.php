<?php
namespace Mordheim\Strategy;

use Mordheim\Fighter;
use Mordheim\GameField;

abstract class BaseBattleStrategy implements BattleStrategy
{
    /**
     * Найти ближайшего врага
     */
    protected function getNearestEnemy(Fighter $self, array $enemies): ?Fighter
    {
        if (empty($enemies)) return null;
        usort($enemies, fn($a, $b) => $self->distance($a) <=> $self->distance($b));
        return $enemies[0];
    }

    /**
     * Проверить страх/ужас. Вернёт true если можно действовать
     */
    protected function canActAgainst(Fighter $self, Fighter $target, GameField $field = null): bool
    {
        if ($field !== null && method_exists($field, 'getAllies')) {
            $allies = $field->getAllies($self);
            if (method_exists($target, 'causesTerror') && $target->causesTerror()) {
                return \Mordheim\Psychology::testTerror($self, $allies);
            } elseif (method_exists($target, 'causesFear') && $target->causesFear()) {
                return \Mordheim\Psychology::testFear($self, $target, $allies);
            }
        } else {
            // Backward compatibility: no allies context
            if (method_exists($target, 'causesTerror') && $target->causesTerror()) {
                return \Mordheim\Psychology::testTerror($self);
            } elseif (method_exists($target, 'causesFear') && $target->causesFear()) {
                return \Mordheim\Psychology::testFear($self, $target);
            }
        }
        return true;
    }

    /**
     * Получить стрелковое оружие и его радиус
     */
    protected function getRangedWeapon(Fighter $self): ?\Mordheim\Weapon
    {
        foreach ($self->equipmentManager->getWeapons() as $w) {
            if ($w->damageType === 'Ranged') {
                return $w;
            }
        }
        return null;
    }
}
