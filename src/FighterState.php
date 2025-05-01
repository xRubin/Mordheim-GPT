<?php
namespace Mordheim;

use Mordheim\Exceptions\FighterAbnormalStateException;

enum FighterState: string
{
    case STANDING = 'standing';
    case KNOCKED_DOWN = 'knocked_down';
    case STUNNED = 'stunned';
    case OUT_OF_ACTION = 'out_of_action';
    case PANIC = 'panic';

    public function validate(): bool
    {
        return match ($this) {
            self::STANDING => true,
            default => throw (new FighterAbnormalStateException())->setState($this),
        };
    }
}
