<?php

namespace Mordheim;

use Mordheim\Attributes\AllowedWarband;
use Mordheim\Attributes\Characteristics;
use Mordheim\Attributes\Equipment;
use Mordheim\Attributes\EquipmentList;
use Mordheim\Attributes\ExceptWarband;
use Mordheim\Attributes\Henchman;
use Mordheim\Attributes\Hero;
use Mordheim\Attributes\HiredSword;
use Mordheim\Attributes\HireFee;
use Mordheim\Attributes\MaxCharacteristics;
use Mordheim\Attributes\MaxCount;
use Mordheim\Attributes\MinCount;
use Mordheim\Attributes\Rating;
use Mordheim\Attributes\Rule;
use Mordheim\Attributes\SkillGroup;
use Mordheim\Attributes\StartExp;
use Mordheim\Attributes\UpkeepFee;
use Mordheim\Attributes\Warband;
use Mordheim\Exceptions\InvalidAttributesException;

enum Blank
{
    #[Warband('REIKLAND'), Hero]
    #[HireFee(60)]
    #[StartExp(20)]
    #[MinCount(1), MaxCount(1)]
    #[Characteristics(4, 4, 4, 3, 3, 1, 4, 1, 8), MaxCharacteristics(4, 6, 6, 4, 4, 3, 6, 4, 9)]
    #[EquipmentList('MERCENARY_EQUIPMENT_LIST')]
    #[Rule('LEADER')]
    #[SkillGroup('COMBAT'), SkillGroup('SHOOTING'), SkillGroup('ACADEMIC'), SkillGroup('STRENGTH'), SkillGroup('SPEED')]
    case REIKLAND_MERCENARY_CAPTAIN;
    #[Warband('REIKLAND'), Hero]
    #[HireFee(35)]
    #[StartExp(8)]
    #[MaxCount(2)]
    #[Characteristics(4, 4, 3, 3, 3, 1, 3, 1, 7), MaxCharacteristics(4, 6, 6, 4, 4, 3, 6, 4, 9)]
    #[EquipmentList('MERCENARY_EQUIPMENT_LIST')]
    #[SkillGroup('COMBAT'), SkillGroup('SHOOTING'), SkillGroup('STRENGTH')]
    case REIKLAND_CHAMPION;
    #[Warband('REIKLAND'), Hero]
    #[HireFee(15)]
    #[MaxCount(2)]
    #[Characteristics(4, 2, 2, 3, 3, 1, 3, 1, 6), MaxCharacteristics(4, 6, 6, 4, 4, 3, 6, 4, 9)]
    #[EquipmentList('MERCENARY_EQUIPMENT_LIST')]
    #[SkillGroup('COMBAT'), SkillGroup('SHOOTING'), SkillGroup('SPEED')]
    case REIKLAND_YOUNGBLOOD;
    #[Warband('REIKLAND'), Henchman]
    #[HireFee(25)]
    #[Characteristics(4, 3, 3, 3, 3, 1, 3, 1, 7), MaxCharacteristics(4, 4, 4, 4, 4, 2, 4, 2, 8)]
    #[EquipmentList('MERCENARY_EQUIPMENT_LIST')]
    case REIKLAND_WARRIOR;
    #[Warband('REIKLAND'), Henchman]
    #[HireFee(25)]
    #[MaxCount(7)]
    #[Characteristics(4, 3, 4, 3, 3, 1, 3, 1, 7), MaxCharacteristics(4, 4, 5, 4, 4, 2, 4, 2, 8)]
    #[EquipmentList('MARKSMAN_EQUIPMENT_LIST')]
    case REIKLAND_MARKSMAN;
    #[Warband('REIKLAND'), Henchman]
    #[HireFee(35)]
    #[MaxCount(5)]
    #[Characteristics(4, 3, 3, 3, 3, 1, 3, 1, 7), MaxCharacteristics(4, 4, 4, 4, 4, 2, 4, 2, 8)]
    #[EquipmentList('MERCENARY_EQUIPMENT_LIST')]
    #[Rule('EXPERT_SWORDSMAN')]
    case REIKLAND_SWORDSMAN;

    #[Warband('MIDDENHEIM'), Hero]
    #[HireFee(60)]
    #[StartExp(20)]
    #[MinCount(1), MaxCount(1)]
    #[Characteristics(4, 4, 4, 4, 3, 1, 4, 1, 8), MaxCharacteristics(4, 6, 6, 4, 4, 3, 6, 4, 9)]
    #[EquipmentList('MERCENARY_EQUIPMENT_LIST')]
    #[Rule('LEADER')]
    #[SkillGroup('COMBAT'), SkillGroup('SHOOTING'), SkillGroup('ACADEMIC'), SkillGroup('STRENGTH'), SkillGroup('SPEED')]
    case MIDDENHEIM_MERCENARY_CAPTAIN;
    #[Warband('MIDDENHEIM'), Hero]
    #[HireFee(35)]
    #[StartExp(8)]
    #[MaxCount(2)]
    #[Characteristics(4, 4, 3, 4, 3, 1, 3, 1, 7), MaxCharacteristics(4, 6, 6, 4, 4, 3, 6, 4, 9)]
    #[EquipmentList('MERCENARY_EQUIPMENT_LIST')]
    #[SkillGroup('COMBAT'), SkillGroup('STRENGTH'), SkillGroup('SPEED')]
    case MIDDENHEIM_CHAMPION;
    #[Warband('MIDDENHEIM'), Hero]
    #[HireFee(15)]
    #[MaxCount(2)]
    #[Characteristics(4, 2, 2, 3, 3, 1, 3, 1, 6), MaxCharacteristics(4, 6, 6, 4, 4, 3, 6, 4, 9)]
    #[EquipmentList('MERCENARY_EQUIPMENT_LIST')]
    #[SkillGroup('COMBAT'), SkillGroup('SHOOTING'), SkillGroup('SPEED')]
    case MIDDENHEIM_YOUNGBLOOD;
    #[Warband('MIDDENHEIM'), Henchman]
    #[HireFee(25)]
    #[Characteristics(4, 3, 3, 3, 3, 1, 3, 1, 7), MaxCharacteristics(4, 4, 4, 4, 4, 2, 4, 2, 8)]
    #[EquipmentList('MERCENARY_EQUIPMENT_LIST')]
    case MIDDENHEIM_WARRIOR;
    #[Warband('MIDDENHEIM'), Henchman]
    #[HireFee(25)]
    #[MaxCount(7)]
    #[Characteristics(4, 3, 3, 3, 3, 1, 3, 1, 7), MaxCharacteristics(4, 4, 4, 4, 4, 2, 4, 2, 8)]
    #[EquipmentList('MARKSMAN_EQUIPMENT_LIST')]
    case MIDDENHEIM_MARKSMAN;
    #[Warband('MIDDENHEIM'), Henchman]
    #[HireFee(35)]
    #[MaxCount(5)]
    #[Characteristics(4, 3, 3, 3, 3, 1, 3, 1, 7), MaxCharacteristics(4, 4, 4, 4, 4, 2, 4, 2, 8)]
    #[EquipmentList('MERCENARY_EQUIPMENT_LIST')]
    #[Rule('EXPERT_SWORDSMAN')]
    case MIDDENHEIM_SWORDSMAN;

    #[Warband('MARIENBURG'), Hero]
    #[HireFee(60)]
    #[StartExp(20)]
    #[MinCount(1), MaxCount(1)]
    #[Characteristics(4, 4, 4, 3, 3, 1, 4, 1, 8), MaxCharacteristics(4, 6, 6, 4, 4, 3, 6, 4, 9)]
    #[EquipmentList('MERCENARY_EQUIPMENT_LIST')]
    #[Rule('LEADER')]
    #[SkillGroup('COMBAT'), SkillGroup('SHOOTING'), SkillGroup('ACADEMIC'), SkillGroup('STRENGTH'), SkillGroup('SPEED')]
    case MARIENBURG_MERCENARY_CAPTAIN;
    #[Warband('MARIENBURG'), Hero]
    #[HireFee(35)]
    #[StartExp(8)]
    #[MaxCount(2)]
    #[Characteristics(4, 4, 3, 3, 3, 1, 3, 1, 7), MaxCharacteristics(4, 6, 6, 4, 4, 3, 6, 4, 9)]
    #[EquipmentList('MERCENARY_EQUIPMENT_LIST')]
    #[SkillGroup('COMBAT'), SkillGroup('SHOOTING'), SkillGroup('SPEED')]
    case MARIENBURG_CHAMPION;
    #[Warband('MARIENBURG'), Hero]
    #[HireFee(15)]
    #[MaxCount(2)]
    #[Characteristics(4, 2, 2, 3, 3, 1, 3, 1, 6), MaxCharacteristics(4, 6, 6, 4, 4, 3, 6, 4, 9)]
    #[EquipmentList('MERCENARY_EQUIPMENT_LIST')]
    #[SkillGroup('COMBAT'), SkillGroup('STRENGTH'), SkillGroup('SPEED')]
    case MARIENBURG_YOUNGBLOOD;
    #[Warband('MARIENBURG'), Henchman]
    #[HireFee(25)]
    #[Characteristics(4, 3, 3, 3, 3, 1, 3, 1, 7), MaxCharacteristics(4, 4, 4, 4, 4, 2, 4, 2, 8)]
    #[EquipmentList('MERCENARY_EQUIPMENT_LIST')]
    case MARIENBURG_WARRIOR;
    #[Warband('MARIENBURG'), Henchman]
    #[HireFee(25)]
    #[MaxCount(7)]
    #[Characteristics(4, 3, 3, 3, 3, 1, 3, 1, 7), MaxCharacteristics(4, 4, 4, 4, 4, 2, 4, 2, 8)]
    #[EquipmentList('MARKSMAN_EQUIPMENT_LIST')]
    case MARIENBURG_MARKSMAN;
    #[Warband('MARIENBURG'), Henchman]
    #[HireFee(35)]
    #[MaxCount(5)]
    #[Characteristics(4, 3, 3, 3, 3, 1, 3, 1, 7), MaxCharacteristics(4, 4, 4, 4, 4, 2, 4, 2, 8)]
    #[EquipmentList('MERCENARY_EQUIPMENT_LIST')]
    #[Rule('EXPERT_SWORDSMAN')]
    case MARIENBURG_SWORDSMAN;

    #[Warband('CULT_OF_THE_POSSESSED'), Hero]
    #[HireFee(70)]
    #[MinCount(1), MaxCount(1)]
    #[Characteristics(4, 4, 4, 3, 3, 1, 3, 1, 8), MaxCharacteristics(4, 6, 6, 4, 4, 3, 6, 4, 9)]
    #[EquipmentList('POSSESSED_EQUIPMENT_LIST')]
    #[Rule('LEADER'), Rule('WIZARD_CHAOS_RITUALS')]
    #[SkillGroup('COMBAT'), SkillGroup('ACADEMIC'), SkillGroup('SPEED')]
    case CULT_MAGISTER;
    #[Warband('CULT_OF_THE_POSSESSED'), Hero]
    #[HireFee(90)]
    #[MaxCount(2)]
    #[Characteristics(5, 4, 0, 4, 4, 2, 4, 2, 7), MaxCharacteristics(6, 8, 0, 6, 6, 4, 7, 5, 10)]
    #[EquipmentList('EMPTY')]
    #[Rule('CAUSE_FEAR'), Rule('MUTATIONS')]
    #[SkillGroup('COMBAT'), SkillGroup('STRENGTH'), SkillGroup('SPEED')]
    case CULT_POSSESSED;
    #[Warband('CULT_OF_THE_POSSESSED'), Hero]
    #[HireFee(25)]
    #[MaxCount(2)]
    #[Characteristics(4, 3, 3, 3, 3, 1, 3, 1, 7), MaxCharacteristics(4, 6, 6, 4, 4, 3, 6, 4, 9)]
    #[EquipmentList('POSSESSED_EQUIPMENT_LIST')]
    #[Rule('MUTATIONS')]
    #[SkillGroup('COMBAT'), SkillGroup('SPEED')]
    case CULT_MUTANT;
    #[Warband('CULT_OF_THE_POSSESSED'), Henchman]
    #[HireFee(35)]
    #[MaxCount(5)]
    #[Characteristics(4, 2, 2, 4, 3, 1, 3, 1, 6), MaxCharacteristics(4, 3, 3, 5, 4, 2, 4, 2, 7)]
    #[EquipmentList('DARKSOUL_EQUIPMENT_LIST')]
    #[Rule('CRAZED')]
    case CULT_DARKSOUL;
    #[Warband('CULT_OF_THE_POSSESSED'), Henchman]
    #[HireFee(25)]
    #[Characteristics(4, 3, 3, 3, 3, 1, 3, 1, 7), MaxCharacteristics(4, 4, 4, 4, 4, 2, 4, 2, 8)]
    #[EquipmentList('POSSESSED_EQUIPMENT_LIST')]
    case CULT_BROTHER;
    #[Warband('CULT_OF_THE_POSSESSED'), Henchman]
    #[HireFee(45)]
    #[MaxCount(3)]
    #[Characteristics(4, 4, 3, 3, 4, 2, 3, 1, 7), MaxCharacteristics(4, 5, 4, 4, 5, 3, 4, 2, 8)]
    #[EquipmentList('DARKSOUL_EQUIPMENT_LIST')]
    case CULT_BEASTMAN;

    #[Warband('WITCH_HUNTERS'), Hero]
    #[HireFee(60)]
    #[StartExp(20)]
    #[MinCount(1), MaxCount(1)]
    #[Characteristics(4, 4, 4, 3, 3, 1, 4, 1, 8), MaxCharacteristics(4, 6, 6, 4, 4, 3, 6, 4, 9)]
    #[EquipmentList('WITCH_HUNTER_EQUIPMENT_LIST')]
    #[Rule('LEADER'), Rule('BURN_THE_WITCH')]
    #[SkillGroup('COMBAT'), SkillGroup('SHOOTING'), SkillGroup('ACADEMIC'), SkillGroup('STRENGTH'), SkillGroup('SPEED')]
    case WITCH_HUNTER_CAPTAIN;
    #[Warband('WITCH_HUNTERS'), Hero]
    #[HireFee(40)]
    #[StartExp(12)]
    #[MaxCount(1)]
    #[Characteristics(4, 3, 3, 3, 3, 1, 3, 1, 8), MaxCharacteristics(4, 6, 6, 4, 4, 3, 6, 4, 9)]
    #[EquipmentList('WITCH_HUNTER_EQUIPMENT_LIST')]
    #[Rule('PRAYERS_OF_SIGMAR')]
    #[SkillGroup('COMBAT'), SkillGroup('ACADEMIC'), SkillGroup('STRENGTH')]
    case WARRIOR_PRIEST;
    #[Warband('WITCH_HUNTERS'), Hero]
    #[HireFee(25)]
    #[StartExp(8)]
    #[MaxCount(3)]
    #[Characteristics(4, 3, 3, 3, 3, 1, 3, 1, 7), MaxCharacteristics(4, 6, 6, 4, 4, 3, 6, 4, 9)]
    #[EquipmentList('WITCH_HUNTER_EQUIPMENT_LIST')]
    #[Rule('BURN_THE_WITCH')]
    #[SkillGroup('COMBAT'), SkillGroup('SHOOTING'), SkillGroup('ACADEMIC'), SkillGroup('SPEED')]
    case WITCH_HUNTER;
    #[Warband('WITCH_HUNTERS'), Henchman]
    #[HireFee(20)]
    #[Characteristics(4, 2, 2, 3, 3, 1, 3, 1, 7), MaxCharacteristics(4, 3, 3, 4, 4, 2, 4, 2, 8)]
    #[EquipmentList('ZEALOT_EQUIPMENT_LIST')]
    case ZEALOT;
    #[Warband('WITCH_HUNTERS'), Henchman]
    #[HireFee(40)]
    #[MaxCount(5)]
    #[Characteristics(4, 3, 3, 4, 4, 1, 3, 1, 10), MaxCharacteristics(4, 4, 4, 4, 4, 2, 4, 2, 9)]
    #[EquipmentList('FLAGELLANT_EQUIPMENT_LIST')]
    #[Rule('FANATICAL')]
    case FLAGELLANT;
    #[Warband('WITCH_HUNTERS'), Henchman]
    #[HireFee(15)]
    #[MaxCount(5)]
    #[Characteristics(6, 4, 0, 4, 3, 1, 4, 1, 5)]
    #[EquipmentList('EMPTY')]
    #[Rule('ANIMAL')]
    case WARHOUND;

    #[Warband('SISTERS_OF_SIGMAR'), Hero]
    #[HireFee(70)]
    #[StartExp(20)]
    #[MinCount(1), MaxCount(1)]
    #[Characteristics(4, 4, 4, 3, 3, 1, 4, 1, 8), MaxCharacteristics(4, 6, 6, 4, 4, 3, 6, 4, 9)]
    #[EquipmentList('SISTERS_OF_SIGMAR_HERO_EQUIPMENT_LIST')]
    #[Rule('LEADER'), Rule('PRAYERS_OF_SIGMAR')]
    #[SkillGroup('COMBAT'), SkillGroup('ACADEMIC'), SkillGroup('STRENGTH'), SkillGroup('SPEED'), SkillGroup('SPECIAL')]
    case SIGMARITE_MATRIARCH;
    #[Warband('SISTERS_OF_SIGMAR'), Hero]
    #[HireFee(35)]
    #[StartExp(8)]
    #[MaxCount(3)]
    #[Characteristics(4, 4, 3, 3, 3, 1, 3, 1, 7), MaxCharacteristics(4, 6, 6, 4, 4, 3, 6, 4, 9)]
    #[EquipmentList('SISTERS_OF_SIGMAR_HERO_EQUIPMENT_LIST')]
    #[SkillGroup('COMBAT'), SkillGroup('ACADEMIC'), SkillGroup('STRENGTH'), SkillGroup('SPEED'), SkillGroup('SPECIAL')]
    case SIGMARITE_SISTER_SUPERIOR;
    #[Warband('SISTERS_OF_SIGMAR'), Hero]
    #[HireFee(25)]
    #[MaxCount(1)]
    #[Characteristics(4, 2, 2, 3, 3, 1, 3, 1, 7), MaxCharacteristics(4, 6, 6, 4, 4, 3, 6, 4, 9)]
    #[EquipmentList('SISTERS_OF_SIGMAR_HERO_EQUIPMENT_LIST')]
    #[Rule('BLESSED_SIGHT')]
    #[SkillGroup('ACADEMIC'), SkillGroup('SPEED'), SkillGroup('SPECIAL')]
    case SIGMARITE_AUGUR;
    #[Warband('SISTERS_OF_SIGMAR'), Henchman]
    #[HireFee(25)]
    #[Characteristics(4, 3, 3, 3, 3, 1, 3, 1, 7), MaxCharacteristics(4, 4, 4, 4, 4, 2, 4, 2, 8)]
    #[EquipmentList('SISTERS_OF_SIGMAR_EQUIPMENT_LIST')]
    case SIGMARITE_SISTER;
    #[Warband('SISTERS_OF_SIGMAR'), Henchman]
    #[HireFee(15)]
    #[MaxCount(10)]
    #[Characteristics(4, 2, 2, 3, 3, 1, 3, 1, 6), MaxCharacteristics(4, 3, 3, 4, 4, 2, 4, 2, 7)]
    #[EquipmentList('SISTERS_OF_SIGMAR_EQUIPMENT_LIST')]
    case SIGMARITE_NOVICE;

    #[Warband('UNDEAD'), Hero]
    #[HireFee(110)]
    #[MinCount(1), MaxCount(1)]
    #[Characteristics(6, 4, 4, 4, 4, 2, 5, 2, 8), MaxCharacteristics(6, 8, 6, 7, 6, 4, 9, 4, 10)]
    #[EquipmentList('UNDEAD_EQUIPMENT_LIST')]
    #[Rule('LEADER'), Rule('CAUSE_FEAR'), Rule('IMMUNE_TO_PSYCHOLOGY'), Rule('IMMUNE_TO_POISON'), Rule('NO_PAIN')]
    #[SkillGroup('COMBAT'), SkillGroup('ACADEMIC'), SkillGroup('STRENGTH'), SkillGroup('SPEED')]
    case UNDEAD_VAMPIRE;
    #[Warband('UNDEAD'), Hero]
    #[HireFee(35)]
    #[MaxCount(1)]
    #[Characteristics(4, 3, 3, 3, 3, 1, 3, 1, 7), MaxCharacteristics(4, 6, 6, 4, 4, 3, 6, 4, 9)]
    #[EquipmentList('UNDEAD_EQUIPMENT_LIST')]
    #[Rule('WIZARD_NECROMANCY')]
    #[SkillGroup('ACADEMIC'), SkillGroup('SPEED')]
    case UNDEAD_NECROMANCER;
    #[Warband('UNDEAD'), Hero]
    #[HireFee(20)]
    #[MaxCount(3)]
    #[Characteristics(4, 2, 2, 3, 3, 1, 3, 1, 7), MaxCharacteristics(4, 6, 6, 4, 4, 3, 6, 4, 9)]
    #[EquipmentList('UNDEAD_EQUIPMENT_LIST')]
    #[SkillGroup('COMBAT'), SkillGroup('STRENGTH')]
    case UNDEAD_DREG;
    #[Warband('UNDEAD'), Henchman]
    #[HireFee(15)]
    #[Characteristics(4, 2, 0, 3, 3, 1, 1, 1, 5), MaxCharacteristics(5, 3, 0, 4, 4, 2, 2, 2, 6)]
    #[EquipmentList('EMPTY')]
    #[Rule('CAUSE_FEAR'), Rule('MAY_NOT_RUN'), Rule('IMMUNE_TO_PSYCHOLOGY')]
    #[Rule('IMMUNE_TO_POISON'), Rule('NO_PAIN'), Rule('NO_BRAIN')]
    case UNDEAD_ZOMBIE;
    #[Warband('UNDEAD'), Henchman]
    #[HireFee(40)]
    #[Characteristics(4, 2, 2, 3, 4, 1, 3, 2, 5), MaxCharacteristics(5, 3, 2, 4, 5, 2, 4, 3, 6)]
    #[EquipmentList('EMPTY')]
    #[Rule('CAUSE_FEAR')]
    case UNDEAD_GHOUL;
    #[Warband('UNDEAD'), Henchman]
    #[HireFee(50)]
    #[MaxCount(5)]
    #[Characteristics(9, 3, 0, 4, 3, 1, 2, 1, 4)]
    #[EquipmentList('EMPTY')]
    #[Rule('CHARGE'), Rule('CAUSE_FEAR'), Rule('MAY_NOT_RUN'), Rule('IMMUNE_TO_PSYCHOLOGY')]
    #[Rule('IMMUNE_TO_POISON'), Rule('UNLIVING'), Rule('NO_PAIN')]
    case UNDEAD_DIRE_WOLF;

    #[Warband('SKAVEN'), Hero]
    #[HireFee(60)]
    #[MinCount(1), MaxCount(1)]
    #[StartExp(20)]
    #[Characteristics(6, 4, 4, 4, 3, 1, 5, 1, 7), MaxCharacteristics(6, 6, 6, 4, 4, 3, 7, 4, 7)]
    #[EquipmentList('SKAVEN_HEROES_EQUIPMENT_LIST')]
    #[Rule('LEADER'), Rule('PERFECT_KILLER')]
    #[SkillGroup('COMBAT'), SkillGroup('SHOOTING'), SkillGroup('ACADEMIC'), SkillGroup('STRENGTH'), SkillGroup('SPEED'), SkillGroup('SPECIAL')]
    case SKAVEN_ASSASSIN_ADEPT;
    #[Warband('SKAVEN'), Hero]
    #[HireFee(45)]
    #[MaxCount(1)]
    #[StartExp(8)]
    #[Characteristics(5, 3, 3, 3, 3, 1, 4, 1, 6), MaxCharacteristics(6, 6, 6, 4, 4, 3, 7, 4, 7)]
    #[EquipmentList('SKAVEN_HEROES_EQUIPMENT_LIST')]
    #[Rule('WIZARD_MAGIC_OF_THE_HORNED_RAT')]
    #[SkillGroup('ACADEMIC'), SkillGroup('SPEED'), SkillGroup('SPECIAL')]
    case SKAVEN_ESHIN_SORCERER;
    #[Warband('SKAVEN'), Hero]
    #[HireFee(40)]
    #[MaxCount(2)]
    #[StartExp(8)]
    #[Characteristics(6, 4, 3, 4, 3, 1, 5, 1, 6), MaxCharacteristics(6, 6, 6, 4, 4, 3, 7, 4, 7)]
    #[EquipmentList('SKAVEN_HEROES_EQUIPMENT_LIST')]
    #[SkillGroup('COMBAT'), SkillGroup('SHOOTING'), SkillGroup('STRENGTH'), SkillGroup('SPEED'), SkillGroup('SPECIAL')]
    case SKAVEN_BLACK_SKAVEN;
    #[Warband('SKAVEN'), Hero]
    #[HireFee(20)]
    #[MaxCount(2)]
    #[Characteristics(6, 2, 3, 3, 3, 1, 4, 1, 4), MaxCharacteristics(6, 6, 6, 4, 4, 3, 7, 4, 7)]
    #[EquipmentList('SKAVEN_HENCHMEN_EQUIPMENT_LIST')]
    #[SkillGroup('COMBAT'), SkillGroup('SHOOTING'), SkillGroup('SPECIAL')]
    case SKAVEN_NIGHT_RUNNER;
    #[Warband('SKAVEN'), Henchman]
    #[HireFee(20)]
    #[Characteristics(5, 3, 3, 3, 3, 1, 4, 1, 5), MaxCharacteristics(6, 4, 4, 4, 4, 2, 5, 2, 6)]
    #[EquipmentList('SKAVEN_HENCHMEN_EQUIPMENT_LIST')]
    case SKAVEN_VERMINKIN;
    #[Warband('SKAVEN'), Henchman]
    #[HireFee(15)]
    #[Characteristics(6, 2, 0, 3, 3, 1, 4, 1, 4)]
    #[EquipmentList('EMPTY')]
    #[Rule('ANIMAL')]
    case SKAVEN_GIANT_RAT;
    #[Warband('SKAVEN'), Henchman, Rating(20)]
    #[HireFee(210)]
    #[MaxCount(1)]
    #[Characteristics(6, 3, 3, 5, 5, 3, 4, 3, 4)]
    #[EquipmentList('EMPTY')]
    #[Rule('CAUSE_FEAR'), Rule('STUPIDITY'), Rule('ANIMAL'), Rule('LARGE_TARGET')]
    case SKAVEN_RAT_OGRE;

    #[Warband('HIRED_SWORDS'), HiredSword, Rating(22)]
    #[ExceptWarband('SKAVEN'), ExceptWarband('UNDEAD')]
    #[HireFee(30), UpkeepFee(15)]
    #[Characteristics(4, 4, 3, 4, 4, 1, 4, 2, 7), MaxCharacteristics(4, 6, 6, 4, 4, 3, 6, 4, 9)]
    #[MaxCount(1)]
    #[Equipment('MORNING_STAR'), Equipment('SPIKED_GAUNTLET'), Equipment('HELMET')]
    #[SkillGroup('COMBAT'), SkillGroup('SPEED'), SkillGroup('STRENGTH')]
    case PIT_FIGHTER;
    #[Warband('HIRED_SWORDS'), HiredSword, Rating(25)]
    #[ExceptWarband('SKAVEN')]
    #[HireFee(80), UpkeepFee(30)]
    #[Characteristics(6, 3, 2, 4, 4, 3, 3, 2, 7), MaxCharacteristics(6, 6, 5, 5, 5, 5, 6, 5, 9)]
    #[MaxCount(1)]
    #[Equipment('DOUBLE_HANDED_AXE'), Equipment('LIGHT_ARMOUR')]
    #[Rule('CAUSE_FEAR'), Rule('LARGE_TARGET')]
    #[SkillGroup('COMBAT'), SkillGroup('STRENGTH')]
    case OGRE_BODYGUARD;
    #[Warband('HIRED_SWORDS'), HiredSword, Rating(5)]
    #[ExceptWarband('SKAVEN')]
    #[HireFee(15), UpkeepFee(5)]
    #[Characteristics(4, 2, 4, 2, 2, 1, 4, 1, 8), MaxCharacteristics(4, 5, 7, 3, 3, 3, 8, 4, 10)]
    #[MaxCount(1)]
    #[Equipment('BOW'), Equipment('DAGGER'), Equipment('HELMET')]
    #[Rule('COOK')]
    #[SkillGroup('SPEED'), SkillGroup('SHOOTING')]
    case HALFLING_SCOUT;
    #[Warband('HIRED_SWORDS'), HiredSword, Rating(16)]
    #[ExceptWarband('WITCH_HUNTERS'), ExceptWarband('SISTERS_OF_SIGMAR')]
    #[HireFee(30), UpkeepFee(15)]
    #[MaxCount(1)]
    #[Characteristics(4, 2, 2, 3, 3, 1, 4, 1, 8), MaxCharacteristics(4, 3, 3, 4, 4, 2, 5, 2, 9)]
    #[Equipment('STAFF')]
    #[Rule('WIZARD_LESSER_MAGIC')]
    #[SkillGroup('ACADEMIC')]
    case WARLOCK;
    #[Warband('HIRED_SWORDS'), HiredSword, Rating(21)]
    #[AllowedWarband('WITCH_HUNTERS'), AllowedWarband('REIKLAND'), AllowedWarband('MIDDENHEIM'), AllowedWarband('MARIENBURG')]
    #[HireFee(50), UpkeepFee(20)]
    #[MaxCount(1)]
    #[Characteristics(4, 4, 3, 4, 3, 1, 4, 1, 7), MaxCharacteristics(4, 6, 6, 4, 4, 3, 6, 4, 9)]
    #[Equipment('SWORD'), Equipment('SHIELD'), Equipment('HEAVY_ARMOUR')] //  Equipment('LANCE') only with mounts
    #[SkillGroup('COMBAT'), SkillGroup('STRENGTH')]
    case FREELANCER;
    #[Warband('HIRED_SWORDS'), HiredSword, Rating(12)]
    #[AllowedWarband('WITCH_HUNTERS'), AllowedWarband('REIKLAND'), AllowedWarband('MIDDENHEIM'), AllowedWarband('MARIENBURG')]
    #[HireFee(40), UpkeepFee(20)]
    #[MaxCount(1)]
    #[Characteristics(5, 4, 5, 3, 3, 1, 6, 1, 8), MaxCharacteristics(5, 7, 7, 4, 4, 3, 9, 4, 10)]
    #[Equipment('SWORD'), Equipment('ELF_BOW'), Equipment('ELVEN_CLOAK')]
    #[Rule('SEEKER'), Rule('EXCELLENT_SIGHT')]
    #[SkillGroup('SHOOTING'), SkillGroup('SPEED'), SkillGroup('SPECIAL')]
    case ELF_RANGER;
    #[Warband('HIRED_SWORDS'), HiredSword, Rating(12)]
    #[AllowedWarband('WITCH_HUNTERS'), AllowedWarband('REIKLAND'), AllowedWarband('MIDDENHEIM'), AllowedWarband('MARIENBURG')]
    #[HireFee(25), UpkeepFee(10)]
    #[MaxCount(1)]
    #[Characteristics(3, 4, 3, 3, 4, 1, 2, 1, 9), MaxCharacteristics(3, 7, 6, 4, 5, 3, 5, 4, 10)]
    #[Equipment('AXE'), Equipment('AXE')]
    #[Rule('DEATHWISH'), Rule('HARD_TO_KILL'), Rule('HARD_HEAD')]
    #[SkillGroup('COMBAT'), SkillGroup('STRENGTH'), SkillGroup('SPECIAL')]
    case DWARF_TROLL_SLAYER;

    #[Warband('HIRED_SWORDS'), HiredSword, Rating(100)]
    #[ExceptWarband('SKAVEN'), ExceptWarband('UNDEAD'), ExceptWarband('CULT_OF_THE_POSSESSED')]
    #[HireFee(150)]
    #[MaxCount(1)]
    #[Characteristics(5, 8, 4, 4, 3, 2, 7, 3, 8), MaxCharacteristics(5, 8, 7, 4, 4, 3, 9, 4, 10)]
    #[Equipment('ITHILMAR_ARMOUR'), Equipment('ELVEN_CLOAK'), Equipment('SWORD_IENH_KHAIN')]
    #[Rule('STRIKE_TO_INJURE'), Rule('EXPERT_SWORDSMAN'), Rule('STEP_ASIDE')]
    #[Rule('SPRINT'), Rule('LIGHTNING_REFLEXES'), Rule('DODGE'), Rule('MIGHTY_BLOW')]
    #[Rule('INVINCIBLE_SWORDSMAN'), Rule('WANDERER')]
    case AENUR;
    #[Warband('HIRED_SWORDS'), HiredSword, Rating(60)]
    #[ExceptWarband('SKAVEN'), ExceptWarband('UNDEAD'), ExceptWarband('CULT_OF_THE_POSSESSED')]
    #[HireFee(70), UpkeepFee(30)]
    #[MaxCount(1)]
    #[Characteristics(4, 3, 6, 4, 3, 2, 6, 1, 7), MaxCharacteristics(4, 6, 6, 4, 4, 3, 6, 4, 9)]
    #[Equipment('DAGGER'), Equipment('DAGGER'), Equipment('THROWING_KNIVES')]
    #[Rule('DODGE'), Rule('SCALE_SHEER_SURFACES'), Rule('QUICK_SHOT'), Rule('EAGLE_EYES')]
    #[Rule('KNIFE_FIGHTER'), Rule('KNIFE_FIGHTER_EXTRAORDINAIRE')]
    case JOHANN;
    #[Warband('HIRED_SWORDS'), HiredSword, Rating(70)]
    #[AllowedWarband('SKAVEN')]
    #[HireFee(80), UpkeepFee(35)]
    #[MaxCount(1)]
    #[Characteristics(5, 5, 4, 4, 4, 2, 5, 4, 8), MaxCharacteristics(6, 6, 6, 4, 4, 3, 7, 4, 7)]
    #[Equipment('ESHIN_FIGHTING_CLAWS')]
    #[Rule('UNFEELING'), Rule('NO_PAIN'), Rule('UNBLINKING_EYE'), Rule('METALLIC_BODY')]
    case VESKIT;

    public function getWarband(): ?\Mordheim\Warband
    {
        $ref = new \ReflectionClassConstant(self::class, $this->name);
        $classAttributes = $ref->getAttributes(Warband::class);

        if (count($classAttributes) === 0)
            throw new InvalidAttributesException('Invalid attributes for: ' . $this->name);

        return $classAttributes[0]->newInstance()->getValue();
    }

    public function getAllowedWarbands(): array
    {
        $ref = new \ReflectionClassConstant(self::class, $this->name);
        $classAttributes = $ref->getAttributes(AllowedWarband::class);

        if (count($classAttributes))
            return array_map(
                fn($attribute) => $attribute->newInstance()->getValue(),
                $classAttributes
            );

        $classAttributes = $ref->getAttributes(AllowedWarband::class);
        if (count($classAttributes))
            return array_values(
                array_diff(
                    \Mordheim\Warband::cases(),
                    array_map(
                        fn($attribute) => $attribute->newInstance()->getValue(),
                        $classAttributes
                    )
                )
            );

        return \Mordheim\Warband::cases();
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
     * @return Equipment[]
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

    public function getMaxCharacteristics(): ?\Mordheim\Characteristics
    {
        $ref = new \ReflectionClassConstant(self::class, $this->name);
        $classAttributes = $ref->getAttributes(MaxCharacteristics::class);

        if (count($classAttributes) === 0)
            return null;

        return $classAttributes[0]->newInstance()->getValue();
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

    public function getEquipmentList(): \Mordheim\EquipmentList
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

    /**
     * @param SpecialRule $specialRule
     * @return bool
     */
    public function hasSpecialRule(SpecialRule $specialRule): bool
    {
        return in_array($specialRule, $this->getSpecialRules());
    }

    public function isWizard(): bool
    {
        if ($this->hasSpecialRule(SpecialRule::WIZARD_NECROMANCY))
            return true;
        if ($this->hasSpecialRule(SpecialRule::WIZARD_CHAOS_RITUALS))
            return true;
        if ($this->hasSpecialRule(SpecialRule::WIZARD_MAGIC_OF_THE_HORNED_RAT))
            return true;
        if ($this->hasSpecialRule(SpecialRule::PRAYERS_OF_SIGMAR))
            return true;
        if ($this->hasSpecialRule(SpecialRule::WIZARD_LESSER_MAGIC))
            return true;

        return false;
    }

    public function getUnlearnedSpells(FighterAdvancement $advancement): array
    {
        if (!$this->isWizard())
            return [];

        return [];
    }
}