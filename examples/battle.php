<?php
/**
 * Пример эмуляции сражения двух банд по три бойца на чистом поле.
 * Все действия и перемещения выводятся в консоль.
 */

use Mordheim\Band;
use Mordheim\BattleLogger;
use Mordheim\Classic\Battle;
use Mordheim\Classic\EquipmentManager;
use Mordheim\Equipment;
use Mordheim\GameField;
use Mordheim\Strategy\AggressiveStrategy;

require_once __DIR__ . '/../vendor/autoload.php';

$field = new GameField();
$battle = new Battle($field, [
    new Band('MARIENBURG', [
        new \Mordheim\Classic\Fighter(
            Mordheim\Classic\Blank::MARIENBURG_MERCENARY_CAPTAIN,
            \Mordheim\Classic\FighterAdvancement::empty(),
            new EquipmentManager([Equipment::SWORD, Equipment::LIGHT_ARMOUR]),
            new \Mordheim\Classic\FighterState(
                [0, 0, 0],
                new AggressiveStrategy(),
                Mordheim\Classic\Blank::MARIENBURG_MERCENARY_CAPTAIN->getCharacteristics()->getWounds()
            )
        ),
        new \Mordheim\Classic\Fighter(
            Mordheim\Classic\Blank::WARLOCK,
            \Mordheim\Classic\FighterAdvancement::empty()->addSpell(\Mordheim\Classic\WizardSpell::create(\Mordheim\Classic\Spell::FIRES_OF_UZHUL)),
            new EquipmentManager([Equipment::STAFF]),
            new \Mordheim\Classic\FighterState(
                [0, 2, 0],
                new \Mordheim\Strategy\CarefulStrategy(),
                Mordheim\Classic\Blank::MARIENBURG_YOUNGBLOOD->getCharacteristics()->getWounds()
            )
        ),
        new \Mordheim\Classic\Fighter(
            Mordheim\Classic\Blank::MARIENBURG_MARKSMAN,
            \Mordheim\Classic\FighterAdvancement::empty(),
            new EquipmentManager([Equipment::BOW]),
            new \Mordheim\Classic\FighterState(
                [0, 4, 0],
                new \Mordheim\Classic\Strategy\CowardlyStrategy(),
                Mordheim\Classic\Blank::MARIENBURG_MARKSMAN->getCharacteristics()->getWounds()
            )
        ),
        new \Mordheim\Classic\Fighter(
            Mordheim\Classic\Blank::MARIENBURG_YOUNGBLOOD,
            \Mordheim\Classic\FighterAdvancement::empty(),
            new EquipmentManager([Equipment::AXE, Equipment::HEAVY_ARMOUR]),
            new \Mordheim\Classic\FighterState(
                [0, 6, 0],
                new AggressiveStrategy(),
                Mordheim\Classic\Blank::MARIENBURG_YOUNGBLOOD->getCharacteristics()->getWounds()
            )
        ),
    ]),
    new Band('MIDDENHEIM', [
        new \Mordheim\Classic\Fighter(
            Mordheim\Classic\Blank::MIDDENHEIM_MERCENARY_CAPTAIN,
            \Mordheim\Classic\FighterAdvancement::empty(),
            new EquipmentManager([Equipment::SWORD, Equipment::SHIELD]),
            new \Mordheim\Classic\FighterState(
                [7, 0, 0],
                new AggressiveStrategy(),
                Mordheim\Classic\Blank::MIDDENHEIM_MERCENARY_CAPTAIN->getCharacteristics()->getWounds()
            )
        ),
        new \Mordheim\Classic\Fighter(
            Mordheim\Classic\Blank::MIDDENHEIM_YOUNGBLOOD,
            \Mordheim\Classic\FighterAdvancement::empty(),
            new EquipmentManager([Equipment::AXE, Equipment::HEAVY_ARMOUR]),
            new \Mordheim\Classic\FighterState(
                [7, 2, 0],
                new AggressiveStrategy(),
                Mordheim\Classic\Blank::MIDDENHEIM_YOUNGBLOOD->getCharacteristics()->getWounds()
            )
        ),
        new \Mordheim\Classic\Fighter(
            Mordheim\Classic\Blank::WARLOCK,
            \Mordheim\Classic\FighterAdvancement::empty()->addSpell(\Mordheim\Classic\WizardSpell::create(\Mordheim\Classic\Spell::SWORD_OF_REZHEBEL)),
            new EquipmentManager([Equipment::STAFF]),
            new \Mordheim\Classic\FighterState(
                [7, 4, 0],
                new AggressiveStrategy(),
                Mordheim\Classic\Blank::MARIENBURG_YOUNGBLOOD->getCharacteristics()->getWounds()
            )
        ),
        new \Mordheim\Classic\Fighter(
            Mordheim\Classic\Blank::MIDDENHEIM_WARRIOR,
            \Mordheim\Classic\FighterAdvancement::empty(),
            new EquipmentManager([Equipment::CLUB]),
            new \Mordheim\Classic\FighterState(
                [7, 6, 0],
                new AggressiveStrategy(),
                Mordheim\Classic\Blank::MIDDENHEIM_WARRIOR->getCharacteristics()->getWounds()
            )
        ),
    ])
]);

for ($turn = 1; $turn <= 8; ++$turn) {
    BattleLogger::clear();
    $battle->playTurn();
    BattleLogger::print();
}
