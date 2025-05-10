<?php

namespace Mordheim\Rule;

use Mordheim\FighterInterface;
use Mordheim\SpecialRule;

class Dodge
{
    public static function roll(FighterInterface $target): bool
    {
        $hasDodge = $target->hasSpecialRule(SpecialRule::DODGE);
        if ($hasDodge && \Mordheim\Dice::roll(6) >= 5) return true;
        return false;
    }
}