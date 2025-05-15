<?php
/**
 * Пример эмуляции сражения двух банд по три бойца на чистом поле.
 * Все действия и перемещения выводятся в консоль.
 */

use Mordheim\Battle;
use Mordheim\BattleLogger;
use Mordheim\Data\Equipment;
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
            new EquipmentManager([Equipment::SWORD, Equipment::LIGHT_ARMOUR]),
            new \Mordheim\FighterState(
                [0, 0, 0],
                new AggressiveStrategy(),
                \Mordheim\Data\Blank::MARIENBURG_MERCENARY_CAPTAIN->getCharacteristics()->getWounds()
            )
        ),
        new \Mordheim\Fighter(
            \Mordheim\Data\Blank::WARLOCK,
            \Mordheim\FighterAdvancement::empty()->addSpell(\Mordheim\WizardSpell::create(\Mordheim\Data\Spell::FIRES_OF_UZHUL)),
            new EquipmentManager([Equipment::STAFF]),
            new \Mordheim\FighterState(
                [0, 2, 0],
                new \Mordheim\Strategy\CarefulStrategy(),
                \Mordheim\Data\Blank::MARIENBURG_YOUNGBLOOD->getCharacteristics()->getWounds()
            )
        ),
        new \Mordheim\Fighter(
            \Mordheim\Data\Blank::MARIENBURG_MARKSMAN,
            \Mordheim\FighterAdvancement::empty(),
            new EquipmentManager([Equipment::BOW]),
            new \Mordheim\FighterState(
                [0, 4, 0],
                new \Mordheim\Strategy\CowardlyStrategy(),
                \Mordheim\Data\Blank::MARIENBURG_MARKSMAN->getCharacteristics()->getWounds()
            )
        ),
        new \Mordheim\Fighter(
            \Mordheim\Data\Blank::MARIENBURG_YOUNGBLOOD,
            \Mordheim\FighterAdvancement::empty(),
            new EquipmentManager([Equipment::AXE, Equipment::HEAVY_ARMOUR]),
            new \Mordheim\FighterState(
                [0, 6, 0],
                new AggressiveStrategy(),
                \Mordheim\Data\Blank::MARIENBURG_YOUNGBLOOD->getCharacteristics()->getWounds()
            )
        ),
    ]),
    new Warband('MIDDENHEIM', [
        new \Mordheim\Fighter(
            \Mordheim\Data\Blank::MIDDENHEIM_MERCENARY_CAPTAIN,
            \Mordheim\FighterAdvancement::empty(),
            new EquipmentManager([Equipment::SWORD, Equipment::SHIELD]),
            new \Mordheim\FighterState(
                [7, 0, 0],
                new AggressiveStrategy(),
                \Mordheim\Data\Blank::MIDDENHEIM_MERCENARY_CAPTAIN->getCharacteristics()->getWounds()
            )
        ),
        new \Mordheim\Fighter(
            \Mordheim\Data\Blank::MIDDENHEIM_YOUNGBLOOD,
            \Mordheim\FighterAdvancement::empty(),
            new EquipmentManager([Equipment::AXE, Equipment::HEAVY_ARMOUR]),
            new \Mordheim\FighterState(
                [7, 2, 0],
                new AggressiveStrategy(),
                \Mordheim\Data\Blank::MIDDENHEIM_YOUNGBLOOD->getCharacteristics()->getWounds()
            )
        ),
        new \Mordheim\Fighter(
            \Mordheim\Data\Blank::WARLOCK,
            \Mordheim\FighterAdvancement::empty()->addSpell(\Mordheim\WizardSpell::create(\Mordheim\Data\Spell::SWORD_OF_REZHEBEL)),
            new EquipmentManager([Equipment::STAFF]),
            new \Mordheim\FighterState(
                [7, 4, 0],
                new AggressiveStrategy(),
                \Mordheim\Data\Blank::MARIENBURG_YOUNGBLOOD->getCharacteristics()->getWounds()
            )
        ),
        new \Mordheim\Fighter(
            \Mordheim\Data\Blank::MIDDENHEIM_WARRIOR,
            \Mordheim\FighterAdvancement::empty(),
            new EquipmentManager([Equipment::CLUB]),
            new \Mordheim\FighterState(
                [7, 6, 0],
                new AggressiveStrategy(),
                \Mordheim\Data\Blank::MIDDENHEIM_WARRIOR->getCharacteristics()->getWounds()
            )
        ),
    ])
]);

for ($turn = 1; $turn <= 6; ++$turn) {
    BattleLogger::clear();
    $battle->playTurn();
    BattleLogger::print();
}
