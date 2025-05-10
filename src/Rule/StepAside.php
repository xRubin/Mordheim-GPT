<?php

namespace Mordheim\Rule;

use Mordheim\FighterInterface;
use Mordheim\SpecialRule;

class StepAside
{
    public static function roll(FighterInterface $target, bool &$parried): void
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