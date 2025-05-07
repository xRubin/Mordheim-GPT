<?php

namespace Mordheim\Data;

use Mordheim\Traits\EnumTryFromNameTrait;

enum SpecialRule: int
{
    use EnumTryFromNameTrait;
    case PLUS_1_ENEMY_ARMOR_SAVE = 1;
    case CONCUSSION = 2;
    case CUTTING_EDGE = 3;
    case PARRY = 4;
    case HEAVY = 5;
    case TWO_HANDED = 6;
    case DIFFICULT_TO_USE = 7;
    case STRIKE_FIRST = 8;
    case UNWIELDY = 9;
    case CAVALRY_BONUS = 10;
    case STRIKE_LAST = 11;
    case MINUS_1_SAVE_MODIFIER = 12;
    case MOVE_OR_FIRE = 13;
    case FIRE_TWICE_AT_HALF_RANGE = 14;
    case THROWN_WEAPON = 15;
    case FIRE_TWICE = 16;
    case SHOOT_IN_HAND_TO_HAND_COMBAT = 17;
    case PREPARE_SHOT = 18;
    case SAVE_MODIFIER = 19;
    case HAND_TO_HAND = 20;
    case ACCURACY = 21;
    case PICK_TARGET = 22;

    case ITHILMAR = 1900;
    case GROMRIL = 2000;
}
