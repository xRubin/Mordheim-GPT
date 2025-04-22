<?php

namespace Mordheim\Data;

use Mordheim\Characteristics;
use Mordheim\EquipmentManager;
use Mordheim\Skill;
use Mordheim\Data\Skills;

class HiredSwords
{
    /**
     * Возвращает массив профилей наемников категории 1a (Tilean Marksman, Elf Ranger, Halfling Scout, Ogre Bodyguard, Dwarf Troll Slayer)
     * Каждый профиль — ассоциативный массив с характеристиками, навыками, экипировкой
     */
    public static function getGrade1a(): array
    {
        // Для всех наёмников используем CarefulStrategy по умолчанию
        $strategyClass = '\Mordheim\Strategy\CarefulStrategy';
        return [
            new \Mordheim\Fighter(
                'Tilean Marksman',
                new Characteristics(4, 2, 3, 3, 3, 7, 1),
                [
                    Skills::getByName('Quick Shot'),
                    Skills::getByName('Eagle Eyes')
                ],
                new EquipmentManager([
                    Weapons::getByName('Crossbow'),
                    Weapons::getByName('Sword'),
                ], [
                    Armors::getByName('Light Armor'),
                ]),
                new $strategyClass()
            ),
            new \Mordheim\Fighter(
                'Elf Ranger',
                new Characteristics(5, 4, 3, 3, 4, 8, 1),
                [
                    Skills::getByName('Quick Shot'),
                    Skills::getByName('Dodge'),
                    Skills::getByName('Scale Sheer Surfaces')
                ],
                new EquipmentManager([
                    Weapons::getByName('Elf Bow'),
                    Weapons::getByName('Sword'),
                ], [
                    Armors::getByName('Light Armor'),
                ]),
                new $strategyClass()
            ),
            new \Mordheim\Fighter(
                'Halfling Scout',
                new Characteristics(4, 2, 2, 2, 4, 8, 1),
                [
                    Skills::getByName('Stealth'),
                    Skills::getByName('Dodge')
                ],
                new EquipmentManager([
                    Weapons::getByName('Short Bow'),
                    Weapons::getByName('Dagger'),
                ]),
                new $strategyClass()
            ),
            new \Mordheim\Fighter(
                'Ogre Bodyguard',
                new Characteristics(6, 3, 4, 4, 2, 7, 3),
                [
                    Skills::getByName('Resilient'),
                    Skills::getByName('Fear')
                ],
                new EquipmentManager([
                    Weapons::getByName('Ogre Club'),
                ], [
                    Armors::getByName('Heavy Armor'),
                ]),
                new $strategyClass()
            ),
            new \Mordheim\Fighter(
                'Dwarf Troll Slayer',
                new Characteristics(3, 4, 3, 4, 2, 9, 1),
                [
                    Skills::getByName('Frenzy'),
                    Skills::getByName('Hard to Kill')
                ],
                new EquipmentManager([
                    Weapons::getByName('Axe'),
                    Weapons::getByName('Axe'),
                ]),
                new $strategyClass()
            ),
        ];
    }
}
