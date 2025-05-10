<?php

namespace Mordheim\Data;

use Mordheim\BlankInterface;
use Mordheim\Data\Attributes\Characteristics;
use Mordheim\Data\Attributes\EquipmentList;
use Mordheim\Data\Attributes\Henchman;
use Mordheim\Data\Attributes\Hero;
use Mordheim\Data\Attributes\HireFee;
use Mordheim\Data\Attributes\HiredSword;
use Mordheim\Data\Attributes\MaxCount;
use Mordheim\Data\Attributes\MinCount;
use Mordheim\Data\Attributes\SkillGroup;
use Mordheim\Data\Attributes\SpecialRule;
use Mordheim\Data\Attributes\StartExp;
use Mordheim\Data\Attributes\UpkeepFee;
use Mordheim\Data\Attributes\Warband;
use Mordheim\EquipmentInterface;
use Mordheim\EquipmentListInterface;
use Mordheim\Exceptions\InvalidAttributesException;
use Mordheim\WarbandInterface;
use Mordheim\Data\Attributes\Equipment;
use Mordheim\Data\Attributes\ExceptWarband;
use Mordheim\Data\Attributes\Rating;

enum Blank implements BlankInterface
{
    #[Warband('REIKLAND'), Hero]
    #[HireFee(60)]
    #[StartExp(20)]
    #[MinCount(1), MaxCount(1)]
    #[Characteristics(4, 4, 4, 3, 3, 1, 4, 1, 8)]
    #[EquipmentList('MERCENARY_EQUIPMENT_LIST')]
    #[SpecialRule('LEADER')]
    #[SkillGroup('COMBAT'), SkillGroup('SHOOTING'), SkillGroup('ACADEMIC'), SkillGroup('STRENGTH'), SkillGroup('SPEED')]
    case REIKLAND_MERCENARY_CAPTAIN;
    #[Warband('REIKLAND'), Hero]
    #[HireFee(35)]
    #[StartExp(8)]
    #[MaxCount(2)]
    #[Characteristics(4, 4, 3, 3, 3, 1, 3, 1, 7)]
    #[EquipmentList('MERCENARY_EQUIPMENT_LIST')]
    #[SkillGroup('COMBAT'), SkillGroup('SHOOTING'), SkillGroup('STRENGTH')]
    case REIKLAND_CHAMPION;
    #[Warband('REIKLAND'), Hero]
    #[HireFee(15)]
    #[MaxCount(2)]
    #[Characteristics(4, 2, 2, 3, 3, 1, 3, 1, 6)]
    #[EquipmentList('MERCENARY_EQUIPMENT_LIST')]
    #[SkillGroup('COMBAT'), SkillGroup('SHOOTING'), SkillGroup('SPEED')]
    case REIKLAND_YOUNGBLOOD;
    #[Warband('REIKLAND'), Henchman]
    #[HireFee(25)]
    #[Characteristics(4, 3, 3, 3, 3, 1, 3, 1, 7)]
    #[EquipmentList('MERCENARY_EQUIPMENT_LIST')]
    case REIKLAND_WARRIOR;
    #[Warband('REIKLAND'), Henchman]
    #[HireFee(25)]
    #[MaxCount(7)]
    #[Characteristics(4, 3, 4, 3, 3, 1, 3, 1, 7)]
    #[EquipmentList('MARKSMAN_EQUIPMENT_LIST')]
    case REIKLAND_MARKSMAN;
    #[Warband('REIKLAND'), Henchman]
    #[HireFee(35)]
    #[MaxCount(5)]
    #[Characteristics(4, 3, 3, 3, 3, 1, 3, 1, 7)]
    #[EquipmentList('MERCENARY_EQUIPMENT_LIST')]
    #[SpecialRule('EXPERT_SWORDSMAN')]
    case REIKLAND_SWORDSMAN;

    #[Warband('MIDDENHEIM'), Hero]
    #[HireFee(60)]
    #[StartExp(20)]
    #[MinCount(1), MaxCount(1)]
    #[Characteristics(4, 4, 4, 4, 3, 1, 4, 1, 8)]
    #[EquipmentList('MERCENARY_EQUIPMENT_LIST')]
    #[SpecialRule('LEADER')]
    #[SkillGroup('COMBAT'), SkillGroup('SHOOTING'), SkillGroup('ACADEMIC'), SkillGroup('STRENGTH'), SkillGroup('SPEED')]
    case MIDDENHEIM_MERCENARY_CAPTAIN;
    #[Warband('MIDDENHEIM'), Hero]
    #[HireFee(35)]
    #[StartExp(8)]
    #[MaxCount(2)]
    #[Characteristics(4, 4, 3, 4, 3, 1, 3, 1, 7)]
    #[EquipmentList('MERCENARY_EQUIPMENT_LIST')]
    #[SkillGroup('COMBAT'), SkillGroup('STRENGTH'), SkillGroup('SPEED')]
    case MIDDENHEIM_CHAMPION;
    #[Warband('MIDDENHEIM'), Hero]
    #[HireFee(15)]
    #[MaxCount(2)]
    #[Characteristics(4, 2, 2, 3, 3, 1, 3, 1, 6)]
    #[EquipmentList('MERCENARY_EQUIPMENT_LIST')]
    #[SkillGroup('COMBAT'), SkillGroup('SHOOTING'), SkillGroup('SPEED')]
    case MIDDENHEIM_YOUNGBLOOD;
    #[Warband('MIDDENHEIM'), Henchman]
    #[HireFee(25)]
    #[Characteristics(4, 3, 3, 3, 3, 1, 3, 1, 7)]
    #[EquipmentList('MERCENARY_EQUIPMENT_LIST')]
    case MIDDENHEIM_WARRIOR;
    #[Warband('MIDDENHEIM'), Henchman]
    #[HireFee(25)]
    #[MaxCount(7)]
    #[Characteristics(4, 3, 3, 3, 3, 1, 3, 1, 7)]
    #[EquipmentList('MARKSMAN_EQUIPMENT_LIST')]
    case MIDDENHEIM_MARKSMAN;
    #[Warband('MIDDENHEIM'), Henchman]
    #[HireFee(35)]
    #[MaxCount(5)]
    #[Characteristics(4, 3, 3, 3, 3, 1, 3, 1, 7)]
    #[EquipmentList('MERCENARY_EQUIPMENT_LIST')]
    #[SpecialRule('EXPERT_SWORDSMAN')]
    case MIDDENHEIM_SWORDSMAN;

    #[Warband('MARIENBURG'), Hero]
    #[HireFee(60)]
    #[StartExp(20)]
    #[MinCount(1), MaxCount(1)]
    #[Characteristics(4, 4, 4, 3, 3, 1, 4, 1, 8)]
    #[EquipmentList('MERCENARY_EQUIPMENT_LIST')]
    #[SpecialRule('LEADER')]
    #[SkillGroup('COMBAT'), SkillGroup('SHOOTING'), SkillGroup('ACADEMIC'), SkillGroup('STRENGTH'), SkillGroup('SPEED')]
    case MARIENBURG_MERCENARY_CAPTAIN;
    #[Warband('MARIENBURG'), Hero]
    #[HireFee(35)]
    #[StartExp(8)]
    #[MaxCount(2)]
    #[Characteristics(4, 4, 3, 3, 3, 1, 3, 1, 7)]
    #[EquipmentList('MERCENARY_EQUIPMENT_LIST')]
    #[SkillGroup('COMBAT'), SkillGroup('SHOOTING'), SkillGroup('SPEED')]
    case MARIENBURG_CHAMPION;
    #[Warband('MARIENBURG'), Hero]
    #[HireFee(15)]
    #[MaxCount(2)]
    #[Characteristics(4, 2, 2, 3, 3, 1, 3, 1, 6)]
    #[EquipmentList('MERCENARY_EQUIPMENT_LIST')]
    #[SkillGroup('COMBAT'), SkillGroup('STRENGTH'), SkillGroup('SPEED')]
    case MARIENBURG_YOUNGBLOOD;
    #[Warband('MARIENBURG'), Henchman]
    #[HireFee(25)]
    #[Characteristics(4, 3, 3, 3, 3, 1, 3, 1, 7)]
    #[EquipmentList('MERCENARY_EQUIPMENT_LIST')]
    case MARIENBURG_WARRIOR;
    #[Warband('MARIENBURG'), Henchman]
    #[HireFee(25)]
    #[MaxCount(7)]
    #[Characteristics(4, 3, 3, 3, 3, 1, 3, 1, 7)]
    #[EquipmentList('MARKSMAN_EQUIPMENT_LIST')]
    case MARIENBURG_MARKSMAN;
    #[Warband('MARIENBURG'), Henchman]
    #[HireFee(35)]
    #[MaxCount(5)]
    #[Characteristics(4, 3, 3, 3, 3, 1, 3, 1, 7)]
    #[EquipmentList('MERCENARY_EQUIPMENT_LIST')]
    #[SpecialRule('EXPERT_SWORDSMAN')]
    case MARIENBURG_SWORDSMAN;

    #[Warband('CULT_OF_THE_POSSESSED'), Hero]
    #[HireFee(70)]
    #[MinCount(1), MaxCount(1)]
    #[Characteristics(4, 4, 4, 3, 3, 1, 3, 1, 8)]
    #[EquipmentList('POSSESSED_EQUIPMENT_LIST')]
    #[SpecialRule('LEADER'), SpecialRule('WIZARD_CHAOS_RITUALS')]
    #[SkillGroup('COMBAT'), SkillGroup('ACADEMIC'), SkillGroup('SPEED')]
    case CULT_MAGISTER;
    #[Warband('CULT_OF_THE_POSSESSED'), Hero]
    #[HireFee(90)]
    #[MaxCount(2)]
    #[Characteristics(5, 4, 0, 4, 4, 2, 4, 2, 7)]
    #[EquipmentList('EMPTY')]
    #[SpecialRule('CAUSE_FEAR'), SpecialRule('MUTATIONS')]
    #[SkillGroup('COMBAT'), SkillGroup('STRENGTH'), SkillGroup('SPEED')]
    case CULT_POSSESSED;
    #[Warband('CULT_OF_THE_POSSESSED'), Hero]
    #[HireFee(25)]
    #[MaxCount(2)]
    #[Characteristics(4, 3, 3, 3, 3, 1, 3, 1, 7)]
    #[EquipmentList('POSSESSED_EQUIPMENT_LIST')]
    #[SpecialRule('MUTATIONS')]
    #[SkillGroup('COMBAT'), SkillGroup('SPEED')]
    case CULT_MUTANT;
    #[Warband('CULT_OF_THE_POSSESSED'), Henchman]
    #[HireFee(35)]
    #[MaxCount(5)]
    #[Characteristics(4, 2, 2, 4, 3, 1, 3, 1, 6)]
    #[EquipmentList('DARKSOUL_EQUIPMENT_LIST')]
    #[SpecialRule('CRAZED')]
    case CULT_DARKSOUL;
    #[Warband('CULT_OF_THE_POSSESSED'), Henchman]
    #[HireFee(25)]
    #[Characteristics(4, 3, 3, 3, 3, 1, 3, 1, 7)]
    #[EquipmentList('POSSESSED_EQUIPMENT_LIST')]
    case CULT_BROTHER;
    #[Warband('CULT_OF_THE_POSSESSED'), Henchman]
    #[HireFee(45)]
    #[MaxCount(3)]
    #[Characteristics(4, 4, 3, 3, 4, 2, 3, 1, 7)]
    #[EquipmentList('DARKSOUL_EQUIPMENT_LIST')]
    case CULT_BEASTMAN;

    #[Warband('WITCH_HUNTERS'), Hero]
    #[HireFee(60)]
    #[StartExp(20)]
    #[MinCount(1), MaxCount(1)]
    #[Characteristics(4, 4, 4, 3, 3, 1, 4, 1, 8)]
    #[EquipmentList('WITCH_HUNTER_EQUIPMENT_LIST')]
    #[SpecialRule('LEADER'), SpecialRule('BURN_THE_WITCH')]
    #[SkillGroup('COMBAT'), SkillGroup('SHOOTING'), SkillGroup('ACADEMIC'), SkillGroup('STRENGTH'), SkillGroup('SPEED')]
    case WITCH_HUNTER_CAPTAIN;
    #[Warband('WITCH_HUNTERS'), Hero]
    #[HireFee(40)]
    #[StartExp(12)]
    #[MaxCount(1)]
    #[Characteristics(4, 3, 3, 3, 3, 1, 3, 1, 8)]
    #[EquipmentList('WITCH_HUNTER_EQUIPMENT_LIST')]
    #[SpecialRule('PRAYERS')]
    #[SkillGroup('COMBAT'), SkillGroup('ACADEMIC'), SkillGroup('STRENGTH')]
    case WARRIOR_PRIEST;
    #[Warband('WITCH_HUNTERS'), Hero]
    #[HireFee(25)]
    #[StartExp(8)]
    #[MaxCount(3)]
    #[Characteristics(4, 3, 3, 3, 3, 1, 3, 1, 7)]
    #[EquipmentList('WITCH_HUNTER_EQUIPMENT_LIST')]
    #[SpecialRule('BURN_THE_WITCH')]
    #[SkillGroup('COMBAT'), SkillGroup('SHOOTING'), SkillGroup('ACADEMIC'), SkillGroup('SPEED')]
    case WITCH_HUNTER;
    #[Warband('WITCH_HUNTERS'), Henchman]
    #[HireFee(20)]
    #[Characteristics(4, 2, 2, 3, 3, 1, 3, 1, 7)]
    #[EquipmentList('ZEALOT_EQUIPMENT_LIST')]
    case ZEALOT;
    #[Warband('WITCH_HUNTERS'), Henchman]
    #[HireFee(40)]
    #[MaxCount(5)]
    #[Characteristics(4, 3, 3, 4, 4, 1, 3, 1, 10)]
    #[EquipmentList('FLAGELLANT_EQUIPMENT_LIST')]
    #[SpecialRule('FANATICAL')]
    case FLAGELLANT;
    #[Warband('WITCH_HUNTERS'), Henchman]
    #[HireFee(15)]
    #[MaxCount(5)]
    #[Characteristics(6, 4, 0, 4, 3, 1, 4, 1, 5)]
    #[EquipmentList('EMPTY')]
    #[SpecialRule('ANIMAL')]
    case WARHOUND;

    #[Warband('SISTERS_OF_SIGMAR'), Hero]
    #[HireFee(70)]
    #[StartExp(20)]
    #[MinCount(1), MaxCount(1)]
    #[Characteristics(4, 4, 4, 3, 3, 1, 4, 1, 8)]
    #[EquipmentList('SISTERS_OF_SIGMAR_HERO_EQUIPMENT_LIST')]
    #[SpecialRule('LEADER'), SpecialRule('PRAYERS_OF_SIGMAR')]
    #[SkillGroup('COMBAT'), SkillGroup('ACADEMIC'), SkillGroup('STRENGTH'), SkillGroup('SPEED'), SkillGroup('SPECIAL')]
    case SIGMARITE_MATRIARCH;
    #[Warband('SISTERS_OF_SIGMAR'), Hero]
    #[HireFee(35)]
    #[StartExp(8)]
    #[MaxCount(3)]
    #[Characteristics(4, 4, 3, 3, 3, 1, 3, 1, 7)]
    #[EquipmentList('SISTERS_OF_SIGMAR_HERO_EQUIPMENT_LIST')]
    #[SkillGroup('COMBAT'), SkillGroup('ACADEMIC'), SkillGroup('STRENGTH'), SkillGroup('SPEED'), SkillGroup('SPECIAL')]
    case SIGMARITE_SISTER_SUPERIOR;
    #[Warband('SISTERS_OF_SIGMAR'), Hero]
    #[HireFee(25)]
    #[MaxCount(1)]
    #[Characteristics(4, 2, 2, 3, 3, 1, 3, 1, 7)]
    #[EquipmentList('SISTERS_OF_SIGMAR_HERO_EQUIPMENT_LIST')]
    #[SpecialRule('BLESSED_SIGHT')]
    #[SkillGroup('ACADEMIC'), SkillGroup('SPEED'), SkillGroup('SPECIAL')]
    case SIGMARITE_AUGUR;
    #[Warband('SISTERS_OF_SIGMAR'), Henchman]
    #[HireFee(25)]
    #[Characteristics(4, 3, 3, 3, 3, 1, 3, 1, 7)]
    #[EquipmentList('SISTERS_OF_SIGMAR_EQUIPMENT_LIST')]
    case SIGMARITE_SISTER;
    #[Warband('SISTERS_OF_SIGMAR'), Henchman]
    #[HireFee(15)]
    #[MaxCount(10)]
    #[Characteristics(4, 2, 2, 3, 3, 1, 3, 1, 6)]
    #[EquipmentList('SISTERS_OF_SIGMAR_EQUIPMENT_LIST')]
    case SIGMARITE_NOVICE;

    #[Warband('UNDEAD'), Hero]
    #[HireFee(110)]
    #[MinCount(1), MaxCount(1)]
    #[Characteristics(6, 4, 4, 4, 4, 2, 5, 2, 8)]
    #[EquipmentList('UNDEAD_EQUIPMENT_LIST')]
    #[SpecialRule('LEADER'), SpecialRule('CAUSE_FEAR'), SpecialRule('IMMUNE_TO_PSYCHOLOGY'), SpecialRule('IMMUNE_TO_POISON'), SpecialRule('NO_PAIN')]
    #[SkillGroup('COMBAT'), SkillGroup('ACADEMIC'), SkillGroup('STRENGTH'), SkillGroup('SPEED')]
    case UNDEAD_VAMPIRE;
    #[Warband('UNDEAD'), Hero]
    #[HireFee(35)]
    #[MaxCount(1)]
    #[Characteristics(4, 3, 3, 3, 3, 1, 3, 1, 7)]
    #[EquipmentList('UNDEAD_EQUIPMENT_LIST')]
    #[SpecialRule('WIZARD_NECROMANCER')]
    #[SkillGroup('ACADEMIC'), SkillGroup('SPEED')]
    case UNDEAD_NECROMANCER;
    #[Warband('UNDEAD'), Hero]
    #[HireFee(20)]
    #[MaxCount(3)]
    #[Characteristics(4, 2, 2, 3, 3, 1, 3, 1, 7)]
    #[EquipmentList('UNDEAD_EQUIPMENT_LIST')]
    #[SkillGroup('COMBAT'), SkillGroup('STRENGTH')]
    case UNDEAD_DREG;
    #[Warband('UNDEAD'), Henchman]
    #[HireFee(15)]
    #[Characteristics(4, 2, 0, 3, 3, 1, 1, 1, 5)]
    #[EquipmentList('EMPTY')]
    #[SpecialRule('CAUSE_FEAR'), SpecialRule('MAY_NOT_RUN'), SpecialRule('IMMUNE_TO_PSYCHOLOGY'), SpecialRule('IMMUNE_TO_POISON'), SpecialRule('NO_PAIN'), SpecialRule('NO_BRAIN')]
    case UNDEAD_ZOMBIE;
    #[Warband('UNDEAD'), Henchman]
    #[HireFee(40)]
    #[Characteristics(4, 2, 2, 3, 4, 1, 3, 2, 5)]
    #[EquipmentList('EMPTY')]
    #[SpecialRule('CAUSE_FEAR')]
    case UNDEAD_GHOUL;
    #[Warband('UNDEAD'), Henchman]
    #[HireFee(50)]
    #[MaxCount(5)]
    #[Characteristics(9, 3, 0, 4, 3, 1, 2, 1, 4)]
    #[EquipmentList('EMPTY')]
    #[SpecialRule('CHARGE'), SpecialRule('CAUSE_FEAR'), SpecialRule('MAY_NOT_RUN'), SpecialRule('IMMUNE_TO_PSYCHOLOGY'), SpecialRule('IMMUNE_TO_POISON'), SpecialRule('UNLIVING'), SpecialRule('NO_PAIN')]
    case UNDEAD_DIRE_WOLF;

    #[Warband('SKAVEN'), Hero]
    #[HireFee(60)]
    #[MinCount(1), MaxCount(1)]
    #[StartExp(20)]
    #[Characteristics(6, 4, 4, 4, 3, 1, 5, 1, 7)]
    #[EquipmentList('SKAVEN_HEROES_EQUIPMENT_LIST')]
    #[SpecialRule('LEADER'), SpecialRule('PERFECT_KILLER')]
    #[SkillGroup('COMBAT'), SkillGroup('SHOOTING'), SkillGroup('ACADEMIC'), SkillGroup('STRENGTH'), SkillGroup('SPEED'), SkillGroup('SPECIAL')]
    case SKAVEN_ASSASSIN_ADEPT;
    #[Warband('SKAVEN'), Hero]
    #[HireFee(45)]
    #[MaxCount(1)]
    #[StartExp(8)]
    #[Characteristics(5, 3, 3, 3, 3, 1, 4, 1, 6)]
    #[EquipmentList('SKAVEN_HEROES_EQUIPMENT_LIST')]
    #[SpecialRule('WIZARD_MAGIC_OF_THE_HORNED_RAT')]
    #[SkillGroup('ACADEMIC'), SkillGroup('SPEED'), SkillGroup('SPECIAL')]
    case SKAVEN_ESHIN_SORCERER;
    #[Warband('SKAVEN'), Hero]
    #[HireFee(40)]
    #[MaxCount(2)]
    #[StartExp(8)]
    #[Characteristics(6, 4, 3, 4, 3, 1, 5, 1, 6)]
    #[EquipmentList('SKAVEN_HEROES_EQUIPMENT_LIST')]
    #[SkillGroup('COMBAT'), SkillGroup('SHOOTING'), SkillGroup('STRENGTH'), SkillGroup('SPEED'), SkillGroup('SPECIAL')]
    case SKAVEN_BLACK_SKAVEN;
    #[Warband('SKAVEN'), Hero]
    #[HireFee(20)]
    #[MaxCount(2)]
    #[Characteristics(6, 2, 3, 3, 3, 1, 4, 1, 4)]
    #[EquipmentList('SKAVEN_HENCHMEN_EQUIPMENT_LIST')]
    #[SkillGroup('COMBAT'), SkillGroup('SHOOTING'), SkillGroup('SPECIAL')]
    case SKAVEN_NIGHT_RUNNER;
    #[Warband('SKAVEN'), Henchman]
    #[HireFee(20)]
    #[Characteristics(5, 3, 3, 3, 3, 1, 4, 1, 5)]
    #[EquipmentList('SKAVEN_HENCHMEN_EQUIPMENT_LIST')]
    case SKAVEN_VERMINKIN;
    #[Warband('SKAVEN'), Henchman]
    #[HireFee(15)]
    #[Characteristics(6, 2, 0, 3, 3, 1, 4, 1, 4)]
    #[EquipmentList('EMPTY')]
    #[SpecialRule('ANIMAL')]
    case SKAVEN_GIANT_RAT;
    #[Warband('SKAVEN'), Henchman, Rating(20)]
    #[HireFee(210)]
    #[MaxCount(1)]
    #[Characteristics(6, 3, 3, 5, 5, 3, 4, 3, 4)]
    #[EquipmentList('EMPTY')]
    #[SpecialRule('CAUSE_FEAR'), SpecialRule('STUPIDITY'), SpecialRule('ANIMAL'), SpecialRule('LARGE_TARGET')]
    case SKAVEN_RAT_OGRE;

    #[Warband('HIRED_SWORDS'), HiredSword, Rating(100)]
    #[ExceptWarband('SKAVEN'), ExceptWarband('UNDEAD'), ExceptWarband('CULT_OF_THE_POSSESSED')]
    #[HireFee(150)]
    #[MaxCount(1)]
    #[Characteristics(5, 8, 4, 4, 3, 2, 7, 3, 8)]
    #[Equipment('ITHILMAR_ARMOR'), Equipment('ELVEN_CLOAK'), Equipment('SWORD_IENH_KHAIN')]
    #[SpecialRule('STRIKE_TO_INJURE'), SpecialRule('EXPERT_SWORDSMAN'), SpecialRule('STEP_ASIDE')]
    #[SpecialRule('SPRINT'), SpecialRule('LIGHTNING_REFLEXES'), SpecialRule('DODGE'), SpecialRUle('MIGHTY_BLOW')]
    #[SpecialRule('INVINCIBLE_SWORDSMAN'), SpecialRule('WANDERER')]
    case AENUR_THE_SWORD_OF_TWILIGHT;


    public function getWarband(): ?WarbandInterface
    {
        $ref = new \ReflectionClassConstant(self::class, $this->name);
        $classAttributes = $ref->getAttributes(Warband::class);

        if (count($classAttributes) === 0)
            throw new InvalidAttributesException('Invalid attributes for: ' . $this->name);

        return $classAttributes[0]->newInstance()->getValue();
    }

    public function getHireFee(): int
    {
        $ref = new \ReflectionClassConstant(self::class, $this->name);
        $classAttributes = $ref->getAttributes(HireFee::class);

        if (count($classAttributes) === 0)
            throw new InvalidAttributesException('Invalid attributes for: ' . $this->name);

        return $classAttributes[0]->newInstance()->getValue();
    }

    public function getUpkeepFee(): int
    {
        $ref = new \ReflectionClassConstant(self::class, $this->name);
        $classAttributes = $ref->getAttributes(UpkeepFee::class);

        if (count($classAttributes) === 0)
            return 0;

        return $classAttributes[0]->newInstance()->getValue();
    }

    public function getRating(): int
    {
        $ref = new \ReflectionClassConstant(self::class, $this->name);
        $classAttributes = $ref->getAttributes(Rating::class);

        if (count($classAttributes) === 0)
            return 0;

        return $classAttributes[0]->newInstance()->getValue();
    }

    public function getStartExp(): int
    {
        $ref = new \ReflectionClassConstant(self::class, $this->name);
        $classAttributes = $ref->getAttributes(StartExp::class);

        if (count($classAttributes) === 0)
            return 0;

        return $classAttributes[0]->newInstance()->getValue();
    }

    /**
     * @return EquipmentInterface[]
     */
    public function getEquipment(): array
    {
        $ref = new \ReflectionClassConstant(self::class, $this->name);
        $classAttributes = $ref->getAttributes(Equipment::class);

        if (count($classAttributes) === 0)
            return [];

        return array_map(
            fn($attribute) => $attribute->newInstance()->getValue(),
            $classAttributes
        );
    }

    public function getMinCount(): int
    {
        $ref = new \ReflectionClassConstant(self::class, $this->name);
        $classAttributes = $ref->getAttributes(MinCount::class);

        if (count($classAttributes) === 0)
            return 0;

        return $classAttributes[0]->newInstance()->getValue();
    }

    public function getMaxCount(): int
    {
        $ref = new \ReflectionClassConstant(self::class, $this->name);
        $classAttributes = $ref->getAttributes(MaxCount::class);

        if (count($classAttributes) === 0)
            return 99;

        return $classAttributes[0]->newInstance()->getValue();
    }

    public function isHero(): bool
    {
        $ref = new \ReflectionClassConstant(self::class, $this->name);
        $classAttributes = $ref->getAttributes(Hero::class);

        return count($classAttributes) > 0;
    }

    public function isHenchman(): bool
    {
        $ref = new \ReflectionClassConstant(self::class, $this->name);
        $classAttributes = $ref->getAttributes(Henchman::class);

        return count($classAttributes) > 0;
    }

    public function isHiredSword(): bool
    {
        $ref = new \ReflectionClassConstant(self::class, $this->name);
        $classAttributes = $ref->getAttributes(HiredSword::class);

        return count($classAttributes) > 0;
    }

    public function getCharacteristics(): \Mordheim\Characteristics
    {
        $ref = new \ReflectionClassConstant(self::class, $this->name);
        $classAttributes = $ref->getAttributes(Characteristics::class);

        if (count($classAttributes) === 0)
            throw new InvalidAttributesException('Invalid attributes for: ' . $this->name);

        return $classAttributes[0]->newInstance()->getValue();
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

    public function getEquipmentList(): EquipmentListInterface
    {
        $ref = new \ReflectionClassConstant(self::class, $this->name);
        $classAttributes = $ref->getAttributes(EquipmentList::class);

        if (count($classAttributes) === 0)
            throw new InvalidAttributesException('Invalid attributes for: ' . $this->name);

        return $classAttributes[0]->newInstance()->getValue();
    }

    public function getAdvancementSkillGroups(): array
    {
        $ref = new \ReflectionClassConstant(self::class, $this->name);
        $classAttributes = $ref->getAttributes(SkillGroup::class);

        if (count($classAttributes) === 0)
            return [];

        return array_map(
            fn($attribute) => $attribute->newInstance()->getValue(),
            $classAttributes
        );
    }
}