<?php

namespace Mordheim;

use Mordheim\Attributes\Cost;
use Mordheim\Attributes\MaximumRange;
use Mordheim\Attributes\Rule;
use Mordheim\Attributes\SaveModifier;
use Mordheim\Attributes\SlotArmour;
use Mordheim\Attributes\SlotHelmet;
use Mordheim\Attributes\SlotMelee;
use Mordheim\Attributes\SlotMisc;
use Mordheim\Attributes\SlotRanged;
use Mordheim\Attributes\Strength;
use Mordheim\Attributes\StrengthBonus;
use Mordheim\Attributes\Warband;
use Mordheim\Exceptions\InvalidAttributesException;

enum Equipment
{
    use EnumTryFromNameTrait;

    #[StrengthBonus(-1)]
    #[Rule('PLUS_1_ENEMY_ARMOUR_SAVE')]
    #[SlotMelee]
    case FIST;
    #[Rule('PLUS_1_ENEMY_ARMOUR_SAVE')]
    #[SlotMelee]
    case DAGGER;
    #[Rule('CONCUSSION')]
    #[SlotMelee]
    case HAMMER;
    #[Rule('CONCUSSION')]
    #[SlotMelee]
    case STAFF;
    #[Rule('CONCUSSION')]
    #[SlotMelee]
    case MACE;
    #[Rule('CONCUSSION')]
    #[SlotMelee]
    case CLUB;
    #[Rule('CUTTING_EDGE')]
    #[SlotMelee]
    case AXE;
    #[Rule('PARRY')]
    #[SlotMelee]
    case SWORD;
    #[StrengthBonus(+2)]
    #[Rule('HEAVY')]
    #[Rule('TWO_HANDED')]
    #[SlotMelee]
    case FLAIL;
    #[StrengthBonus(+1)]
    #[Rule('HEAVY')]
    #[Rule('DIFFICULT_TO_USE')]
    #[SlotMelee]
    case MORNING_STAR;
    #[StrengthBonus(+1)]
    #[Rule('TWO_HANDED')]
    #[SlotMelee]
    case HALBERD;
    #[Rule('STRIKE_FIRST')]
    #[Rule('UNWIELDY')]
    #[Rule('CAVALRY_BONUS')]
    #[SlotMelee]
    case SPEAR;
    #[StrengthBonus(+2)]
    #[Rule('CAVALRY_BONUS')]
    #[SlotMelee]
    case LANCE;
    #[StrengthBonus(+2)]
    #[Rule('TWO_HANDED')]
    #[Rule('STRIKE_LAST')]
    #[SlotMelee]
    case DOUBLE_HANDED_SWORD;
    #[StrengthBonus(+2)]
    #[Rule('TWO_HANDED')]
    #[Rule('STRIKE_LAST')]
    #[SlotMelee]
    case DOUBLE_HANDED_HAMMER;
    #[StrengthBonus(+2)]
    #[Rule('TWO_HANDED')]
    #[Rule('CUTTING_EDGE')]
    #[SlotMelee]
    case DOUBLE_HANDED_AXE;

    #[MaximumRange(16)]
    #[Strength(3)]
    #[SlotRanged]
    case SHORT_BOW;
    #[MaximumRange(24)]
    #[Strength(3)]
    #[SlotRanged]
    case BOW;
    #[MaximumRange(30)]
    #[Strength(3)]
    #[SlotRanged]
    case LONG_BOW;
    #[MaximumRange(36)]
    #[Strength(3)]
    #[Rule('MINUS_1_SAVE_MODIFIER')]
    #[SlotRanged]
    case ELF_BOW;
    #[MaximumRange(30)]
    #[Strength(4)]
    #[Rule('MOVE_OR_FIRE')]
    #[SlotRanged]
    case CROSSBOW;
    #[MaximumRange(30)]
    #[Strength(3)]
    #[Rule('FIRE_TWICE_AT_HALF_RANGE')]
    #[SlotRanged]
    case SLING;
    #[MaximumRange(6)]
    #[Rule('THROWN_WEAPON')]
    #[SlotRanged]
    case THROWING_STARS;
    #[MaximumRange(6)]
    #[Rule('THROWN_WEAPON')]
    #[SlotRanged]
    case THROWING_KNIVES;
    #[MaximumRange(24)]
    #[Strength(3)]
    #[Rule('FIRE_TWICE')]
    #[SlotRanged]
    case REPEATER_CROSSBOW;
    #[MaximumRange(10)]
    #[Strength(4)]
    #[Rule('SHOOT_IN_HAND_TO_HAND_COMBAT')]
    #[SlotRanged]
    case CROSSBOW_PISTOL;

    #[MaximumRange(6)]
    #[Strength(4)]
    #[Rule('PREPARE_SHOT')]
    #[Rule('MINUS_1_SAVE_MODIFIER')]
    #[Rule('HAND_TO_HAND')]
    #[SlotRanged]
    case PISTOL;
    #[MaximumRange(10)]
    #[Strength(4)]
    #[Rule('ACCURACY')]
    #[Rule('PREPARE_SHOT')]
    #[Rule('MINUS_1_SAVE_MODIFIER')]
    #[Rule('HAND_TO_HAND')]
    #[SlotRanged]
    case DUELLING_PISTOL;
    #[MaximumRange(16)]
    #[Strength(3)]
    #[Rule('SHOT')]
    #[Rule('FIRE_ONCE')]
    #[SlotRanged]
    case BLUNDERBUSS;
    #[MaximumRange(24)]
    #[Strength(4)]
    #[Rule('PREPARE_SHOT')]
    #[Rule('MOVE_OR_FIRE')]
    #[Rule('MINUS_1_SAVE_MODIFIER')]
    #[SlotRanged]
    case HANDGUN;
    #[MaximumRange(48)]
    #[Strength(4)]
    #[Rule('MOVE_OR_FIRE')]
    #[Rule('PREPARE_SHOT')]
    #[Rule('PICK_TARGET')]
    #[Rule('MINUS_1_SAVE_MODIFIER')]
    #[SlotRanged]
    case HOCHLAND_LONG_RIFFLE;

    #[Rule('SAVE_6')]
    #[SlotArmour]
    case LIGHT_ARMOUR;
    #[Rule('SAVE_5')]
    #[Rule('MOVEMENT')]
    #[SlotArmour]
    case HEAVY_ARMOUR;
    #[Rule('SAVE_6')]
    #[SlotMelee]
    case SHIELD;
    #[Rule('PARRY')]
    #[SlotMelee]
    case BUCKLER;
    #[SlotHelmet]
    #[Rule('AVOID_STUN')]
    case HELMET;
    #[Rule('SAVE_5')]
    #[SlotArmour]
    case ITHILMAR_ARMOUR;
    #[Rule('SAVE_4')]
    #[SlotArmour]
    case GROMRIL_ARMOUR;

    #[Rule('ITHILMAR')]
    #[Rule('PLUS_1_ENEMY_ARMOUR_SAVE')]
    #[SlotMelee]
    case ITHILMAR_DAGGER;
    #[Rule('ITHILMAR')]
    #[Rule('CONCUSSION')]
    #[SlotMelee]
    case ITHILMAR_HAMMER;
    #[Rule('ITHILMAR')]
    #[Rule('CONCUSSION')]
    #[SlotMelee]
    case ITHILMAR_STAFF;
    #[Rule('ITHILMAR')]
    #[Rule('CONCUSSION')]
    #[SlotMelee]
    case ITHILMAR_MACE;
    #[Rule('ITHILMAR')]
    #[Rule('CONCUSSION')]
    #[SlotMelee]
    case ITHILMAR_CLUB;
    #[Rule('ITHILMAR')]
    #[Rule('CUTTING_EDGE')]
    #[SlotMelee]
    case ITHILMAR_AXE;
    #[Rule('ITHILMAR')]
    #[StrengthBonus(+2)]
    #[Rule('HEAVY')]
    #[Rule('TWO_HANDED')]
    #[SlotMelee]
    case ITHILMAR_FLAIL;
    #[Rule('ITHILMAR')]
    #[StrengthBonus(+1)]
    #[Rule('HEAVY')]
    #[Rule('DIFFICULT_TO_USE')]
    #[SlotMelee]
    case ITHILMAR_MORNING_STAR;
    #[Rule('ITHILMAR')]
    #[StrengthBonus(+1)]
    #[Rule('TWO_HANDED')]
    #[SlotMelee]
    case ITHILMAR_HALBERD;
    #[Rule('ITHILMAR')]
    #[Rule('STRIKE_FIRST')]
    #[Rule('UNWIELDY')]
    #[Rule('CAVALRY_BONUS')]
    #[SlotMelee]
    case ITHILMAR_SPEAR;
    #[Rule('ITHILMAR')]
    #[StrengthBonus(+2)]
    #[Rule('CAVALRY_BONUS')]
    #[SlotMelee]
    case ITHILMAR_LANCE;
    #[Rule('ITHILMAR')]
    #[StrengthBonus(+2)]
    #[Rule('TWO_HANDED')]
    #[Rule('STRIKE_LAST')]
    #[SlotMelee]
    case ITHILMAR_DOUBLE_HANDED_SWORD;
    #[Rule('ITHILMAR')]
    #[StrengthBonus(+2)]
    #[Rule('TWO_HANDED')]
    #[Rule('STRIKE_LAST')]
    #[SlotMelee]
    case ITHILMAR_DOUBLE_HANDED_HAMMER;
    #[Rule('ITHILMAR')]
    #[StrengthBonus(+2)]
    #[Rule('TWO_HANDED')]
    #[Rule('STRIKE_LAST')]
    #[SlotMelee]
    case ITHILMAR_DOUBLE_HANDED_AXE;

    #[Rule('GROMRIL')]
    #[Rule('PLUS_1_ENEMY_ARMOUR_SAVE')]
    #[SlotMelee]
    case GROMRIL_DAGGER;
    #[Rule('GROMRIL')]
    #[Rule('CONCUSSION')]
    #[SlotMelee]
    case GROMRIL_HAMMER;
    #[Rule('GROMRIL')]
    #[Rule('CONCUSSION')]
    #[SlotMelee]
    case GROMRIL_STAFF;
    #[Rule('GROMRIL')]
    #[Rule('CONCUSSION')]
    #[SlotMelee]
    case GROMRIL_MACE;
    #[Rule('GROMRIL')]
    #[Rule('CONCUSSION')]
    #[SlotMelee]
    case GROMRIL_CLUB;
    #[Rule('GROMRIL')]
    #[Rule('CUTTING_EDGE')]
    #[SlotMelee]
    case GROMRIL_AXE;
    #[StrengthBonus(+2)]
    #[Rule('GROMRIL')]
    #[Rule('HEAVY')]
    #[Rule('TWO_HANDED')]
    #[SlotMelee]
    case GROMRIL_FLAIL;
    #[Rule('GROMRIL')]
    #[StrengthBonus(+1)]
    #[Rule('HEAVY')]
    #[Rule('DIFFICULT_TO_USE')]
    #[SlotMelee]
    case GROMRIL_MORNING_STAR;
    #[Rule('GROMRIL')]
    #[StrengthBonus(+1)]
    #[Rule('TWO_HANDED')]
    #[SlotMelee]
    case GROMRIL_HALBERD;
    #[Rule('GROMRIL')]
    #[Rule('STRIKE_FIRST')]
    #[Rule('UNWIELDY')]
    #[Rule('CAVALRY_BONUS')]
    #[SlotMelee]
    case GROMRIL_SPEAR;
    #[Rule('GROMRIL')]
    #[StrengthBonus(+2)]
    #[Rule('CAVALRY_BONUS')]
    #[SlotMelee]
    case GROMRIL_LANCE;
    #[Rule('GROMRIL')]
    #[StrengthBonus(+2)]
    #[Rule('TWO_HANDED')]
    #[Rule('STRIKE_LAST')]
    #[SlotMelee]
    case GROMRIL_DOUBLE_HANDED_SWORD;
    #[Rule('GROMRIL')]
    #[StrengthBonus(+2)]
    #[Rule('TWO_HANDED')]
    #[Rule('STRIKE_LAST')]
    #[SlotMelee]
    case GROMRIL_DOUBLE_HANDED_HAMMER;
    #[Rule('GROMRIL')]
    #[StrengthBonus(+2)]
    #[Rule('TWO_HANDED')]
    #[Rule('STRIKE_LAST')]
    #[SlotMelee]
    case GROMRIL_DOUBLE_HANDED_AXE;

    #[Warband('SISTERS_OF_SIGMAR')]
    #[Cost(15)]
    #[StrengthBonus(+1)]
    #[Rule('CONCUSSION')]
    #[Rule('HOLY_WEAPON')]
    #[SlotMelee]
    case SIGMARITE_WARHAMMER;
    #[Warband('SISTERS_OF_SIGMAR')]
    #[Cost(10)]
    #[Rule('CANNOT_BE_PARRIED')]
    #[Rule('WHIPCRACK')]
    #[SlotMelee]
    case STEEL_WHIP;

    #[Warband('SKAVEN')]
    #[Cost(25)]
    #[MaximumRange(8)]
    #[SaveModifier(+1)]
    #[Rule('POISON')]
    #[Rule('STEALTHY')]
    #[SlotRanged]
    case BLOWPIPE;
    #[Warband('SKAVEN')]
    #[Cost(35)]
    #[MaximumRange(5)]
    #[SaveModifier(-3)]
    #[Rule('PREPARE_SHOT')]
    #[SlotRanged]
    case WARPLOCK_PISTOL;
    #[Warband('SKAVEN')]
    #[Cost(35)]
    #[Rule('PAIR')]
    #[Rule('CLIMB')]
    #[Rule('PARRY')]
    #[Rule('CUMBERSOME')]
    #[SlotMelee]
    case FIGHTING_CLAWS;

    #[Warband('SKAVEN')]
    #[Strength(5)]
    #[SaveModifier(-3)]
    #[Rule('PAIR')]
    #[Rule('PARRY')]
    #[SlotMelee]
    case ESHIN_FIGHTING_CLAWS;
    #[Warband('SKAVEN')]
    #[Cost(50)]
    #[Rule('PAIR')]
    #[Rule('VENOMOUS')]
    #[Rule('PARRY')]
    #[SlotMelee]
    case WEEPING_BLADES;

    #[Warband('HIRED_SWORDS')]
    #[StrengthBonus(+1)]
    #[Rule('PARRY')]
    #[Rule('CRITICAL_HIT_ON_5')]
    #[SlotMelee]
    case SWORD_IENH_KHAIN;

    #[Warband('HIRED_SWORDS')]
    #[Rule('PARRY')]
    #[SlotMelee]
    case SPIKED_GAUNTLET;

    #[SlotMelee]
    case ROPE_AND_HOOK;
    #[SlotMisc]
    case LUCKY_CHARM;
    #[SlotMisc]
    case HOLY_TOME;
    #[SlotMisc]
    case BLESSED_WATER;
    #[SlotMisc]
    case HOLY_RELIC;
    #[SlotMisc]
    case CRIMSON_SHADE;
    #[SlotMisc]
    case ELVEN_CLOAK;
    #[SlotMisc]
    case HUNTING_ARROWS;
    #[SlotMisc]
    case SUPERIOR_BLACKPOWDER;

    #[MaximumRange(8)]
    #[Strength(5)]
    #[SlotRanged]
    case DARK_BLOOD;
    #[MaximumRange(4)]
    #[Strength(3)]
    #[SlotRanged]
    case SOULFIRE_3;
    #[MaximumRange(4)]
    #[Strength(5)]
    #[SlotRanged]
    case SOULFIRE_5;
    #[MaximumRange(18)]
    #[Strength(4)]
    #[SlotRanged]
    case FIRE_OF_UZHUL;
    #[MaximumRange(3)]
    #[Strength(3)]
    #[SlotRanged]
    case WORD_OF_PAIN;
    #[MaximumRange(24)]
    #[Strength(3)]
    #[SlotRanged]
    case SILVER_ARROW_OF_ARHA;
    #[StrengthBonus(+2)]
    #[SlotMelee]
    case SWORD_OF_REZHEBEL;
    #[MaximumRange(8)]
    #[Strength(4)]
    #[SlotRanged]
    case WARPFIRE_DIRECT;
    #[MaximumRange(2)]
    #[Strength(3)]
    #[SlotRanged]
    case WARPFIRE_AOE;
    #[MaximumRange(8)]
    #[Strength(1)]
    #[SlotRanged]
    case GNAWDOOM;

    public function getName(): string
    {
        return $this->name;
    }

    public function getRange(): int
    {
        $ref = new \ReflectionClassConstant(self::class, $this->name);
        $classAttributes = $ref->getAttributes(MaximumRange::class);

        if (count($classAttributes) === 0)
            return 0;

        return $classAttributes[0]->newInstance()->getValue();
    }

    public function getStrength(int $fighterStrength): int
    {
        $ref = new \ReflectionClassConstant(self::class, $this->name);
        $classAttributes = $ref->getAttributes(Strength::class);

        if (count($classAttributes))
            return $classAttributes[0]->newInstance()->getValue();

        $classAttributes = $ref->getAttributes(StrengthBonus::class);

        if (count($classAttributes) === 0)
            return $fighterStrength;

        return $fighterStrength + $classAttributes[0]->newInstance()->getValue();
    }

    public function getSpecialRules(): array
    {
        $ref = new \ReflectionClassConstant(self::class, $this->name);
        $classAttributes = $ref->getAttributes(Rule::class);

        if (count($classAttributes) === 0)
            return [];

        return array_map(
            fn($attribute) => $attribute->newInstance()->getValue(),
            $classAttributes
        );
    }

    public function getSlot(): Slot
    {
        $ref = new \ReflectionClassConstant(self::class, $this->name);
        foreach ([
                     SlotMelee::class,
                     SlotRanged::class,
                     SlotArmour::class,
                     SlotHelmet::class,
                     SlotMisc::class,
                 ] as $class) {
            $classAttributes = $ref->getAttributes($class);
            if (count($classAttributes))
                return $classAttributes[0]->newInstance()->getValue();
        }

        throw new InvalidAttributesException('Invalid attributes for: ' . $this->name);
    }

    public function hasSpecialRule(SpecialRule $specialRule): bool
    {
        return in_array($specialRule, $this->getSpecialRules());
    }
}
