<?php

namespace Mordheim\Data;

use Mordheim\Characteristics;
use Mordheim\EquipmentManager;
use Mordheim\Fighter;
use Mordheim\Strategy\AggressiveStrategy;
use Mordheim\Warband;

class Warbands
{
    /**
     * @return Warband[]
     */
    public static function getAll(): array
    {
        return [
            new Warband('Reikland', [
                new Fighter('Mercenary Captain',
                    new Characteristics(4, 4, 4, 3, 3, 1, 4, 1, 8),
                    [Skills::getByName('Leader')], new EquipmentManager([Weapons::getByName('Dagger')]), new AggressiveStrategy()
                ),
                new Fighter('Champion',
                    new Characteristics(4, 4, 3, 3, 3, 1, 3, 1, 7),
                    [], new EquipmentManager([Weapons::getByName('Dagger')]), new AggressiveStrategy()
                ),
                new Fighter('Youngblood',
                    new Characteristics(4, 2, 2, 3, 3, 1, 3, 1, 6),
                    [], new EquipmentManager([Weapons::getByName('Dagger')]), new AggressiveStrategy()
                ),
                new Fighter('Warrior',
                    new Characteristics(4, 3, 3, 3, 3, 1, 3, 1, 7),
                    [], new EquipmentManager([Weapons::getByName('Dagger')]), new AggressiveStrategy()
                ),
                new Fighter('Marksman',
                    new Characteristics(4, 3, 3, 3, 3, 1, 3, 1, 7),
                    [], new EquipmentManager([Weapons::getByName('Dagger')]), new AggressiveStrategy()
                ),
                new Fighter('Swordsman',
                    new Characteristics(4, 3, 3, 3, 3, 1, 3, 1, 7),
                    [Skills::getByName('Expert Swordsman')], new EquipmentManager([Weapons::getByName('Dagger')]), new AggressiveStrategy()
                ),
            ]),
            new Warband('Marienburg', [
                new Fighter('Mercenary Captain',
                    new Characteristics(4, 4, 4, 3, 3, 1, 4, 1, 8),
                    [Skills::getByName('Leader')], new EquipmentManager([Weapons::getByName('Dagger')]), new AggressiveStrategy()
                ),
                new Fighter('Champion',
                    new Characteristics(4, 4, 3, 3, 3, 1, 3, 1, 7),
                    [], new EquipmentManager([Weapons::getByName('Dagger')]), new AggressiveStrategy()
                ),
                new Fighter('Youngblood',
                    new Characteristics(4, 2, 2, 3, 3, 1, 3, 1, 6),
                    [], new EquipmentManager([Weapons::getByName('Dagger')]), new AggressiveStrategy()
                ),
                new Fighter('Warrior',
                    new Characteristics(4, 3, 3, 3, 3, 1, 3, 1, 7),
                    [], new EquipmentManager([Weapons::getByName('Dagger')]), new AggressiveStrategy()
                ),
                new Fighter('Marksman',
                    new Characteristics(4, 3, 3, 3, 3, 1, 3, 1, 7),
                    [], new EquipmentManager([Weapons::getByName('Dagger')]), new AggressiveStrategy()
                ),
                new Fighter('Swordsman',
                    new Characteristics(4, 3, 3, 3, 3, 1, 3, 1, 7),
                    [Skills::getByName('Expert Swordsman')], new EquipmentManager([Weapons::getByName('Dagger')]), new AggressiveStrategy()
                ),
            ]),
        ];
    }
}
