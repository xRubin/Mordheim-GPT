<?php

namespace Mordheim\Data;

use Mordheim\Attributes\MaximumRange;
use Mordheim\Attributes\SpecialRule;
use Mordheim\Attributes\Strength;
use Mordheim\Attributes\StrengthBonus;

enum Weapon: int
{
    #[StrengthBonus(-1)]
    #[SpecialRule('PLUS_1_ENEMY_ARMOR_SAVE')]
    case FIST = 0;
    #[SpecialRule('PLUS_1_ENEMY_ARMOR_SAVE')]
    case DAGGER = 1;
    #[SpecialRule('CONCUSSION')]
    case HAMMER = 2;
    #[SpecialRule('CONCUSSION')]
    case STAFF = 3;
    #[SpecialRule('CONCUSSION')]
    case MACE = 4;
    #[SpecialRule('CONCUSSION')]
    case CLUB = 5;
    #[SpecialRule('CUTTING_EDGE')]
    case AXE = 6;
    #[StrengthBonus(+2)]
    #[SpecialRule('HEAVY')]
    #[SpecialRule('TWO_HANDED')]
    case FLAIL = 7;
    #[StrengthBonus(+1)]
    #[SpecialRule('HEAVY')]
    #[SpecialRule('DIFFICULT_TO_USE')]
    case MORNING_STAR = 8;
    #[StrengthBonus(+1)]
    #[SpecialRule('TWO_HANDED')]
    case HALBERD = 9;
    #[SpecialRule('STRIKE_FIRST')]
    #[SpecialRule('UNWIELDY')]
    #[SpecialRule('CAVALRY_BONUS')]
    case SPEAR = 10;
    #[StrengthBonus(+2)]
    #[SpecialRule('CAVALRY_BONUS')]
    case LANCE = 11;
    #[StrengthBonus(+2)]
    #[SpecialRule('TWO_HANDED')]
    #[SpecialRule('STRIKE_LAST')]
    case DOUBLE_HANDED_SWORD = 12;
    #[StrengthBonus(+2)]
    #[SpecialRule('TWO_HANDED')]
    #[SpecialRule('STRIKE_LAST')]
    case DOUBLE_HANDED_HAMMER = 13;
    #[StrengthBonus(+2)]
    case DOUBLE_HANDED_AXE = 14;

    #[MaximumRange(16)]
    #[Strength(3)]
    case SHORT_BOW = 15;
    #[MaximumRange(24)]
    #[Strength(3)]
    case BOW = 16;
    #[MaximumRange(30)]
    #[Strength(3)]
    case LONG_BOW = 17;
    #[MaximumRange(36)]
    #[Strength(3)]
    #[SpecialRule('MINUS_1_SAVE_MODIFIER')]
    case ELF_BOW = 18;
    #[MaximumRange(30)]
    #[Strength(4)]
    #[SpecialRule('MOVE_OR_FIRE')]
    case CROSSBOW = 19;
    #[MaximumRange(30)]
    #[Strength(3)]
    #[SpecialRule('FIRE_TWICE_AT_HALF_RANGE')]
    case SLING = 20;
    #[MaximumRange(6)]
    #[SpecialRule('THROWN_WEAPON')]
    case THROWING_STAR = 21;
    #[MaximumRange(6)]
    #[SpecialRule('THROWN_WEAPON')]
    case THROWING_KNIFE = 22;
    #[MaximumRange(24)]
    #[Strength(3)]
    #[SpecialRule('FIRE_TWICE')]
    case REPEATER_CROSSBOW = 23;
    #[MaximumRange(10)]
    #[Strength(4)]
    #[SpecialRule('SHOOT_IN_HAND_TO_HAND_COMBAT')]
    case CROSSBOW_PISTOL = 24;

    #[MaximumRange(6)]
    #[Strength(4)]
    #[SpecialRule('PREPARE_SHOT')]
    #[SpecialRule('SAVE_MODIFIER')]
    #[SpecialRule('HAND_TO_HAND')]
    case PISTOL = 25;
    #[MaximumRange(10)]
    #[Strength(4)]
    #[SpecialRule('ACCURACY')]
    #[SpecialRule('PREPARE_SHOT')]
    #[SpecialRule('SAVE_MODIFIER')]
    #[SpecialRule('HAND_TO_HAND')]
    case DUELLING_PISTOL = 26;
    #[MaximumRange(16)]
    #[Strength(3)]
    #[SpecialRule('SHOT')]
    #[SpecialRule('FIRE_ONCE')]
    case BLUNDERBUSS = 27;
    #[MaximumRange(24)]
    #[Strength(4)]
    #[SpecialRule('PREPARE_SHOT')]
    #[SpecialRule('MOVE_OR_FIRE')]
    #[SpecialRule('SAVE_MODIFIER')]
    case HANDGUN = 28;
    #[MaximumRange(48)]
    #[Strength(4)]
    #[SpecialRule('MOVE_OR_FIRE')]
    #[SpecialRule('PREPARE_SHOT')]
    #[SpecialRule('PICK_TARGET')]
    #[SpecialRule('SAVE_MODIFIER')]
    case HOCHLAND_LONG_RIFFLE = 29;

    #[SpecialRule('PLUS_1_ENEMY_ARMOR_SAVE')]
    case ITHILMAR_DAGGER = 1901;
    #[SpecialRule('CONCUSSION')]
    case ITHILMAR_HAMMER = 1902;
    #[SpecialRule('CONCUSSION')]
    case ITHILMAR_STAFF = 1903;
    #[SpecialRule('CONCUSSION')]
    case ITHILMAR_MACE = 1904;
    #[SpecialRule('CONCUSSION')]
    case ITHILMAR_CLUB = 1905;
    #[SpecialRule('CUTTING_EDGE')]
    case ITHILMAR_AXE = 1906;
    #[StrengthBonus(+2)]
    #[SpecialRule('HEAVY')]
    #[SpecialRule('TWO_HANDED')]
    case ITHILMAR_FLAIL = 1907;
    #[StrengthBonus(+1)]
    #[SpecialRule('HEAVY')]
    #[SpecialRule('DIFFICULT_TO_USE')]
    case ITHILMAR_MORNING_STAR = 1908;
    #[StrengthBonus(+1)]
    #[SpecialRule('TWO_HANDED')]
    case ITHILMAR_HALBERD = 1909;
    #[SpecialRule('STRIKE_FIRST')]
    #[SpecialRule('UNWIELDY')]
    #[SpecialRule('CAVALRY_BONUS')]
    case ITHILMAR_SPEAR = 1910;
    #[StrengthBonus(+2)]
    #[SpecialRule('CAVALRY_BONUS')]
    case ITHILMAR_LANCE = 1911;
    #[StrengthBonus(+2)]
    #[SpecialRule('TWO_HANDED')]
    #[SpecialRule('STRIKE_LAST')]
    case ITHILMAR_DOUBLE_HANDED_SWORD = 1912;
    #[StrengthBonus(+2)]
    #[SpecialRule('TWO_HANDED')]
    #[SpecialRule('STRIKE_LAST')]
    case ITHILMAR_DOUBLE_HANDED_HAMMER = 1913;
    #[StrengthBonus(+2)]
    #[SpecialRule('TWO_HANDED')]
    #[SpecialRule('STRIKE_LAST')]
    case ITHILMAR_DOUBLE_HANDED_AXE = 1914;

    #[SpecialRule('PLUS_1_ENEMY_ARMOR_SAVE')]
    case GROMRIL_DAGGER = 2001;
    #[SpecialRule('CONCUSSION')]
    case GROMRIL_HAMMER = 2002;
    #[SpecialRule('CONCUSSION')]
    case GROMRIL_STAFF = 2003;
    #[SpecialRule('CONCUSSION')]
    case GROMRIL_MACE = 2004;
    #[SpecialRule('CONCUSSION')]
    case GROMRIL_CLUB = 2005;
    #[SpecialRule('CUTTING_EDGE')]
    case GROMRIL_AXE = 2006;
    #[StrengthBonus(+2)]
    #[SpecialRule('HEAVY')]
    #[SpecialRule('TWO_HANDED')]
    case GROMRIL_FLAIL = 2007;
    #[StrengthBonus(+1)]
    #[SpecialRule('HEAVY')]
    #[SpecialRule('DIFFICULT_TO_USE')]
    case GROMRIL_MORNING_STAR = 2008;
    #[StrengthBonus(+1)]
    #[SpecialRule('TWO_HANDED')]
    case GROMRIL_HALBERD = 209;
    #[SpecialRule('STRIKE_FIRST')]
    #[SpecialRule('UNWIELDY')]
    #[SpecialRule('CAVALRY_BONUS')]
    case GROMRIL_SPEAR = 2010;
    #[StrengthBonus(+2)]
    #[SpecialRule('CAVALRY_BONUS')]
    case GROMRIL_LANCE = 2011;
    #[StrengthBonus(+2)]
    #[SpecialRule('TWO_HANDED')]
    #[SpecialRule('STRIKE_LAST')]
    case GROMRIL_DOUBLE_HANDED_SWORD = 2012;
    #[StrengthBonus(+2)]
    #[SpecialRule('TWO_HANDED')]
    #[SpecialRule('STRIKE_LAST')]
    case GROMRIL_DOUBLE_HANDED_HAMMER = 2013;
    #[StrengthBonus(+2)]
    #[SpecialRule('TWO_HANDED')]
    #[SpecialRule('STRIKE_LAST')]
    case GROMRIL_DOUBLE_HANDED_AXE = 2014;

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

        $ref = new \ReflectionClassConstant(self::class, $this->name);
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
}
