<?php

namespace Mordheim;

use Mordheim\Data\Equipment;
use Mordheim\Exceptions\EquipmentManagerAddItemException;

class EquipmentManager
{
    /** @var EquipmentInterface[] */
    private array $items = [];

    public function __construct(array $items = [])
    {
        foreach ($items as $item) {
            $this->addItem($item);
        }
    }

    public function addItem(EquipmentInterface $item): static
    {
        $slotMelee = 2;
        $slotRanged = 1;
        $slotArmour = 1;
        $slotHelmet = 1;
        foreach (array_merge($this->items, [$item]) as $equipment) {
            if ($equipment->getSlot() === Slot::RANGED) {
                $slotRanged -= 1;
            } elseif ($equipment->getSlot() === Slot::MELEE) {
                if ($equipment->hasSpecialRule(SpecialRule::TWO_HANDED)) {
                    $slotMelee -= 2;
                } else {
                    $slotMelee -= 1;
                }
            } elseif ($equipment->getSlot() === Slot::ARMOUR) {
                $slotArmour -= 1;
            } elseif ($equipment->getSlot() === Slot::HELMET) {
                $slotHelmet -= 1;
            }
        }
        if ($slotRanged < 0 || $slotMelee < 0 || $slotArmour < 0 || $slotHelmet < 0)
            throw new EquipmentManagerAddItemException();
        $this->items[] = $item;
        $names = array_map(fn($equipment) => $equipment->getName(), $this->items);
        \Mordheim\BattleLogger::add("[DEBUG] Оружие после добавления: " . implode(',', $names));
        return $this;
    }

    public function hasItem(EquipmentInterface $item): bool
    {
        return in_array($item, $this->items);
    }

    /**
     * Итоговые параметры оружия для атаки (главное оружие)
     */
    public function getMainWeapon(Slot $slot, $default = null): ?EquipmentInterface
    {
        $items = $this->getItemsBySlot($slot);
        return reset($items) ?: $default;
    }

    /**
     * @param Slot $slot
     * @return EquipmentInterface[]
     */
    public function getItemsBySlot(Slot $slot): array
    {
        return array_values(
            array_filter($this->items, fn(EquipmentInterface $item) => $item->getSlot() === $slot)
        );
    }

    /**
     * Возвращает количество одноручного оружия ближнего боя
     */
    public function countOneHandedMeleeWeapons(): int
    {
        $count = 0;
        foreach ($this->getItemsBySlot(Slot::MELEE) as $weapon) {
            if (
                !$weapon->hasSpecialRule(SpecialRule::TWO_HANDED) && !$weapon->hasSpecialRule(SpecialRule::PAIR)
            ) {
                $count++;
            }
        }
        return $count;
    }

    public function getMovementPenalty(): int
    {
        // Heavy Armour и Shield вместе дают -1 к движению, иначе штрафа нет
        return (in_array(Equipment::HEAVY_ARMOUR, $this->items) && in_array(Equipment::SHIELD, $this->items)) ? -1 : 0;
    }

    public function canBeParried(EquipmentInterface $attackerWeapon, EquipmentInterface $defenderWeapon, int $hitRoll): bool
    {
        return $defenderWeapon->hasSpecialRule(SpecialRule::PARRY) && $hitRoll >= 4 && !$attackerWeapon->hasSpecialRule(SpecialRule::CANNOT_BE_PARRIED);
    }

    public function getArmourSaveModifier(?EquipmentInterface $weapon = null): int
    {
        $mod = 0;
        if ($weapon && $weapon->hasSpecialRule(SpecialRule::TWO_HANDED)) {
            $mod += 2; // Two-Handed ухудшает сейв на 2
        }
        if ($weapon && $weapon->hasSpecialRule(SpecialRule::MINUS_1_SAVE_MODIFIER)) {
            $mod += 1; // эльфийский лук?
        }
        if ($weapon && $weapon->hasSpecialRule(SpecialRule::CUTTING_EDGE)) {
            $mod += 1; // Cutting Edge ухудшает сейв на 1
        }
        return $mod;
    }

    /**
     * @param SpecialRule $specialRule
     * @return bool
     */
    public function hasSpecialRule(SpecialRule $specialRule): bool
    {
        foreach ($this->items as $equipment) {
            if ($equipment->hasSpecialRule($specialRule))
                return true;
        }

        return false;
    }
}
