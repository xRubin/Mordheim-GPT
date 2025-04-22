<?php

namespace Mordheim\Data;

use Mordheim\Armor;
use Mordheim\SpecialRule;

class Armors
{
    private static ?array $cache = null;

    /**
     * @return Armor[]
     */
    public static function getAll(): array
    {
        if (is_null(self::$cache)) {
            self::$cache = [
                'Heavy Armor' => new Armor('Heavy Armor', 5, 0, 'Тяжелая броня', [SpecialRule::SAVE, SpecialRule::MOVEMENT]),
                'Light Armor' => new Armor('Light Armor', 6, 0, 'Легкая броня', []),
                'Shield' => new Armor('Shield', 6, 0, 'Щит', [SpecialRule::SHIELD_PARRY]),
                'Helmet' => new Armor('Helmet', 0, 0, 'Шлем', [SpecialRule::AVOID_STUN]),
            ];
        }
        return self::$cache;
    }

    public static function getByName(string $name): ?Armor
    {
        return self::getAll()[$name] ?? null;
    }
}
