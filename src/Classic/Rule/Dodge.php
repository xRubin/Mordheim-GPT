<?php

namespace Mordheim\Classic\Rule;

use Mordheim\Classic\Fighter;
use Mordheim\Classic\SpecialRule;

class Dodge
{
    public static function roll(Fighter $target): bool
    {
        $hasDodge = $target->hasSpecialRule(SpecialRule::DODGE);
        if ($hasDodge && \Mordheim\Dice::roll(6) >= 5) return true;
        return false;
    }
}