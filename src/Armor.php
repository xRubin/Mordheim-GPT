<?php

namespace Mordheim;
class Armor
{
    /**
     * @var SpecialRule[]
     */
    public array $specialRules = [];
    public string $name;
    public int $baseSave; // Базовый сейв (например, 6 для Light Armor)
    public int $saveModifier; // Модификатор к сейву (например, -1 к пробитию)
    public string $description;

    public function __construct(string $name, int $baseSave, int $saveModifier, string $description, array $specialRules = [])
    {
        $this->name = $name;
        $this->baseSave = $baseSave;
        $this->saveModifier = $saveModifier;
        $this->description = $description;
        $this->specialRules = $specialRules;
    }

    public function hasRule(SpecialRule $specialRule): bool
    {
        return in_array($specialRule, $this->specialRules, true);
    }

    /**
     * Применяет правила Mordheim для брони
     * Возвращает итоговый сейв с учетом всех модификаторов
     * $ap — armor piercing оружия
     */
    public function getFinalSave(int $ap = 0, bool $isCritical = false, bool $inMelee = false): int
    {
        $save = $this->baseSave;
        $save -= $ap;
        $save += $this->saveModifier;
        // Shield: +1 к сейву в рукопашной
        if ($inMelee && $this->hasRule(\Mordheim\SpecialRule::SHIELD_PARRY)) {
            $save -= 1;
        }
        // Спецправила брони
        if ($isCritical && $this->hasRule(\Mordheim\SpecialRule::IGNORE_CRIT)) {
            // Броня не игнорируется при крите
            // (можно реализовать спецэффекты, если нужно)
        }
        if ($this->hasRule(\Mordheim\SpecialRule::HEAVY_ARMOR_PENALTY)) {
            $save += 1; // штраф к сейву (пример)
        }
        // Helmet: спец. эффект только при ранении по таблице (см. Fighter)
        // Ограничение итогового сейва для всех типов брони
        if ($save < 2) $save = 2;
        if ($save > 6) $save = 6;
        return $save;
    }
}
