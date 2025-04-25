<?php
namespace Mordheim;

/**
 * Перечисление спецправил для оружия и брони.
 */
enum SpecialRule: string
{
    case PARRY = 'Parry';
    case CONCUSSION = 'Concussion';
    case CRITICAL = 'Critical';
    case ARMOR_PIERCING = 'ArmorPiercing';
    case DOUBLE_HANDED = 'DoubleHanded';
    case FLAIL = 'Flail';
    case TWO_HANDED = 'TwoHanded';
    case WHIP = 'Whip';
    case CLUB = 'Club';
    case IGNORE_ARMOR_SAVE = 'IgnoreArmorSave';
    case AXE = 'Axe';
    case SWORD = 'Sword';
    case IGNORE_CRIT = 'IgnoreCrit'; // броня не игнорируется при критах
    case AVOID_STUN = 'AvoidStun'; //  A model has a special 4+ save on a D6 against being stunned. If the save is made, treat the stunned result as knocked down instead. This save is not modified by the opponent’s Strength.
    case SHIELD_PARRY = 'ShieldParry'; // щит даёт возможность парировать
    case HEAVY_ARMOR_PENALTY = 'HeavyArmorPenalty'; // штраф к сейву или инициативе
    case HELMET_PROTECTION = 'HelmetProtection'; // шлем даёт защиту от стана/оглушения
    case LIGHT_ARMOR_BONUS = 'LightArmorBonus'; // облегчённая броня даёт бонус к инициативе/сейву
    case SAVE = 'Save'; // тяжелая броня даёт базовый сейв 5+
    case MOVEMENT = 'Movement'; // Heavy Armor + Shield: -1 к движению
    case MOVE_OR_FIRE = 'MoveOrFire'; // You may not move and fire in the same turn
    case PAIR = 'Pair'; // Всегда используются парой, дают +1 атаку
    case CLIMB = 'Climb'; // Даёт бонус +1 к тесту Initiative на лазание
    case CUMBERSOME = 'Cumbersome'; // Громоздкое: нельзя использовать с другим оружием в рукопашной
}

