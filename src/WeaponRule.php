<?php
namespace Mordheim;

enum WeaponRule: string
{
    case PARRY = 'Parry';
    case CONCUSSION = 'Concussion';
    case CRITICAL = 'Critical';
    case ARMOR_PIERCING = 'ArmorPiercing';
    case DOUBLE_HANDED = 'DoubleHanded';
    case FLAIL = 'Flail';
    case WHIP = 'Whip';
    case UNBALANCED = 'Unbalanced';
    case CLUB = 'Club';
    case AXE = 'Axe';
    case SWORD = 'Sword';
    // Добавьте другие по мере необходимости
}
