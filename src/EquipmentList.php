<?php

namespace Mordheim;

use Mordheim\Attributes\Equipment;

enum EquipmentList
{
    use EnumTryFromNameTrait;

    case EMPTY;
    #[Equipment('DAGGER')]
    #[Equipment('CLUB')]
    #[Equipment('MACE')]
    #[Equipment('HAMMER')]
    #[Equipment('AXE')]
    #[Equipment('SWORD')]
    #[Equipment('MORNING_STAR')]
    #[Equipment('DOUBLE_HANDED_AXE')]
    #[Equipment('DOUBLE_HANDED_SWORD')]
    #[Equipment('SPEAR')]
    #[Equipment('HALBERD')]
    #[Equipment('CROSSBOW')]
    #[Equipment('PISTOL')]
    #[Equipment('DUELLING_PISTOL')]
    #[Equipment('BOW')]
    #[Equipment('LIGHT_ARMOUR')]
    #[Equipment('HEAVY_ARMOUR')]
    #[Equipment('SHIELD')]
    #[Equipment('BUCKLER')]
    #[Equipment('HELMET')]
    case MERCENARY_EQUIPMENT_LIST;
    #[Equipment('DAGGER')]
    #[Equipment('CLUB')]
    #[Equipment('MACE')]
    #[Equipment('HAMMER')]
    #[Equipment('AXE')]
    #[Equipment('SWORD')]
    #[Equipment('CROSSBOW')]
    #[Equipment('PISTOL')]
    #[Equipment('BOW')]
    #[Equipment('LONG_BOW')]
    #[Equipment('BLUNDERBUSS')]
    #[Equipment('HANDGUN')]
    #[Equipment('HUNTING_RIFLE')]
    #[Equipment('LIGHT_ARMOUR')]
    #[Equipment('SHIELD')]
    #[Equipment('HELMET')]
    case MARKSMAN_EQUIPMENT_LIST;

    #[Equipment('DAGGER')]
    #[Equipment('MACE')]
    #[Equipment('HAMMER')]
    #[Equipment('AXE')]
    #[Equipment('SWORD')]
    #[Equipment('DOUBLE_HANDED_AXE')]
    #[Equipment('DOUBLE_HANDED_HAMMER')]
    #[Equipment('DOUBLE_HANDED_SWORD')]
    #[Equipment('SPEAR')]
    #[Equipment('BOW')]
    #[Equipment('SHORT_BOW')]
    #[Equipment('LIGHT_ARMOUR')]
    #[Equipment('HEAVY_ARMOUR')]
    #[Equipment('SHIELD')]
    #[Equipment('HELMET')]
    case POSSESSED_EQUIPMENT_LIST;

    #[Equipment('DAGGER')]
    #[Equipment('MACE')]
    #[Equipment('HAMMER')]
    #[Equipment('AXE')]
    #[Equipment('SWORD')]
    #[Equipment('DOUBLE_HANDED_AXE')]
    #[Equipment('DOUBLE_HANDED_HAMMER')]
    #[Equipment('DOUBLE_HANDED_SWORD')]
    #[Equipment('FLAIL')]
    #[Equipment('LIGHT_ARMOUR')]
    #[Equipment('HEAVY_ARMOUR')]
    #[Equipment('SHIELD')]
    #[Equipment('HELMET')]
    case DARKSOUL_EQUIPMENT_LIST;

    #[Equipment('DAGGER')]
    #[Equipment('MACE')]
    #[Equipment('HAMMER')]
    #[Equipment('AXE')]
    #[Equipment('SWORD')]
    #[Equipment('DOUBLE_HANDED_AXE')]
    #[Equipment('DOUBLE_HANDED_HAMMER')]
    #[Equipment('DOUBLE_HANDED_SWORD')]
    #[Equipment('CROSSBOW')]
    #[Equipment('PISTOL')]
    #[Equipment('CROSSBOW_PISTOL')]
    #[Equipment('LIGHT_ARMOUR')]
    #[Equipment('HEAVY_ARMOUR')]
    #[Equipment('SHIELD')]
    #[Equipment('BUCKLER')]
    #[Equipment('HELMET')]
    case WITCH_HUNTER_EQUIPMENT_LIST;
    #[Equipment('FLAIL')]
    #[Equipment('MORNING_STAR')]
    #[Equipment('DOUBLE_HANDED_AXE')]
    #[Equipment('DOUBLE_HANDED_HAMMER')]
    #[Equipment('DOUBLE_HANDED_SWORD')]
    case FLAGELLANT_EQUIPMENT_LIST;
    #[Equipment('DAGGER')]
    #[Equipment('MACE')]
    #[Equipment('HAMMER')]
    #[Equipment('AXE')]
    #[Equipment('SWORD')]
    #[Equipment('DOUBLE_HANDED_AXE')]
    #[Equipment('DOUBLE_HANDED_HAMMER')]
    #[Equipment('DOUBLE_HANDED_SWORD')]
    #[Equipment('SPEAR')]
    #[Equipment('BOW')]
    #[Equipment('SHORT_BOW')]
    #[Equipment('LIGHT_ARMOUR')]
    #[Equipment('SHIELD')]
    #[Equipment('HELMET')]
    case ZEALOT_EQUIPMENT_LIST;

    #[Equipment('DAGGER')]
    #[Equipment('MACE')]
    #[Equipment('HAMMER')]
    #[Equipment('SIGMARITE_WARHAMMER')]
    #[Equipment('FLAIL')]
    #[Equipment('STEEL_WHIP')]
    #[Equipment('DOUBLE_HANDED_AXE')]
    #[Equipment('DOUBLE_HANDED_HAMMER')]
    #[Equipment('DOUBLE_HANDED_SWORD')]
    #[Equipment('SLING')]
    #[Equipment('LIGHT_ARMOUR')]
    #[Equipment('HEAVY_ARMOUR')]
    #[Equipment('SHIELD')]
    #[Equipment('BUCKLER')]
    #[Equipment('HELMET')]
    case SISTERS_OF_SIGMAR_EQUIPMENT_LIST;

    #[Equipment('DAGGER')]
    #[Equipment('MACE')]
    #[Equipment('HAMMER')]
    #[Equipment('SIGMARITE_WARHAMMER')]
    #[Equipment('FLAIL')]
    #[Equipment('STEEL_WHIP')]
    #[Equipment('DOUBLE_HANDED_AXE')]
    #[Equipment('DOUBLE_HANDED_HAMMER')]
    #[Equipment('DOUBLE_HANDED_SWORD')]
    #[Equipment('SLING')]
    #[Equipment('LIGHT_ARMOUR')]
    #[Equipment('HEAVY_ARMOUR')]
    #[Equipment('SHIELD')]
    #[Equipment('BUCKLER')]
    #[Equipment('HELMET')]
    #[Equipment('HOLY_TOME')]
    #[Equipment('BLESSED_WATER')]
    #[Equipment('HOLY_RELIC')]
    case SISTERS_OF_SIGMAR_HERO_EQUIPMENT_LIST;

    #[Equipment('DAGGER')]
    #[Equipment('MACE')]
    #[Equipment('HAMMER')]
    #[Equipment('AXE')]
    #[Equipment('SWORD')]
    #[Equipment('DOUBLE_HANDED_AXE')]
    #[Equipment('DOUBLE_HANDED_HAMMER')]
    #[Equipment('DOUBLE_HANDED_SWORD')]
    #[Equipment('SPEAR')]
    #[Equipment('HALBERD')]
    #[Equipment('BOW')]
    #[Equipment('SHORT_BOW')]
    #[Equipment('LIGHT_ARMOUR')]
    #[Equipment('HEAVY_ARMOUR')]
    #[Equipment('SHIELD')]
    #[Equipment('HELMET')]
    case UNDEAD_EQUIPMENT_LIST;

    #[Equipment('DAGGER')]
    #[Equipment('SWORD')]
    #[Equipment('FLAIL')]
    #[Equipment('SPEAR')]
    #[Equipment('HALBERD')]
    #[Equipment('WEEPING_BLADES')]
    #[Equipment('FIGHTING_CLAWS')]
    #[Equipment('SLING')]
    #[Equipment('THROWING_STARS')]
    #[Equipment('BLOWPIPE')]
    #[Equipment('WARPLOCK_PISTOL')]
    #[Equipment('LIGHT_ARMOUR')]
    #[Equipment('BUCKLER')]
    #[Equipment('HELMET')]
    case SKAVEN_HEROES_EQUIPMENT_LIST;

    #[Equipment('DAGGER')]
    #[Equipment('CLUB')]
    #[Equipment('SWORD')]
    #[Equipment('SPEAR')]
    #[Equipment('SLING')]
    #[Equipment('LIGHT_ARMOUR')]
    #[Equipment('SHIELD')]
    #[Equipment('HELMET')]
    case SKAVEN_HENCHMEN_EQUIPMENT_LIST;

    public function getItems(): array
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
}