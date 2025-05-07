<?php

namespace Mordheim\Data;

use Mordheim\WarbandInterface;

enum Warband: int implements WarbandInterface
{
    case REIKLAND = 1;
    case MIDDENHEIM = 2;
    case MARIENBURG = 3;

    public function getTitle(): string
    {
        return match ($this) {
            self::REIKLAND => 'Reikland',
            self::MIDDENHEIM => 'Middenheim',
            self::MARIENBURG => 'Marienburg',
        };
    }

    public function getBlanks(): array
    {
        return match ($this) {
            self::REIKLAND => [
                Blank::REIKLAND_MERCENARY_CAPTAIN,
                Blank::REIKLAND_CHAMPION,
                Blank::REIKLAND_YOUNGBLOOD,
                Blank::REIKLAND_WARRIOR,
                Blank::REIKLAND_MARKSMAN,
                Blank::REIKLAND_SWORDSMAN
            ],
            self::MIDDENHEIM => [
                Blank::MIDDENHEIM_MERCENARY_CAPTAIN,
                Blank::MIDDENHEIM_CHAMPION,
                Blank::MIDDENHEIM_YOUNGBLOOD,
                Blank::MIDDENHEIM_WARRIOR,
                Blank::MIDDENHEIM_MARKSMAN,
                Blank::MIDDENHEIM_SWORDSMAN,
            ],
            self::MARIENBURG => [
                Blank::MARIENBURG_MERCENARY_CAPTAIN,
                Blank::MARIENBURG_CHAMPION,
                Blank::MARIENBURG_YOUNGBLOOD,
                Blank::MARIENBURG_WARRIOR,
                Blank::MARIENBURG_MARKSMAN,
                Blank::MARIENBURG_SWORDSMAN,
            ],
        };
    }

    public function getStartWealth(): int
    {
        return match ($this) {
            self::MARIENBURG => 600,
            default => 500,
        };
    }

    public function getMaxFighters(): int
    {
        return match ($this) {
            default => 15,
        };
    }
}