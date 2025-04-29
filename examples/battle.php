<?php
/**
 * Пример эмуляции сражения двух банд по три бойца на чистом поле.
 * Все действия и перемещения выводятся в консоль.
 */

use Mordheim\Battle;
use Mordheim\BattleLogger;
use Mordheim\Characteristics;
use Mordheim\Data\Armors;
use Mordheim\Data\Weapons;
use Mordheim\EquipmentManager;
use Mordheim\Fighter;
use Mordheim\GameField;
use Mordheim\Strategy\AggressiveStrategy;
use Mordheim\Warband;

require_once __DIR__ . '/../vendor/autoload.php';

function createFighter($name, $pos, $weapons = [], $armors = [])
{
    $char = new Characteristics(4, 3, 3, 3, 3, 1, 3, 1, 7);
    return new Fighter($name, $char, [], new EquipmentManager($weapons, $armors), new AggressiveStrategy(), $pos);
}

function findEnemies($fighter, $warbands)
{
    $enemies = [];
    foreach ($warbands as $wb) {
        if (!in_array($fighter, $wb->fighters, true)) {
            foreach ($wb->fighters as $f) {
                if ($f->alive) $enemies[] = $f;
            }
        }
    }
    return $enemies;
}

$field = new GameField();
$battle = new Battle($field, [
    new Warband('Red', [
        createFighter('Red1', [0, 0, 0], [Weapons::getByName('Sword')], [Armors::getByName('Light Armor')]),
        createFighter('Red2', [0, 2, 0], [Weapons::getByName('Axe')], [Armors::getByName('Heavy Armor')]),
        createFighter('Red3', [0, 4, 0], [Weapons::getByName('Bow')]),
    ]),
    new Warband('Blue', [
        createFighter('Blue1', [7, 0, 0], [Weapons::getByName('Sword')], [Armors::getByName('Shield')]),
        createFighter('Blue2', [7, 2, 0], [Weapons::getByName('Axe')], [Armors::getByName('Heavy Armor')]),
        createFighter('Blue3', [7, 4, 0], [Weapons::getByName('Club')]),
    ])
]);

for ($turn = 1; $turn <= 6; ++$turn) {
    BattleLogger::clear();
    $battle->playTurn();
    BattleLogger::print();
    $battle->nextWarband();
}
