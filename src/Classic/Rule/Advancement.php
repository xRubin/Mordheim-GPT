<?php

namespace Mordheim\Classic\Rule;

use Mordheim\Classic\Blank;
use Mordheim\Classic\FighterAdvancement;
use Mordheim\Classic\SpecialRule;
use Mordheim\Dice;

class Advancement
{
    public static function getAvailableAdvancementsCount(Blank $blank, int $exp): int
    {
        if ($blank->hasSpecialRule(SpecialRule::ANIMAL) || $blank->hasSpecialRule(SpecialRule::UNLIVING))
            return 0;

        if ($blank->isHero())
            $levels = [2, 4, 6, 8, 11, 14, 17, 20, 24, 28, 32, 36, 41, 46, 51, 57, 63, 69, 76, 83, 90];
        else
            $levels = [2, 5, 9, 14];

        $cnt = 0;
        foreach ($levels as $lvl) {
            if ($lvl <= $blank->getStartExp())
                continue;
            if ($exp >= $lvl) $cnt++;
        }
        return $cnt;
    }

    public static function getCurrentAdvancementsCount(FighterAdvancement $advancement): int
    {
        $points = $advancement->getCharacteristics()->getMovement()
            + $advancement->getCharacteristics()->getWeaponSkill()
            + $advancement->getCharacteristics()->getBallisticSkill()
            + $advancement->getCharacteristics()->getStrength()
            + $advancement->getCharacteristics()->getToughness()
            + $advancement->getCharacteristics()->getWounds()
            + $advancement->getCharacteristics()->getInitiative()
            + $advancement->getCharacteristics()->getAttacks()
            + $advancement->getCharacteristics()->getLeadership();

        $specialRules = count($advancement->getSpecialRules());

        return $points + $specialRules;
    }

    public static function validateAdvancement(Blank $blank, FighterAdvancement $advancement, array $data = []): bool
    {
        switch ($data[0]) {
            case 'roll_spell':
                return $blank->isWizard() && count($blank->getUnlearnedSpells($advancement));
            case 'select_skill':
                // Получаем все доступные группы навыков
                $groups = $blank->getAdvancementSkillGroups();
                $warband = $blank->getWarband();
                $availableSkills = [];
                foreach ($groups as $group) {
                    $availableSkills = array_merge($availableSkills, $group->getSpecialRules($warband));
                }
                // Исключаем уже полученные навыки
                $currentSkills = array_merge($blank->getSpecialRules(), $advancement->getSpecialRules());
                $availableSkills = array_diff($availableSkills, $currentSkills);
                return count($availableSkills) > 0;
            case 'stat':
                if (!($stat = $data[1])) return false;
                $limits = [
                    'movement' => 'getMovement',
                    'weapon_skill' => 'getWeaponSkill',
                    'ballistic_skill' => 'getBallisticSkill',
                    'strength' => 'getStrength',
                    'toughness' => 'getToughness',
                    'wounds' => 'getWounds',
                    'initiative' => 'getInitiative',
                    'attacks' => 'getAttacks',
                    'leadership' => 'getLeadership',
                ];
                $getter = $limits[$stat];
                $currentValue = $blank->getCharacteristics()->$getter() + $advancement->getCharacteristics()->$getter();
                return $currentValue < $blank->getMaxCharacteristics()->$getter();
            default:
                return false;
        }
    }

    public static function rollAdvancement(Blank $blank, FighterAdvancement $advancement, int $exp): array
    {
        do {
            $suggests = array_filter(
                $blank->isHenchman() ? self::rollHenchmanAdvancement() : self:: rollHeroAdvancement(),
                fn($data) => self::validateAdvancement($blank, $advancement, $data)
            );

        } while (!count($suggests));

        return $suggests;
    }

    public static function rollHeroAdvancement(): array
    {
        $roll = Dice::roll(6) + Dice::roll(6);
        if ($roll <= 5)
            return [['select_skill'], ['roll_spell']];
        if ($roll == 6)
            return Dice::roll(6) <= 3 ? [['stat', 'strength']] : [['stat', 'attacks']];
        if ($roll == 7)
            return [['stat', 'weapon_skill'], ['stat', 'ballistic_skill']];
        if ($roll == 8)
            return Dice::roll(6) <= 3 ? [['stat', 'initiative']] : [['stat', 'leadership']];
        if ($roll == 9)
            return Dice::roll(6) <= 3 ? [['stat', 'wounds']] : [['stat', 'toughness']];
        return [['select_skill'], ['roll_spell']];
    }

    public static function rollHenchmanAdvancement(): array
    {
        $roll = Dice::roll(6) + Dice::roll(6);
        if ($roll <= 4)
            return [['stat', 'initiative']];
        if ($roll == 5)
            return [['stat', 'strength']];
        if ($roll <= 7)
            return [['stat', 'weapon_skill'], ['stat', 'ballistic_skill']];
        if ($roll == 8)
            return [['stat', 'attacks']];
        if ($roll == 9)
            return [['stat', 'leadership']];
        \Mordheim\BattleLogger::add("Reroll");
        return self::rollHenchmanAdvancement();
    }
}