<?php

namespace Mordheim\Rule;

use Mordheim\BlankInterface;
use Mordheim\FighterAdvancement;
use Mordheim\SpecialRule;

class WizardHelper
{
    public static function isWizard(BlankInterface $blank): bool
    {
        if ($blank->hasSpecialRule(SpecialRule::WIZARD_NECROMANCY))
            return true;
        if ($blank->hasSpecialRule(SpecialRule::WIZARD_CHAOS_RITUALS))
            return true;
        if ($blank->hasSpecialRule(SpecialRule::WIZARD_MAGIC_OF_THE_HORNED_RAT))
            return true;
        if ($blank->hasSpecialRule(SpecialRule::PRAYERS_OF_SIGMAR))
            return true;

        return false;
    }

    /**
     * TODO
     */
    public static function getUnlearnedSpells(BlankInterface $blank, FighterAdvancement $advancement): array
    {
        if (!self::isWizard($blank))
            return [];

        return [];
    }
}