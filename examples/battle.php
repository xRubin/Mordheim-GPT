<?php
/**
 * Пример эмуляции сражения двух банд по три бойца на чистом поле.
 * Все действия и перемещения выводятся в консоль.
 */

use Mordheim\Battle;
use Mordheim\BattleLogger;
use Mordheim\Data\Armors;
use Mordheim\Data\Weapons;
use Mordheim\EquipmentManager;
use Mordheim\GameField;
use Mordheim\Strategy\AggressiveStrategy;
use Mordheim\Warband;

require_once __DIR__ . '/../vendor/autoload.php';

$field = new GameField();
$battle = new Battle($field, [
    new Warband('MARIENBURG', [
        new \Mordheim\Fighter(
            \Mordheim\Data\Blank::MARIENBURG_MERCENARY_CAPTAIN,
            \Mordheim\FighterAdvancement::empty(),
            new EquipmentManager([Weapons::getByName('Sword')], [Armors::getByName('Light Armor')]),
            new \Mordheim\FighterState(
                [0, 0, 0],
                new AggressiveStrategy(),
                \Mordheim\Data\Blank::MARIENBURG_MERCENARY_CAPTAIN->getCharacteristics()->wounds
            )
        ),
        new \Mordheim\Fighter(
            \Mordheim\Data\Blank::MARIENBURG_YOUNGBLOOD,
            \Mordheim\FighterAdvancement::empty(),
            new EquipmentManager([Weapons::getByName('Axe')], [Armors::getByName('Heavy Armor')]),
            new \Mordheim\FighterState(
                [0, 2, 0],
                new AggressiveStrategy(),
                \Mordheim\Data\Blank::MARIENBURG_YOUNGBLOOD->getCharacteristics()->wounds
            )
        ),
        new \Mordheim\Fighter(
            \Mordheim\Data\Blank::MARIENBURG_MARKSMAN,
            \Mordheim\FighterAdvancement::empty(),
            new EquipmentManager([Weapons::getByName('Bow')]),
            new \Mordheim\FighterState(
                [0, 4, 0],
                new AggressiveStrategy(),
                \Mordheim\Data\Blank::MARIENBURG_MARKSMAN->getCharacteristics()->wounds
            )
        ),
    ]),
    new Warband('MIDDENHEIM', [
        new \Mordheim\Fighter(
            \Mordheim\Data\Blank::MIDDENHEIM_MERCENARY_CAPTAIN,
            \Mordheim\FighterAdvancement::empty(),
            new EquipmentManager([Weapons::getByName('Sword')], [Armors::getByName('Shield')]),
            new \Mordheim\FighterState(
                [7, 0, 0],
                new AggressiveStrategy(),
                \Mordheim\Data\Blank::MIDDENHEIM_MERCENARY_CAPTAIN->getCharacteristics()->wounds
            )
        ),
        new \Mordheim\Fighter(
            \Mordheim\Data\Blank::MIDDENHEIM_YOUNGBLOOD,
            \Mordheim\FighterAdvancement::empty(),
            new EquipmentManager([Weapons::getByName('Axe')], [Armors::getByName('Heavy Armor')]),
            new \Mordheim\FighterState(
                [7, 2, 0],
                new AggressiveStrategy(),
                \Mordheim\Data\Blank::MIDDENHEIM_YOUNGBLOOD->getCharacteristics()->wounds
            )
        ),
        new \Mordheim\Fighter(
            \Mordheim\Data\Blank::MIDDENHEIM_WARRIOR,
            \Mordheim\FighterAdvancement::empty(),
            new EquipmentManager([Weapons::getByName('Club')]),
            new \Mordheim\FighterState(
                [7, 4, 0],
                new AggressiveStrategy(),
                \Mordheim\Data\Blank::MIDDENHEIM_WARRIOR->getCharacteristics()->wounds
            )
        ),
    ])
]);

for ($turn = 1; $turn <= 6; ++$turn) {
    BattleLogger::clear();
    $battle->playTurn();
    BattleLogger::print();
}
