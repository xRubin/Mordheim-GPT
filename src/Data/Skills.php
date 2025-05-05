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
                'Nimble' => new Skill('Nimble', '+1 Initiative.'), // TODO: check rules
                // ... Добавить остальные навыки уровня 1a
                'Eagle Eyes' => new Skill('Eagle Eyes', '+6" to maximum range of missile weapons.'),
                'Scale Sheer Surfaces' => new Skill('Scale Sheer Surfaces', 'May climb any vertical surface without penalty.'),
                'Stealth' => new Skill('Stealth', 'Harder to spot at range; -1 to be hit by shooting if more than 8" away.'),
                'Fear' => new Skill('Fear', 'Causes fear (enemy must pass Leadership test to charge or fight).'),
                'Frenzy' => new Skill('Frenzy', 'Must charge if possible, +1 Attack in combat.'),
                'Hard to Kill' => new Skill('Hard to Kill', 'Only taken Out of Action on a roll of 6 on the injury table.'),
                'Leader' => new Skill('Leader', '6" leadership bonus.'),
                'Wealth' => new Skill('Wealth', '+100gc to starting treasury.'),
                'Stupidity' => new Skill('Stupidity', 'At the start of turn, must pass Leadership test or do nothing for that turn.'),
                'Large Target' => new Skill('Large Target', 'Large Targets as defined in the shooting rules.'),
                'Lightning Reflexes' => new Skill('Lightning Reflexes', 'If the warrior is charged he will ‘strike first’ against those that charged that turn.'),
            ];
        }
        return self::$cache;
    }

    public static function getByName(string $name): ?Skill
    {
        return self::getAll()[$name] ?? null;
    }
}
