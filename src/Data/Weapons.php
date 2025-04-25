<?php

namespace Mordheim\Data;

use Mordheim\SpecialRule;
use Mordheim\Weapon;

class Weapons
{
    private static ?array $cache = null;

    /**
     * @return Weapon[]
     */
    public static function getAll(): array
    {
        if (is_null(self::$cache)) {
            self::$cache = [
                // Melee
                'Sword' => new Weapon('Sword', 'Parry', 0, 0, 1, 0, 'Melee', [SpecialRule::PARRY, SpecialRule::SWORD]),
                'Dagger' => new Weapon('Dagger', 'No special rules', 0, 0, 0, 0, 'Melee', []),
                'Club' => new Weapon('Club', 'Дубинка', 0, 0, 0, 0, 'Melee', [SpecialRule::CLUB]),
                'Ogre Club' => new Weapon('Ogre Club', 'Strength 6, Two-handed', 6, 0, 0, 0, 'Melee', [SpecialRule::DOUBLE_HANDED, SpecialRule::CLUB]),
                'Axe' => new Weapon('Axe', '+1 Strength', 1, 0, 0, 0, 'Melee', [SpecialRule::AXE]),
                'Spear' => new Weapon('Spear', 'First strike, two-handed.', 0, 0, 0, 0, 'Melee'),
                'Halberd' => new Weapon('Halberd', '+1 strength, two-handed.', 1, 0, 0, 0, 'Melee'),
                'Flail' => new Weapon('Flail', '+2 strength in first round, two-handed, -1 to hit.', 2, 0, -1, 0, 'Melee', [SpecialRule::FLAIL]),
                'Morning Star' => new Weapon('Morning Star', '+2 strength in first round, one-handed, -1 to hit.', 2, 0, -1, 0, 'Melee'),
                // Ranged
                'Bow' => new Weapon('Bow', 'Range 24", strength 3.', 3, 0, 0, 24, 'Ranged'),
                'Crossbow' => new Weapon('Crossbow', 'Range 30", Strength 4', 4, 0, 0, 30, 'Ranged', [SpecialRule::ARMOR_PIERCING]),
                'Elf Bow' => new Weapon('Elf Bow', 'Range 36", Strength 3', 3, 0, 1, 36, 'Ranged', []),
                'Short Bow' => new Weapon('Short Bow', 'Range 18", Strength 3', 3, 0, 0, 18, 'Ranged', []),
                'Handgun' => new Weapon('Handgun', 'Range 24", strength 4, move or fire.', 4, 0, 0, 24, 'Ranged'),
                'Pistol' => new Weapon('Pistol', 'Range 8", strength 4, can use two.', 4, 0, 0, 8, 'Ranged'),
                'Dueling Pistol' => new Weapon('Dueling Pistol', 'Range 10", strength 4, +1 to hit, can use two.', 4, 0, 1, 10, 'Ranged'),
                'Throwing Knives' => new Weapon('Throwing Knives', 'Range 6", strength as user, quick shot.', 0, 0, 0, 6, 'Ranged'),
                'Sling' => new Weapon('Sling', 'Range 18", strength 3, can shoot twice if not moved.', 3, 0, 0, 18, 'Ranged'),
                // Экзотика и спец. оружие можно добавить по мере необходимости
                'Fighting Claws' => new Weapon('Fighting Claws', 'Pair: +1 Attack, Parry, +1 to climb, cannot use other weapons in close combat.', 0, 0, 0, 0, 'Melee', [SpecialRule::PAIR, SpecialRule::PARRY, SpecialRule::CLIMB, SpecialRule::CUMBERSOME]),
                'Warplock Jezzail' => new Weapon('Warplock Jezzail', 'Range 36", Strength 6, ignores armor save.', 6, 0, 0, 36, 'Ranged', [SpecialRule::IGNORE_ARMOR_SAVE, SpecialRule::MOVE_OR_FIRE]),

                // check
                'Dragon Sword' => new Weapon('Dragon Sword', 'Two-handed, Parry', 1, 0, 1, 0, 'Melee', [SpecialRule::PARRY, SpecialRule::TWO_HANDED]),
            ];
        }
        return self::$cache;
    }

    public static function getByName(string $name): ?Weapon
    {
        return self::getAll()[$name] ?? null;
    }
}
