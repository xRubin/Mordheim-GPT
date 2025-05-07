<?php

namespace Mordheim\Data;

use Mordheim\SkillGroupInterface;

enum SkillGroup: int implements SkillGroupInterface
{
    case COMBAT = 1;
    case SHOOTING = 2;
    case ACADEMIC = 3;
    case STRENGTH = 4;
    case SPEED = 5;

    public function getTitle(): string
    {
        return match ($this) {
            self::COMBAT => 'Combat',
            self::SHOOTING => 'Shooting',
            self::ACADEMIC => 'Academic',
            self::STRENGTH => 'Strength',
            self::SPEED => 'Speed',
        };
    }

    public function getSkills(): array
    {
        return match ($this) {
            self::COMBAT => [],
            self::SHOOTING => [],
            self::ACADEMIC => [],
            self::STRENGTH => [],
            self::SPEED => [],
        };
    }
}