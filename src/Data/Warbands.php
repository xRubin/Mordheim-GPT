<?php
namespace Mordheim\Data;

use Mordheim\Warband;
use Mordheim\Fighter;
use Mordheim\Characteristics;
use Mordheim\Skill;
use Mordheim\Weapon;
use Mordheim\EquipmentManager;
use Mordheim\Strategy\AggressiveStrategy;
use Mordheim\Data\Skills;

class Warbands
{
    /**
     * @return Warband[]
     */
    public static function getAll(): array
    {
        // Пример одной банды уровня 1a
        $humanStats = new Characteristics(4, 3, 3, 3, 3, 1, 3, 1, 7);
        return [
            new Warband('Reikland', [
                new Fighter('Captain', $humanStats, [Skills::getByName('Leader')], new EquipmentManager([Weapons::getByName('Sword')]), new AggressiveStrategy()),
                new Fighter('Youngblood', $humanStats, [], new EquipmentManager([Weapons::getByName('Dagger')]), new AggressiveStrategy()),
                // Здесь можно добавить других бойцов Reikland уровня 1a
            ]),
            new Warband('Marienburg', [
                new Fighter('Merchant Prince', $humanStats, [Skills::getByName('Wealth')], new EquipmentManager([Weapons::getByName('Sword')]), new AggressiveStrategy()),
                new Fighter('Marksman', $humanStats, [], new EquipmentManager([Weapons::getByName('Bow')]), new AggressiveStrategy()),
                // Здесь можно добавить других бойцов Marienburg уровня 1a
            ]),
            // Для расширения: добавить другие банды уровня 1a
        ];
    }
}
