<?php

namespace Mordheim\Data;

use Mordheim\Skill;

class Skills
{
    private static ?array $cache = null;

    /**
     * @return Skill[]
     */
    public static function getAll(): array
    {
        if (is_null(self::$cache)) {
            self::$cache = [
                'Step Aside' => new Skill('Step Aside', '5+ save against melee attacks.'),
                'Dodge' => new Skill('Dodge', '5+ save against shooting attacks.'),
                'Strongman' => new Skill('Strongman', 'Can use two-handed weapons without penalty.'),
                'Resilient' => new Skill('Resilient', '-1 to be wounded.'),
                'Quick Shot' => new Skill('Quick Shot', 'May shoot twice if not moved.'),
                'Sprint' => new Skill('Sprint', 'May add +D6 to movement when running.'),
                'Acrobat' => new Skill('Acrobat', 'Ignores falling damage and dangerous terrain when jumping or falling.'),
                'Leap' => new Skill('Leap', 'Can jump up to 6\" horizontally (Initiative test required).'),
                'Jump Up' => new Skill('Jump Up', 'May stand up from knockdown without spending movement.'),
                'Nimble' => new Skill('Nimble', '+1 Initiative.'),
                // ... Добавить остальные навыки уровня 1a
                'Eagle Eyes' => new Skill('Eagle Eyes', ''),
                'Scale Sheer Surfaces' => new Skill('Scale Sheer Surfaces', ''),
                'Stealth' => new Skill('Stealth', ''),
                'Fear' => new Skill('Fear', ''),
                'Frenzy' => new Skill('Frenzy', ''),
                'Hard to Kill' => new Skill('Hard to Kill', ''),
                'Leader' => new Skill('Leader', '6" leadership bonus.'),
                'Wealth' => new Skill('Wealth', '+100gc to starting treasury.'),
                'Stupidity' => new Skill('Stupidity', ''),
            ];
        }
        return self::$cache;
    }

    public static function getByName(string $name): ?Skill
    {
        return self::getAll()[$name] ?? null;
    }
}
