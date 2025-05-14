<?php

namespace Mordheim;

use Mordheim\Traits\EnumTryFromNameTrait;

enum SpecialRule implements SpecialRuleInterface
{
    use EnumTryFromNameTrait;

    case PLUS_1_ENEMY_ARMOR_SAVE;
    case CONCUSSION;
    case CUTTING_EDGE;
    case PARRY;
    case HEAVY;
    case TWO_HANDED;
    case DIFFICULT_TO_USE;
    case STRIKE_FIRST;
    case UNWIELDY;
    case CAVALRY_BONUS;
    case STRIKE_LAST;
    case MINUS_1_SAVE_MODIFIER;
    case MOVE_OR_FIRE;
    case FIRE_TWICE_AT_HALF_RANGE;
    case THROWN_WEAPON;
    case FIRE_TWICE;
    case SHOOT_IN_HAND_TO_HAND_COMBAT;
    case PREPARE_SHOT;
    case SAVE_MODIFIER;
    case HAND_TO_HAND;
    case ACCURACY;
    case PICK_TARGET;
    case SAVE_2;
    case SAVE_3;
    case SAVE_4;
    case SAVE_5;
    case SAVE_6;
    case ITHILMAR;
    case GROMRIL;
    case HOLY_WEAPON;
    case CANNOT_BE_PARRIED;
    case WHIPCRACK;
    case POISON;
    case STEALTHY;
    case PAIR;
    case CLIMB;
    case CUMBERSOME;
    case VENOMOUS;
    case AVOID_STUN;
    case MOVEMENT;

    case LEADER;
    case EXPERT_SWORDSMAN;
    case WIZARD_CHAOS_RITUALS;
    case MUTATIONS;
    case CAUSE_FEAR;
    case CRAZED;
    case BURN_THE_WITCH;
    case FANATICAL;
    case ANIMAL;
    case PRAYERS_OF_SIGMAR;
    case BLESSED_SIGHT;
    case WIZARD_NECROMANCY;
    case CHARGE;
    case MAY_NOT_RUN;
    case IMMUNE_TO_PSYCHOLOGY;
    case IMMUNE_TO_POISON;
    case UNLIVING;
    case NO_PAIN;
    case NO_BRAIN;
    case PERFECT_KILLER;
    case WIZARD_MAGIC_OF_THE_HORNED_RAT;
    case LARGE_TARGET;
    case STEP_ASIDE;
    case RESILIENT;
    case DODGE;
    case QUICK_SHOT;
    case SPRINT;
    case STRIKE_TO_INJURE;
    case COMBAT_MASTER;
    case WEAPONS_TRAINING;
    case WEB_OF_STEEL;
    case PISTOLIER;
    case EAGLE_EYES;
    case WEAPONS_EXPERT;
    case NIMBLE;
    case TRICK_SHOOTER;
    case HUNTER;
    case KNIFE_FIGHTER;
    case KNIFE_FIGHTER_EXTRAORDINAIRE;
    case BATTLE_TONGUE;
    case SORCERY;
    case STREETWISE;
    case HAGGLE;
    case ARCANE_LORE;
    case WYRDSTONE_HUNTER;
    case WARRIOR_WIZARD;
    case MIGHTY_BLOW;
    case PIT_FIGHTER;
    case STRONGMAN;
    case UNSTOPPABLE_CHARGE;
    case LEAP;
    case ACROBAT;
    case LIGHTNING_REFLEXES;
    case JUMP_UP;
    case SCALE_SHEER_SURFACES;
    case STUPIDITY;
    case INVINCIBLE_SWORDSMAN;
    case WANDERER;
    case CRITICAL_HIT_ON_5;
    case UNFEELING;
    case UNBLINKING_EYE;
    case METALLIC_BODY;
    case WIZARD_LESSER_MAGIC;
    case FEARSOME;
    case IMMUNE_TO_SPELLS; // Shield of Faith
    case DOUBLE_DAMAGE; // The Hammer of Sigmar
    case PLUS_2_STRENGTH; // The Hammer of Sigmar
    case REROLL_ANY_FAILED; // Luck of Shemtek

    case MUTATION_DAEMON_SOUL;
    case MUTATION_GREAT_CLAW;
    case MUTATION_CLOVEN_HOOFS;
    case MUTATION_TENTACLE;
    case MUTATION_BLACKBLOOD;
    case MUTATION_SPINES;
    case MUTATION_SCORPION_TAIL;
    case MUTATION_EXTRA_ARM;
}