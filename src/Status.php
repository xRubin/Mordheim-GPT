<?php

namespace Mordheim;

enum Status: string
{
    case STANDING = 'standing';
    case KNOCKED_DOWN = 'knocked_down';
    case STUNNED = 'stunned';
    case OUT_OF_ACTION = 'out_of_action';
    case PANIC = 'panic';

    public function canAct(): bool
    {
        return match ($this) {
            self::STANDING => true,
            default => false,
        };
    }

    public function isAlive(): bool
    {
        return match ($this) {
            self::OUT_OF_ACTION => false,
            default => true,
        };
    }

    public function canLead(): bool
    {
        return match ($this) {
            self::OUT_OF_ACTION => false,
            self::STUNNED => false,
            default => true,
        };
    }

    public function canFrenzy(): bool
    {
        return match ($this) {
            self::OUT_OF_ACTION => false,
            self::STUNNED => false,
            self::KNOCKED_DOWN => false,
            default => true,
        };
    }
}
