<?php

namespace Mordheim\Classic;

enum Status
{
    case STANDING;
    case KNOCKED_DOWN;
    case STUNNED;
    case OUT_OF_ACTION;
    case PANIC;
    case FRENZY;

    public function canAct(): bool
    {
        return match ($this) {
            self::STANDING,
            self::FRENZY => true,
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
