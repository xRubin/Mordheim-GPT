<?php

namespace Mordheim;

class EquipmentManager
{
    /** @var Weapon[] */
    private array $weapons = [];
    /** @var Armor[] */
    private array $armors = [];

    public function __construct(array $weapons = [], array $armors = [])
    {
        foreach ($weapons as $weapon) {
            $this->addWeapon($weapon);
        }
        foreach ($armors as $armor) {
            $this->addArmor($armor);
        }
    }

    public function addWeapon(Weapon $weapon): bool
    {
        $freeHands = 2;
        if ($weapon->hasRule(\Mordheim\SpecialRule::DOUBLE_HANDED)) {
            $freeHands -= 2;
        } else {
            $freeHands -= 1;
        }
        if ($freeHands < 0) return false;
        $this->weapons[] = $weapon;
        return true;
    }

    public function addArmor(Armor $armor): bool
    {
        $this->armors[] = $armor;
        return true;
    }

    /**
     * Проверяет, есть ли у бойца шлем с защитой от стана (Avoid stun)
     */
    public function hasHelmetProtection(): bool
    {
        foreach ($this->armors as $eq) {
            if ($eq->hasRule(\Mordheim\SpecialRule::AVOID_STUN)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Итоговые параметры оружия для атаки (главное оружие)
     */
    public function getMainWeapon(): ?Weapon
    {
        return $this->weapons[0] ?? null;
    }

    /**
     * Все оружие (например, для dual wield)
     */
    public function getWeapons(): array
    {
        return $this->weapons;
    }

    public function getMovementPenalty(): int
    {
        // Heavy Armor и Shield вместе дают -1 к движению, иначе штрафа нет
        $hasHeavyArmor = false;
        $hasShield = false;
        foreach ($this->armors as $eq) {
            if ($eq->hasRule(\Mordheim\SpecialRule::MOVEMENT)) {
                $hasHeavyArmor = true;
            }
            if ($eq->hasRule(\Mordheim\SpecialRule::SHIELD_PARRY)) {
                $hasShield = true;
            }
        }
        return ($hasHeavyArmor && $hasShield) ? -1 : 0;
    }

    public function hasShieldParry(): bool
    {
        foreach ($this->armors as $eq) {
            if ($eq->hasRule(\Mordheim\SpecialRule::SHIELD_PARRY)) {
                return true;
            }
        }
        return false;
    }

    public function canBeParried(Weapon $attackerWeapon, ?Weapon $defenderWeapon, int $hitRoll): bool
    {
        $attackerHasFlail = $attackerWeapon->hasRule(\Mordheim\SpecialRule::FLAIL);
        $defenderCanParry = $defenderWeapon && $defenderWeapon->hasRule(\Mordheim\SpecialRule::PARRY);
        return $defenderCanParry && $hitRoll >= 4 && !$attackerHasFlail;
    }

    public function getResilientModifier(Fighter $target): int
    {
        return (int)$target->hasSkill('Resilient');
    }

    public function getArmorSaveModifier(Weapon $weapon): int
    {
        $mod = 0;
        if ($weapon->hasRule(\Mordheim\SpecialRule::DOUBLE_HANDED)) {
            $mod += 2; // Double-Handed ухудшает сейв на 2
        }
        if ($weapon->hasRule(\Mordheim\SpecialRule::ARMOR_PIERCING)) {
            $mod += 1; // Armor-Piercing ухудшает сейв на 1
        }
        return $mod;
    }

    public function getInjuryModifier(Weapon $weapon): int
    {
        return $weapon->hasRule(\Mordheim\SpecialRule::AXE) ? -1 : 0;
    }

    /**
     * Итоговый сейв с учётом всей брони
     */
    public function getArmorSave(?Weapon $attackerWeapon, bool $isCritical = false): int
    {
        $baseSave = 0;
        $bonus = 0;
        $specialRules = [];
        foreach ($this->armors as $eq) {
            if (isset($eq->baseSave) && $eq->baseSave > 0) {
                $baseSave = max($baseSave, $eq->baseSave);
                $bonus += $eq->saveModifier;
                $specialRules = array_merge($specialRules, $eq->specialRules ?? []);
            }
        }
        $ap = $attackerWeapon ? $attackerWeapon->armorPiercing : 0;
        $save = $baseSave - $bonus - $ap;
        // Спецправила брони
        if ($isCritical && in_array(\Mordheim\SpecialRule::IGNORE_CRIT, $specialRules)) {
            // Броня не игнорируется при крите
            // (можно реализовать спецэффекты, если нужно)
        }
        if (in_array(\Mordheim\SpecialRule::HEAVY_ARMOR_PENALTY, $specialRules)) {
            $save += 1; // штраф к сейву
        }
        if (in_array(\Mordheim\SpecialRule::LIGHT_ARMOR_BONUS, $specialRules)) {
            $save -= 1; // бонус к сейву
        }
        if ($save < 2) $save = 2;
        if ($save > 6) $save = 6;
        return $save;
    }

    /**
     * Суммарные модификаторы от экипировки (пример)
     */
    public function getTotalModifiers(): array
    {
        $mods = ['strength' => 0, 'toHit' => 0, 'armorPiercing' => 0];
        foreach ($this->weapons as $w) {
            $mods['strength'] += $w->strength;
            $mods['toHit'] += $w->toHitModifier;
            $mods['armorPiercing'] += $w->armorPiercing;
        }
        return $mods;
    }

    /**
     * Удаляет оружие по имени
     */
    public function removeWeapon(string $weaponName): bool
    {
        foreach ($this->weapons as $i => $w) {
            if ($w->name === $weaponName) {
                array_splice($this->weapons, $i, 1);
                return true;
            }
        }
        return false;
    }

    /**
     * Удаляет броню по имени
     */
    public function removeArmor(string $armorName): bool
    {
        foreach ($this->armors as $i => $a) {
            if ($a->name === $armorName) {
                array_splice($this->armors, $i, 1);
                return true;
            }
        }
        return false;
    }

    /**
     * Меняет экипированное оружие на другое (если оно есть в списке)
     */
    public function swapWeapon(string $from, Weapon $to): bool
    {
        foreach ($this->weapons as $i => $w) {
            if ($w->name === $from) {
                $copy = $this->weapons;
                $copy[$i] = $to;
                $test = new self($copy, $this->armors);
                if (count($test->weapons) === count($this->weapons)) {
                    $this->weapons = $test->weapons;
                    return true;
                }
                return false;
            }
        }
        return false;
    }

    /**
     * Меняет экипированную броню на другую (по типу)
     */
    public function swapArmor(Armor $from, Armor $to): bool
    {
        foreach ($this->armors as $i => $a) {
            if ($a->name === $from->name) {
                $copy = $this->armors;
                $copy[$i] = $to;
                $test = new self($this->weapons, $copy);
                if (count($test->armors) === count($this->armors)) {
                    $this->armors = $test->armors;
                    return true;
                }
                return false;
            }
        }
        return false;
    }

    /**
     * Полностью заменить оружие (контроль правил)
     */
    public function setWeapons(array $weapons): bool
    {
        $test = new self($weapons, $this->armors);
        if (count($test->weapons) === count($weapons)) {
            $this->weapons = $test->weapons;
            return true;
        }
        return false;
    }

    /**
     * Полностью заменить броню (контроль правил)
     */
    public function setArmors(array $armors): bool
    {
        $test = new self($this->weapons, $armors);
        if (count($test->armors) === count($armors)) {
            $this->armors = $test->armors;
            return true;
        }
        return false;
    }
}
