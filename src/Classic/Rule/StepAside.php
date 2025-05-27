<?php

namespace Mordheim\Classic\Rule;

use Mordheim\Classic\Fighter;
use Mordheim\Classic\SpecialRule;

class StepAside
{
    public static function roll(Fighter $target, bool &$parried): void
    {
        if ($target->hasSpecialRule(SpecialRule::STEP_ASIDE) && !$parried) {
            $stepAsideRoll = \Mordheim\Dice::roll(6);
            \Mordheim\BattleLogger::add("{$target->getName()} использует Step Aside: $stepAsideRoll (нужно 5+)");
            if ($stepAsideRoll >= 5) {
                $parried = true;
                \Mordheim\BattleLogger::add("Step Aside сработал!");
            } else {
                \Mordheim\BattleLogger::add("Step Aside не сработал.");
            }
        }
    }
}