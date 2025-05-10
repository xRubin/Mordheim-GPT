<?php

namespace Mordheim\Data;

use Mordheim\SkillGroupInterface;
use Mordheim\SpecialRule;
use Mordheim\Traits\EnumTryFromNameTrait;
use Mordheim\WarbandInterface;

enum SkillGroup implements SkillGroupInterface
{
    use EnumTryFromNameTrait;

    case COMBAT;
    case SHOOTING;
    case ACADEMIC;
    case STRENGTH;
    case SPEED;
    case SPECIAL;

    public function getSkills(WarbandInterface $warband): array
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
                SpecialRule::CAUSE_FEAR,
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
            self::SPECIAL => [],
        };
    }
}