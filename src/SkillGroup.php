<?php

namespace Mordheim;

enum SkillGroup
{
    use EnumTryFromNameTrait;

    case COMBAT;
    case SHOOTING;
    case ACADEMIC;
    case STRENGTH;
    case SPEED;
    case SPECIAL;

    public function getSpecialRules(Blank $blank): array
    {
        return match ($this) {
            self::COMBAT => [
                SpecialRule::STRIKE_TO_INJURE,
                SpecialRule::COMBAT_MASTER,
                SpecialRule::WEAPONS_TRAINING,
                SpecialRule::WEB_OF_STEEL,
                SpecialRule::EXPERT_SWORDSMAN,
                SpecialRule::STEP_ASIDE,
            ],
            self::SHOOTING => [
                SpecialRule::QUICK_SHOT,
                SpecialRule::PISTOLIER,
                SpecialRule::EAGLE_EYES,
                SpecialRule::WEAPONS_EXPERT,
                SpecialRule::NIMBLE,
                SpecialRule::TRICK_SHOOTER,
                SpecialRule::HUNTER,
                SpecialRule::KNIFE_FIGHTER
            ],
            self::ACADEMIC => [
                SpecialRule::BATTLE_TONGUE,
                SpecialRule::SORCERY,
                SpecialRule::STREETWISE,
                SpecialRule::HAGGLE,
                SpecialRule::ARCANE_LORE,
                SpecialRule::WYRDSTONE_HUNTER,
                SpecialRule::WARRIOR_WIZARD,
            ],
            self::STRENGTH => [
                SpecialRule::MIGHTY_BLOW,
                SpecialRule::PIT_FIGHTER,
                SpecialRule::RESILIENT,
                SpecialRule::FEARSOME,
                SpecialRule::STRONGMAN,
                SpecialRule::UNSTOPPABLE_CHARGE,
            ],
            self::SPEED => [
                SpecialRule::LEAP,
                SpecialRule::SPRINT,
                SpecialRule::ACROBAT,
                SpecialRule::LIGHTNING_REFLEXES,
                SpecialRule::JUMP_UP,
                SpecialRule::DODGE,
                SpecialRule::SCALE_SHEER_SURFACES,
            ],
            self::SPECIAL => $this->getSpecialSkills($blank),
        };
    }

    private function getSpecialSkills(Blank $blank): array
    {
        return match ($blank->getWarband()) {
            Warband::SISTERS_OF_SIGMAR => [
                SpecialRule::SIGN_OF_SIGMAR,
                SpecialRule::PROTECTION_OF_SIGMAR,
                SpecialRule::UTTER_DETERMINATION,
                SpecialRule::RIGHTEOUS_FURY,
                SpecialRule::ABSOLUTE_FAITH,
            ],
            Warband::SKAVEN => [
                SpecialRule::BLACK_HUNGER,
                SpecialRule::TAIL_FIGHTING,
                SpecialRule::WALL_RUNNER,
                SpecialRule::INFILTRATION,
                SpecialRule::ART_OF_SILENT_DEATH,
            ],
            Warband::HIRED_SWORDS => match ($blank) {
                Blank::ELF_RANGER => [
                    SpecialRule::SEEKER,
                    SpecialRule::EXCELLENT_SIGHT,
                ],
                Blank::DWARF_TROLL_SLAYER => [
                    SpecialRule::DEATHWISH,
                    SpecialRule::HARD_TO_KILL,
                    SpecialRule::HARD_HEAD
                ]
            }
        };
    }
}