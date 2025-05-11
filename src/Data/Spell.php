<?php

namespace Mordheim\Data;

use Mordheim\Data\Attributes\Difficulty;
use Mordheim\Data\Attributes\SpecialRule;
use Mordheim\Exceptions\InvalidAttributesException;
use Mordheim\SpellInterface;

enum Spell implements SpellInterface
{
    #[SpecialRule('PRAYERS_OF_SIGMAR')]
    #[Difficulty(7)]
    case HAMMER_OF_SIGMAR;
    #[SpecialRule('PRAYERS_OF_SIGMAR')]
    #[Difficulty(8)]
    case HEARTS_OF_STEEL;
    #[SpecialRule('PRAYERS_OF_SIGMAR')]
    #[Difficulty(9)]
    case SOULFIRE;
    #[SpecialRule('PRAYERS_OF_SIGMAR')]
    #[Difficulty(6)]
    case SHIELD_OF_FAITH;
    #[SpecialRule('PRAYERS_OF_SIGMAR')]
    #[Difficulty(5)]
    case HEALING_HAND;
    #[SpecialRule('PRAYERS_OF_SIGMAR')]
    #[Difficulty(9)]
    case ARMOUR_OF_RIGHTEOUSNESS;

    #[SpecialRule('WIZARD_NECROMANCY')]
    #[Difficulty(10)]
    case LIFESTEALER;
    #[SpecialRule('WIZARD_NECROMANCY')]
    #[Difficulty(5)]
    case RE_ANIMATION;
    #[SpecialRule('WIZARD_NECROMANCY')]
    #[Difficulty(6)]
    case DEATH_VISION;
    #[SpecialRule('WIZARD_NECROMANCY')]
    #[Difficulty(9)]
    case SPELL_OF_DOOM;
    #[SpecialRule('WIZARD_NECROMANCY')]
    #[Difficulty(6]
    case CALL_OF_VANHEL;
    #[SpecialRule('WIZARD_NECROMANCY')]
    #[Difficulty(0)]
    case SPELL_OF_AWAKENING;

    #[SpecialRule('WIZARD_CHAOS_RITUALS')]
    #[Difficulty(10)]
    case VISION_OF_TORMENT;
    #[SpecialRule('WIZARD_CHAOS_RITUALS')]
    #[Difficulty(7)]
    case EYE_OF_GOD;
    #[SpecialRule('WIZARD_CHAOS_RITUALS')]
    #[Difficulty(8)]
    case DARK_BLOOD;
    #[SpecialRule('WIZARD_CHAOS_RITUALS')]
    #[Difficulty(9)]
    case LURE_OD_CHAOS;
    #[SpecialRule('WIZARD_CHAOS_RITUALS')]
    #[Difficulty(7)]
    case WINGS_OF_DARKNESS;
    #[SpecialRule('WIZARD_CHAOS_RITUALS')]
    #[Difficulty(3)]
    case WORD_OF_PAIN;

    #[SpecialRule('WIZARD_LESSER_MAGIC')]
    #[Difficulty(7)]
    case FIRES_OF_UZHUL;
    #[SpecialRule('WIZARD_LESSER_MAGIC')]
    #[Difficulty(7)]
    case FLIGHT_OF_ZIMMERAN;
    #[SpecialRule('WIZARD_LESSER_MAGIC')]
    #[Difficulty(7)]
    case DREAD_OF_ARAMAR;
    #[SpecialRule('WIZARD_LESSER_MAGIC')]
    #[Difficulty(7)]
    case SILVER_ARROWS_OF_ARHA;
    #[SpecialRule('WIZARD_LESSER_MAGIC')]
    #[Difficulty(6)]
    case LUCK_OF_SHEMTEK;
    #[SpecialRule('WIZARD_LESSER_MAGIC')]
    #[Difficulty(7)]
    case SWORD_OF_REZHEBEL;

    #[SpecialRule('WIZARD_MAGIC_OF_THE_HORNED_RAT')]
    #[Difficulty(8)]
    case WARPFIRE;
    #[SpecialRule('WIZARD_MAGIC_OF_THE_HORNED_RAT')]
    #[Difficulty(0)]
    case CHILDREN_OF_THE_HORNED_RAT;
    #[SpecialRule('WIZARD_MAGIC_OF_THE_HORNED_RAT')]
    #[Difficulty(8)]
    case GNAWDOOM;
    #[SpecialRule('WIZARD_MAGIC_OF_THE_HORNED_RAT')]
    #[Difficulty(4)]
    case BLACK_FURY;
    #[SpecialRule('WIZARD_MAGIC_OF_THE_HORNED_RAT')]
    #[Difficulty(8)]
    case EYE_OF_THE_WARP;
    #[SpecialRule('WIZARD_MAGIC_OF_THE_HORNED_RAT')]
    #[Difficulty(6)]
    case SORCERERS_CURSE;

    public function getOwnerSpecialRule(): \Mordheim\SpecialRule
    {
        $ref = new \ReflectionClassConstant(self::class, $this->name);
        $classAttributes = $ref->getAttributes(SpecialRule::class);

        if (count($classAttributes) === 0)
            throw new InvalidAttributesException('Invalid attributes for: ' . $this->name);

        return $classAttributes[0]->newInstance()->getValue();
    }

    public function getBlankDifficulty(): int
    {
        $ref = new \ReflectionClassConstant(self::class, $this->name);
        $classAttributes = $ref->getAttributes(Difficulty::class);

        if (count($classAttributes) === 0)
            return 0;

        return $classAttributes[0]->newInstance()->getValue();
    }
}