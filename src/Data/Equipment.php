<?php

namespace Mordheim\Data;

use Mordheim\Data\Attributes\Cost;
use Mordheim\Data\Attributes\MaximumRange;
use Mordheim\Data\Attributes\SlotArmor;
use Mordheim\Data\Attributes\SlotHelmet;
use Mordheim\Data\Attributes\SlotMelee;
use Mordheim\Data\Attributes\SlotMisc;
use Mordheim\Data\Attributes\SlotRanged;
use Mordheim\Data\Attributes\SpecialRule;
use Mordheim\Data\Attributes\Strength;
use Mordheim\Data\Attributes\StrengthBonus;
use Mordheim\Data\Attributes\Warband;
use Mordheim\EquipmentInterface;
use Mordheim\Exceptions\InvalidAttributesException;
use Mordheim\Slot;
use Mordheim\SpecialRuleInterface;
use Mordheim\Traits\EnumTryFromNameTrait;
use Mordheim\Data\Attributes\SaveModifier;

enum Equipment implements EquipmentInterface
{
    use EnumTryFromNameTrait;

    #[StrengthBonus(-1)]
    #[SpecialRule('PLUS_1_ENEMY_ARMOR_SAVE')]
    #[SlotMelee]
    case FIST;
    #[SpecialRule('PLUS_1_ENEMY_ARMOR_SAVE')]
    #[SlotMelee]
    case DAGGER;
    #[SpecialRule('CONCUSSION')]
    #[SlotMelee]
    case HAMMER;
    #[SpecialRule('CONCUSSION')]
    #[SlotMelee]
    case STAFF;
    #[SpecialRule('CONCUSSION')]
    #[SlotMelee]
    case MACE;
    #[SpecialRule('CONCUSSION')]
    #[SlotMelee]
    case CLUB;
    #[SpecialRule('CUTTING_EDGE')]
    #[SlotMelee]
    case AXE;
    #[SpecialRule('PARRY')]
    #[SlotMelee]
    case SWORD;
    #[StrengthBonus(+2)]
    #[SpecialRule('HEAVY')]
    #[SpecialRule('TWO_HANDED')]
    #[SlotMelee]
    case FLAIL;
    #[StrengthBonus(+1)]
    #[SpecialRule('HEAVY')]
    #[SpecialRule('DIFFICULT_TO_USE')]
    #[SlotMelee]
    case MORNING_STAR;
    #[StrengthBonus(+1)]
    #[SpecialRule('TWO_HANDED')]
    #[SlotMelee]
    case HALBERD;
    #[SpecialRule('STRIKE_FIRST')]
    #[SpecialRule('UNWIELDY')]
    #[SpecialRule('CAVALRY_BONUS')]
    #[SlotMelee]
    case SPEAR;
    #[StrengthBonus(+2)]
    #[SpecialRule('CAVALRY_BONUS')]
    #[SlotMelee]
    case LANCE;
    #[StrengthBonus(+2)]
    #[SpecialRule('TWO_HANDED')]
    #[SpecialRule('STRIKE_LAST')]
    #[SlotMelee]
    case DOUBLE_HANDED_SWORD;
    #[StrengthBonus(+2)]
    #[SpecialRule('TWO_HANDED')]
    #[SpecialRule('STRIKE_LAST')]
    #[SlotMelee]
    case DOUBLE_HANDED_HAMMER;
    #[StrengthBonus(+2)]
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
    #[SpecialRule('MINUS_1_SAVE_MODIFIER')]
    #[SlotRanged]
    case ELF_BOW;
    #[MaximumRange(30)]
    #[Strength(4)]
    #[SpecialRule('MOVE_OR_FIRE')]
    #[SlotRanged]
    case CROSSBOW;
    #[MaximumRange(30)]
    #[Strength(3)]
    #[SpecialRule('FIRE_TWICE_AT_HALF_RANGE')]
    #[SlotRanged]
    case SLING;
    #[MaximumRange(6)]
    #[SpecialRule('THROWN_WEAPON')]
    #[SlotRanged]
    case THROWING_STARS;
    #[MaximumRange(6)]
    #[SpecialRule('THROWN_WEAPON')]
    #[SlotRanged]
    case THROWING_KNIVES;
    #[MaximumRange(24)]
    #[Strength(3)]
    #[SpecialRule('FIRE_TWICE')]
    #[SlotRanged]
    case REPEATER_CROSSBOW;
    #[MaximumRange(10)]
    #[Strength(4)]
    #[SpecialRule('SHOOT_IN_HAND_TO_HAND_COMBAT')]
    #[SlotRanged]
    case CROSSBOW_PISTOL;

    #[MaximumRange(6)]
    #[Strength(4)]
    #[SpecialRule('PREPARE_SHOT')]
    #[SpecialRule('SAVE_MODIFIER')]
    #[SpecialRule('HAND_TO_HAND')]
    #[SlotRanged]
    case PISTOL;
    #[MaximumRange(10)]
    #[Strength(4)]
    #[SpecialRule('ACCURACY')]
    #[SpecialRule('PREPARE_SHOT')]
    #[SpecialRule('SAVE_MODIFIER')]
    #[SpecialRule('HAND_TO_HAND')]
    #[SlotRanged]
    case DUELLING_PISTOL;
    #[MaximumRange(16)]
    #[Strength(3)]
    #[SpecialRule('SHOT')]
    #[SpecialRule('FIRE_ONCE')]
    #[SlotRanged]
    case BLUNDERBUSS;
    #[MaximumRange(24)]
    #[Strength(4)]
    #[SpecialRule('PREPARE_SHOT')]
    #[SpecialRule('MOVE_OR_FIRE')]
    #[SpecialRule('SAVE_MODIFIER')]
    #[SlotRanged]
    case HANDGUN;
    #[MaximumRange(48)]
    #[Strength(4)]
    #[SpecialRule('MOVE_OR_FIRE')]
    #[SpecialRule('PREPARE_SHOT')]
    #[SpecialRule('PICK_TARGET')]
    #[SpecialRule('SAVE_MODIFIER')]
    #[SlotRanged]
    case HOCHLAND_LONG_RIFFLE;

    #[SpecialRule('SAVE_6')]
    #[SlotArmor]
    case LIGHT_ARMOR;
    #[SpecialRule('SAVE_5')]
    #[SpecialRule('MOVEMENT')]
    #[SlotArmor]
    case HEAVY_ARMOR;
    #[SpecialRule('SAVE_6')]
    #[SlotMelee]
    case SHIELD;
    #[SlotMelee]
    case BUCKLER;
    #[SlotHelmet]
    #[SpecialRule('AVOID_STUN')]
    case HELMET;
    #[SpecialRule('SAVE_5')]
    #[SlotArmor]
    case ITHILMAR_ARMOR;
    #[SpecialRule('SAVE_4')]
    #[SlotArmor]
    case GROMRIL_ARMOR;

    #[SpecialRule('ITHILMAR')]
    #[SpecialRule('PLUS_1_ENEMY_ARMOR_SAVE')]
    #[SlotMelee]
    case ITHILMAR_DAGGER;
    #[SpecialRule('ITHILMAR')]
    #[SpecialRule('CONCUSSION')]
    #[SlotMelee]
    case ITHILMAR_HAMMER;
    #[SpecialRule('ITHILMAR')]
    #[SpecialRule('CONCUSSION')]
    #[SlotMelee]
    case ITHILMAR_STAFF;
    #[SpecialRule('ITHILMAR')]
    #[SpecialRule('CONCUSSION')]
    #[SlotMelee]
    case ITHILMAR_MACE;
    #[SpecialRule('ITHILMAR')]
    #[SpecialRule('CONCUSSION')]
    #[SlotMelee]
    case ITHILMAR_CLUB;
    #[SpecialRule('ITHILMAR')]
    #[SpecialRule('CUTTING_EDGE')]
    #[SlotMelee]
    case ITHILMAR_AXE;
    #[SpecialRule('ITHILMAR')]
    #[StrengthBonus(+2)]
    #[SpecialRule('HEAVY')]
    #[SpecialRule('TWO_HANDED')]
    #[SlotMelee]
    case ITHILMAR_FLAIL;
    #[SpecialRule('ITHILMAR')]
    #[StrengthBonus(+1)]
    #[SpecialRule('HEAVY')]
    #[SpecialRule('DIFFICULT_TO_USE')]
    #[SlotMelee]
    case ITHILMAR_MORNING_STAR;
    #[SpecialRule('ITHILMAR')]
    #[StrengthBonus(+1)]
    #[SpecialRule('TWO_HANDED')]
    #[SlotMelee]
    case ITHILMAR_HALBERD;
    #[SpecialRule('ITHILMAR')]
    #[SpecialRule('STRIKE_FIRST')]
    #[SpecialRule('UNWIELDY')]
    #[SpecialRule('CAVALRY_BONUS')]
    #[SlotMelee]
    case ITHILMAR_SPEAR;
    #[SpecialRule('ITHILMAR')]
    #[StrengthBonus(+2)]
    #[SpecialRule('CAVALRY_BONUS')]
    #[SlotMelee]
    case ITHILMAR_LANCE;
    #[SpecialRule('ITHILMAR')]
    #[StrengthBonus(+2)]
    #[SpecialRule('TWO_HANDED')]
    #[SpecialRule('STRIKE_LAST')]
    #[SlotMelee]
    case ITHILMAR_DOUBLE_HANDED_SWORD;
    #[SpecialRule('ITHILMAR')]
    #[StrengthBonus(+2)]
    #[SpecialRule('TWO_HANDED')]
    #[SpecialRule('STRIKE_LAST')]
    #[SlotMelee]
    case ITHILMAR_DOUBLE_HANDED_HAMMER;
    #[SpecialRule('ITHILMAR')]
    #[StrengthBonus(+2)]
    #[SpecialRule('TWO_HANDED')]
    #[SpecialRule('STRIKE_LAST')]
    #[SlotMelee]
    case ITHILMAR_DOUBLE_HANDED_AXE;

    #[SpecialRule('GROMRIL')]
    #[SpecialRule('PLUS_1_ENEMY_ARMOR_SAVE')]
    #[SlotMelee]
    case GROMRIL_DAGGER;
    #[SpecialRule('GROMRIL')]
    #[SpecialRule('CONCUSSION')]
    #[SlotMelee]
    case GROMRIL_HAMMER;
    #[SpecialRule('GROMRIL')]
    #[SpecialRule('CONCUSSION')]
    #[SlotMelee]
    case GROMRIL_STAFF;
    #[SpecialRule('GROMRIL')]
    #[SpecialRule('CONCUSSION')]
    #[SlotMelee]
    case GROMRIL_MACE;
    #[SpecialRule('GROMRIL')]
    #[SpecialRule('CONCUSSION')]
    #[SlotMelee]
    case GROMRIL_CLUB;
    #[SpecialRule('GROMRIL')]
    #[SpecialRule('CUTTING_EDGE')]
    #[SlotMelee]
    case GROMRIL_AXE;
    #[StrengthBonus(+2)]
    #[SpecialRule('GROMRIL')]
    #[SpecialRule('HEAVY')]
    #[SpecialRule('TWO_HANDED')]
    #[SlotMelee]
    case GROMRIL_FLAIL;
    #[SpecialRule('GROMRIL')]
    #[StrengthBonus(+1)]
    #[SpecialRule('HEAVY')]
    #[SpecialRule('DIFFICULT_TO_USE')]
    #[SlotMelee]
    case GROMRIL_MORNING_STAR;
    #[SpecialRule('GROMRIL')]
    #[StrengthBonus(+1)]
    #[SpecialRule('TWO_HANDED')]
    #[SlotMelee]
    case GROMRIL_HALBERD;
    #[SpecialRule('GROMRIL')]
    #[SpecialRule('STRIKE_FIRST')]
    #[SpecialRule('UNWIELDY')]
    #[SpecialRule('CAVALRY_BONUS')]
    #[SlotMelee]
    case GROMRIL_SPEAR;
    #[SpecialRule('GROMRIL')]
    #[StrengthBonus(+2)]
    #[SpecialRule('CAVALRY_BONUS')]
    #[SlotMelee]
    case GROMRIL_LANCE;
    #[SpecialRule('GROMRIL')]
    #[StrengthBonus(+2)]
    #[SpecialRule('TWO_HANDED')]
    #[SpecialRule('STRIKE_LAST')]
    #[SlotMelee]
    case GROMRIL_DOUBLE_HANDED_SWORD;
    #[SpecialRule('GROMRIL')]
    #[StrengthBonus(+2)]
    #[SpecialRule('TWO_HANDED')]
    #[SpecialRule('STRIKE_LAST')]
    #[SlotMelee]
    case GROMRIL_DOUBLE_HANDED_HAMMER;
    #[SpecialRule('GROMRIL')]
    #[StrengthBonus(+2)]
    #[SpecialRule('TWO_HANDED')]
    #[SpecialRule('STRIKE_LAST')]
    #[SlotMelee]
    case GROMRIL_DOUBLE_HANDED_AXE;

    #[Warband('SISTERS_OF_SIGMAR')]
    #[Cost(15)]
    #[StrengthBonus(+1)]
    #[SpecialRule('CONCUSSION')]
    #[SpecialRule('HOLY_WEAPON')]
    #[SlotMelee]
    case SIGMARITE_WARHAMMER;
    #[Warband('SISTERS_OF_SIGMAR')]
    #[Cost(10)]
    #[SpecialRule('CANNOT_BE_PARRIED')]
    #[SpecialRule('WHIPCRACK')]
    #[SlotMelee]
    case STEEL_WHIP;

    #[Warband('SKAVEN')]
    #[Cost(25)]
    #[MaximumRange(8)]
    #[SaveModifier(+1)]
    #[SpecialRule('POISON')]
    #[SpecialRule('STEALTHY')]
    #[SlotRanged]
    case BLOWPIPE;
    #[Warband('SKAVEN')]
    #[Cost(35)]
    #[MaximumRange(5)]
    #[SaveModifier(-3)]
    #[SpecialRule('PREPARE_SHOT')]
    #[SlotRanged]
    case WARPLOCK_PISTOL;
    #[Warband('SKAVEN')]
    #[Cost(35)]
    #[SpecialRule('PAIR')]
    #[SpecialRule('CLIMB')]
    #[SpecialRule('PARRY')]
    #[SpecialRule('CUMBERSOME')]
    #[SlotMelee]
    case FIGHTING_CLAWS;

    #[Warband('SKAVEN')]
    #[Strength(5)]
    #[SaveModifier(-3)]
    #[SpecialRule('PAIR')]
    #[SpecialRule('PARRY')]
    #[SlotMelee]
    case ESHIN_FIGHTING_CLAWS;
    #[Warband('SKAVEN')]
    #[Cost(50)]
    #[SpecialRule('PAIR')]
    #[SpecialRule('VENOMOUS')]
    #[SpecialRule('PARRY')]
    #[SlotMelee]
    case WEEPING_BLADES;

    #[Warband('HIRED_SWORDS')]
    #[StrengthBonus(+1)]
    #[SpecialRule('PARRY')]
    #[SpecialRule('CRITICAL_HIT_ON_5')]
    #[SlotMelee]
    case SWORD_IENH_KHAIN;

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
        $classAttributes = $ref->getAttributes(SpecialRule::class);

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
                     SlotArmor::class,
                     SlotHelmet::class,
                     SlotMisc::class,
                 ] as $class) {
            $classAttributes = $ref->getAttributes($class);
            if (count($classAttributes))
                return $classAttributes[0]->newInstance()->getValue();
        }

        throw new InvalidAttributesException('Invalid attributes for: ' . $this->name);
    }

    public function hasSpecialRule(SpecialRuleInterface $rule): bool
    {
        return in_array($rule, $this->getSpecialRules());
    }
}
