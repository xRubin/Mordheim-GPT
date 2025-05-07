<?php

namespace Mordheim\Data;

use Mordheim\EquipmentListInterface;

enum EquipmentList: int implements EquipmentListInterface
{
    case MERCENARY_EQUIPMENT_LIST = 1;
    case MARKSMAN_EQUIPMENT_LIST = 2;

    public function getTitle(): string
    {
        return match ($this) {
            self::MERCENARY_EQUIPMENT_LIST => 'Mercenary Equipment List',
            self::MARKSMAN_EQUIPMENT_LIST => 'Marksman Equipment List',
        };
    }

    public function getItems(): array
    {
        return match ($this) {
            self::MERCENARY_EQUIPMENT_LIST => [
                Weapons::getByName('Dagger'),
                Weapons::getByName('Club'),
                Weapons::getByName('Mace'),
                Weapons::getByName('Hammer'),
                Weapons::getByName('Axe'),
                Weapons::getByName('Sword'),
                Weapons::getByName('Morning star'),
                Weapons::getByName('Double-handed axe'),
                Weapons::getByName('Double-handed sword'),
                Weapons::getByName('Spear'),
                Weapons::getByName('Halberd'),
                Weapons::getByName('Crossbow'),
                Weapons::getByName('Pistol'),
                Weapons::getByName('Duelling pistol'),
                Weapons::getByName('Bow'),
                Armors::getByName('Light armour'),
                Armors::getByName('Heavy armour'),
                Armors::getByName('Shield'),
                Armors::getByName('Buckler'),
                Armors::getByName('Helmet'),
            ],
            self::MARKSMAN_EQUIPMENT_LIST => [
                Weapons::getByName('Dagger'),
                Weapons::getByName('Club'),
                Weapons::getByName('Mace'),
                Weapons::getByName('Hammer'),
                Weapons::getByName('Axe'),
                Weapons::getByName('Sword'),
                Weapons::getByName('Crossbow'),
                Weapons::getByName('Pistol'),
                Weapons::getByName('Bow'),
                Weapons::getByName('Long bow'),
                Weapons::getByName('Blunderbuss'),
                Weapons::getByName('Handgun'),
                Weapons::getByName('Hunting rifle'),
                Armors::getByName('Light armour'),
                Armors::getByName('Shield'),
                Armors::getByName('Helmet'),
            ],
        };
    }
}