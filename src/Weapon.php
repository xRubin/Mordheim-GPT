<?php
namespace Mordheim;

use Mordheim\SpecialRule;

class Weapon
{
    public string $name;
    public string $description;
    public int $strength; // Сила оружия
    public int $armorPiercing; // Пробитие брони (AP)
    public int $toHitModifier; // Модификатор попадания
    public int $range; // Дальность (для стрелкового)
    public string $damageType; // Melee/Ranged
    /**
     * @var SpecialRule[]
     */
    public array $specialRules = [];

    /**
     * @param SpecialRule[] $specialRules
     */
    public function __construct(string $name, string $description, int $strength = 0, int $armorPiercing = 0, int $toHitModifier = 0, int $range = 0, string $damageType = 'Melee', array $specialRules = [])
    {
        $this->name = $name;
        $this->description = $description;
        $this->strength = $strength;
        $this->armorPiercing = $armorPiercing;
        $this->toHitModifier = $toHitModifier;
        $this->range = $range;
        $this->damageType = $damageType;
        $this->specialRules = $specialRules;
    }

    public function hasRule(SpecialRule $specialRule): bool
    {
        return in_array($specialRule, $this->specialRules, true);
    }
}

