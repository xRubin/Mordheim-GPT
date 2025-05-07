<?php

namespace Mordheim\Data;

use Mordheim\Armor;
use Mordheim\BlankInterface;
use Mordheim\Characteristics;
use Mordheim\EquipmentListInterface;
use Mordheim\WarbandInterface;
use Mordheim\Weapon;

enum Blank: int implements BlankInterface
{
    case REIKLAND_MERCENARY_CAPTAIN = 1;
    case REIKLAND_CHAMPION = 2;
    case REIKLAND_YOUNGBLOOD = 3;
    case REIKLAND_WARRIOR = 4;
    case REIKLAND_MARKSMAN = 5;
    case REIKLAND_SWORDSMAN = 6;

    case MIDDENHEIM_MERCENARY_CAPTAIN = 11;
    case MIDDENHEIM_CHAMPION = 12;
    case MIDDENHEIM_YOUNGBLOOD = 13;
    case MIDDENHEIM_WARRIOR = 14;
    case MIDDENHEIM_MARKSMAN = 15;
    case MIDDENHEIM_SWORDSMAN = 16;

    case MARIENBURG_MERCENARY_CAPTAIN = 21;
    case MARIENBURG_CHAMPION = 22;
    case MARIENBURG_YOUNGBLOOD = 23;
    case MARIENBURG_WARRIOR = 24;
    case MARIENBURG_MARKSMAN = 25;
    case MARIENBURG_SWORDSMAN = 26;


    public function getWarband(): ?WarbandInterface
    {
        return match ($this) {
            self::REIKLAND_MERCENARY_CAPTAIN,
            self::REIKLAND_CHAMPION,
            self::REIKLAND_YOUNGBLOOD,
            self::REIKLAND_WARRIOR,
            self::REIKLAND_MARKSMAN,
            self::REIKLAND_SWORDSMAN => Warband::REIKLAND,

            self::MIDDENHEIM_MERCENARY_CAPTAIN,
            self::MIDDENHEIM_CHAMPION,
            self::MIDDENHEIM_YOUNGBLOOD,
            self::MIDDENHEIM_WARRIOR,
            self::MIDDENHEIM_MARKSMAN,
            self::MIDDENHEIM_SWORDSMAN => Warband::MIDDENHEIM,

            self::MARIENBURG_MERCENARY_CAPTAIN,
            self::MARIENBURG_CHAMPION,
            self::MARIENBURG_YOUNGBLOOD,
            self::MARIENBURG_WARRIOR,
            self::MARIENBURG_MARKSMAN,
            self::MARIENBURG_SWORDSMAN => Warband::MARIENBURG,

            default => null,
        };
    }

    public function getTitle(): string
    {
        return match ($this) {
            self::REIKLAND_MERCENARY_CAPTAIN => 'Mercenary Captain',
            self::REIKLAND_CHAMPION => 'Champion',
            self::REIKLAND_YOUNGBLOOD => 'Youngblood',
            self::REIKLAND_WARRIOR => 'Warrior',
            self::REIKLAND_MARKSMAN => 'Marksman',
            self::REIKLAND_SWORDSMAN => 'Swordman',

            self::MIDDENHEIM_MERCENARY_CAPTAIN => 'Mercenary Captain',
            self::MIDDENHEIM_CHAMPION => 'Champion',
            self::MIDDENHEIM_YOUNGBLOOD => 'Youngblood',
            self::MIDDENHEIM_WARRIOR => 'Warrior',
            self::MIDDENHEIM_MARKSMAN => 'Marksman',
            self::MIDDENHEIM_SWORDSMAN => 'Swordman',

            self::MARIENBURG_MERCENARY_CAPTAIN => 'Mercenary Captain',
            self::MARIENBURG_CHAMPION => 'Champion',
            self::MARIENBURG_YOUNGBLOOD => 'Youngblood',
            self::MARIENBURG_WARRIOR => 'Warrior',
            self::MARIENBURG_MARKSMAN => 'Marksman',
            self::MARIENBURG_SWORDSMAN => 'Swordman',
        };
    }

    public function getHireCost(): int
    {
        return match ($this) {
            self::REIKLAND_MERCENARY_CAPTAIN => 60,
            self::REIKLAND_CHAMPION => 35,
            self::REIKLAND_YOUNGBLOOD => 15,
            self::REIKLAND_WARRIOR => 25,
            self::REIKLAND_MARKSMAN => 25,
            self::REIKLAND_SWORDSMAN => 35,

            self::MIDDENHEIM_MERCENARY_CAPTAIN => 60,
            self::MIDDENHEIM_CHAMPION => 35,
            self::MIDDENHEIM_YOUNGBLOOD => 15,
            self::MIDDENHEIM_WARRIOR => 25,
            self::MIDDENHEIM_MARKSMAN => 25,
            self::MIDDENHEIM_SWORDSMAN => 35,

            self::MARIENBURG_MERCENARY_CAPTAIN => 60,
            self::MARIENBURG_CHAMPION => 35,
            self::MARIENBURG_YOUNGBLOOD => 15,
            self::MARIENBURG_WARRIOR => 25,
            self::MARIENBURG_MARKSMAN => 25,
            self::MARIENBURG_SWORDSMAN => 35,
        };
    }

    public function getStartExp(): int
    {
        return match ($this) {
            self::REIKLAND_MERCENARY_CAPTAIN => 20,
            self::MIDDENHEIM_MERCENARY_CAPTAIN => 20,
            self::MARIENBURG_MERCENARY_CAPTAIN => 20,
            self::REIKLAND_CHAMPION => 8,
            self::MIDDENHEIM_CHAMPION => 8,
            self::MARIENBURG_CHAMPION => 8,
            default => 0,
        };
    }

    /**
     * @return Weapon[]
     */
    public function getStartWeapons(): array
    {
        return match ($this) {
            default => [Weapons::getByName('Dagger')]
        };
    }

    /**
     * @return Armor[]
     */
    public function getStartArmors(): array
    {
        return match ($this) {
            default => []
        };
    }

    public function getMinCount(): int
    {
        return match ($this) {
            self::REIKLAND_MERCENARY_CAPTAIN => 1,
            self::MIDDENHEIM_MERCENARY_CAPTAIN => 1,
            self::MARIENBURG_MERCENARY_CAPTAIN => 1,
            default => 0,
        };
    }

    public function getMaxCount(): int
    {
        return match ($this) {
            self::REIKLAND_MERCENARY_CAPTAIN => 1,
            self::REIKLAND_CHAMPION => 2,
            self::REIKLAND_YOUNGBLOOD => 2,
            self::REIKLAND_MARKSMAN => 7,
            self::REIKLAND_SWORDSMAN => 5,

            self::MIDDENHEIM_MERCENARY_CAPTAIN => 1,
            self::MIDDENHEIM_CHAMPION => 2,
            self::MIDDENHEIM_YOUNGBLOOD => 2,
            self::MIDDENHEIM_MARKSMAN => 7,
            self::MIDDENHEIM_SWORDSMAN => 5,

            self::MARIENBURG_MERCENARY_CAPTAIN => 1,
            self::MARIENBURG_CHAMPION => 2,
            self::MARIENBURG_YOUNGBLOOD => 2,
            self::MARIENBURG_MARKSMAN => 7,
            self::MARIENBURG_SWORDSMAN => 5,

            default => 99,
        };
    }

    public function isHero(): bool
    {
        return match ($this) {
            self::REIKLAND_MERCENARY_CAPTAIN,
            self::REIKLAND_CHAMPION,
            self::REIKLAND_YOUNGBLOOD,

            self::MIDDENHEIM_MERCENARY_CAPTAIN,
            self::MIDDENHEIM_CHAMPION,
            self::MIDDENHEIM_YOUNGBLOOD,

            self::MARIENBURG_MERCENARY_CAPTAIN,
            self::MARIENBURG_CHAMPION,
            self::MARIENBURG_YOUNGBLOOD => true,

            default => false,
        };
    }

    public function isHenchman(): bool
    {
        return !$this->isHero();
    }

    public function isMercenary(): bool
    {
        return false; // TODO
    }

    public function getCharacteristics(): Characteristics
    {
        return match ($this) {
            self::REIKLAND_MERCENARY_CAPTAIN => new Characteristics(4, 4, 4, 3, 3, 1, 4, 1, 8),
            self::REIKLAND_CHAMPION => new Characteristics(4, 4, 3, 3, 3, 1, 3, 1, 7),
            self::REIKLAND_YOUNGBLOOD => new Characteristics(4, 2, 2, 3, 3, 1, 3, 1, 6),
            self::REIKLAND_WARRIOR => new Characteristics(4, 3, 3, 3, 3, 1, 3, 1, 7),
            self::REIKLAND_MARKSMAN => new Characteristics(4, 3, 4, 3, 3, 1, 3, 1, 7),
            self::REIKLAND_SWORDSMAN => new Characteristics(4, 3, 3, 3, 3, 1, 3, 1, 7),

            self::MIDDENHEIM_MERCENARY_CAPTAIN => new Characteristics(4, 4, 4, 4, 3, 1, 4, 1, 8),
            self::MIDDENHEIM_CHAMPION => new Characteristics(4, 4, 3, 4, 3, 1, 3, 1, 7),
            self::MIDDENHEIM_YOUNGBLOOD => new Characteristics(4, 2, 2, 3, 3, 1, 3, 1, 6),
            self::MIDDENHEIM_WARRIOR,
            self::MIDDENHEIM_MARKSMAN,
            self::MIDDENHEIM_SWORDSMAN => new Characteristics(4, 3, 3, 3, 3, 1, 3, 1, 7),

            self::MARIENBURG_MERCENARY_CAPTAIN => new Characteristics(4, 4, 4, 3, 3, 1, 4, 1, 8),
            self::MARIENBURG_CHAMPION => new Characteristics(4, 4, 3, 3, 3, 1, 3, 1, 7),
            self::MARIENBURG_YOUNGBLOOD => new Characteristics(4, 2, 2, 3, 3, 1, 3, 1, 6),
            self::MARIENBURG_WARRIOR,
            self::MARIENBURG_MARKSMAN,
            self::MARIENBURG_SWORDSMAN => new Characteristics(4, 3, 3, 3, 3, 1, 3, 1, 7),
        };
    }

    public function getSpecialRules(): array
    {
        return match ($this) {
            self::REIKLAND_MERCENARY_CAPTAIN => [Skills::getByName('Leader')],
            self::REIKLAND_SWORDSMAN => [Skills::getByName('Expert Swordsman')],
            self::MIDDENHEIM_MERCENARY_CAPTAIN => [Skills::getByName('Leader')],
            self::MIDDENHEIM_SWORDSMAN => [Skills::getByName('Expert Swordsman')],
            self::MARIENBURG_MERCENARY_CAPTAIN => [Skills::getByName('Leader')],
            self::MARIENBURG_SWORDSMAN => [Skills::getByName('Expert Swordsman')],
            default => [],
        };
    }

    public function getEquipmentList(): EquipmentListInterface
    {
        return match ($this) {
            self::REIKLAND_MERCENARY_CAPTAIN => EquipmentList::MERCENARY_EQUIPMENT_LIST,
            self::REIKLAND_CHAMPION => EquipmentList::MERCENARY_EQUIPMENT_LIST,
            self::REIKLAND_YOUNGBLOOD => EquipmentList::MERCENARY_EQUIPMENT_LIST,
            self::REIKLAND_WARRIOR => EquipmentList::MERCENARY_EQUIPMENT_LIST,
            self::REIKLAND_MARKSMAN => EquipmentList::MARKSMAN_EQUIPMENT_LIST,
            self::REIKLAND_SWORDSMAN => EquipmentList::MERCENARY_EQUIPMENT_LIST,

            self::MIDDENHEIM_MERCENARY_CAPTAIN => EquipmentList::MERCENARY_EQUIPMENT_LIST,
            self::MIDDENHEIM_CHAMPION => EquipmentList::MERCENARY_EQUIPMENT_LIST,
            self::MIDDENHEIM_YOUNGBLOOD => EquipmentList::MERCENARY_EQUIPMENT_LIST,
            self::MIDDENHEIM_WARRIOR => EquipmentList::MERCENARY_EQUIPMENT_LIST,
            self::MIDDENHEIM_MARKSMAN => EquipmentList::MARKSMAN_EQUIPMENT_LIST,
            self::MIDDENHEIM_SWORDSMAN => EquipmentList::MERCENARY_EQUIPMENT_LIST,

            self::MARIENBURG_MERCENARY_CAPTAIN => EquipmentList::MERCENARY_EQUIPMENT_LIST,
            self::MARIENBURG_CHAMPION => EquipmentList::MERCENARY_EQUIPMENT_LIST,
            self::MARIENBURG_YOUNGBLOOD => EquipmentList::MERCENARY_EQUIPMENT_LIST,
            self::MARIENBURG_WARRIOR => EquipmentList::MERCENARY_EQUIPMENT_LIST,
            self::MARIENBURG_MARKSMAN => EquipmentList::MARKSMAN_EQUIPMENT_LIST,
            self::MARIENBURG_SWORDSMAN => EquipmentList::MERCENARY_EQUIPMENT_LIST,
        };
    }

    public function getAdvancementSkillGroups(): array
    {
        return match ($this) {
            self::REIKLAND_MERCENARY_CAPTAIN => [SkillGroup::COMBAT, SkillGroup::SHOOTING, SkillGroup::ACADEMIC, SkillGroup::STRENGTH, SkillGroup::SPEED],
            self::REIKLAND_CHAMPION => [SkillGroup::COMBAT, SkillGroup::SHOOTING, SkillGroup::STRENGTH],
            self::REIKLAND_YOUNGBLOOD => [SkillGroup::COMBAT, SkillGroup::SHOOTING, SkillGroup::SPEED],

            self::MIDDENHEIM_MERCENARY_CAPTAIN => [SkillGroup::COMBAT, SkillGroup::SHOOTING, SkillGroup::ACADEMIC, SkillGroup::STRENGTH, SkillGroup::SPEED],
            self::MIDDENHEIM_CHAMPION => [SkillGroup::COMBAT, SkillGroup::STRENGTH, SkillGroup::SPEED],
            self::MIDDENHEIM_YOUNGBLOOD => [SkillGroup::COMBAT, SkillGroup::SHOOTING, SkillGroup::SPEED],

            self::MARIENBURG_MERCENARY_CAPTAIN => [SkillGroup::COMBAT, SkillGroup::SHOOTING, SkillGroup::ACADEMIC, SkillGroup::STRENGTH, SkillGroup::SPEED],
            self::MARIENBURG_CHAMPION => [SkillGroup::COMBAT, SkillGroup::SHOOTING, SkillGroup::SPEED],
            self::MARIENBURG_YOUNGBLOOD => [SkillGroup::COMBAT, SkillGroup::STRENGTH, SkillGroup::SPEED],

            default => [],
        };
    }
}