<?php

namespace Mordheim;

class Fighter
{
    private string $name = '';

    public function __construct(
        private readonly Blank              $blank,
        private readonly FighterAdvancement $advancement,
        private readonly EquipmentManager   $equipmentManager,
        private ?FighterState               $fighterState = null,
    )
    {
        $this->name = $blank->name;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): Fighter
    {
        $this->name = $name;
        return $this;
    }

    public function getBlank(): Blank
    {
        return $this->blank;
    }

    public function getAdvancement(): FighterAdvancement
    {
        return $this->advancement;
    }

    public function getEquipmentManager(): EquipmentManager
    {
        return $this->equipmentManager;
    }

    public function getState(): ?FighterState
    {
        return $this->fighterState;
    }

    public function getMovement(bool $withBonus = true): int
    {
        $base = $this->blank->getCharacteristics()->getMovement() + $this->advancement->getCharacteristics()->getMovement();
        if (!$withBonus)
            return $base;
        $bonus = $this->getState()?->getCharacteristics()->getMovement() ?? 0;
        return $base - $bonus;
    }

    public function getStrength(bool $withBonus = true): int
    {
        $base = $this->blank->getCharacteristics()->getStrength() + $this->advancement->getCharacteristics()->getStrength();
        if (!$withBonus)
            return $base;
        $bonus = $this->getState()?->getCharacteristics()->getStrength() ?? 0;
        return $base - $bonus;
    }

    public function getWeaponSkill(bool $withBonus = true): int
    {
        $base = $this->blank->getCharacteristics()->getWeaponSkill() + $this->advancement->getCharacteristics()->getWeaponSkill();
        if (!$withBonus)
            return $base;
        $bonus = $this->getState()?->getCharacteristics()->getWeaponSkill() ?? 0;
        if ($this->getState()?->hasActiveSpell(Spell::SWORD_OF_REZHEBEL)) {
            $bonus += 2;
        }
        return $base - $bonus;
    }

    public function getBallisticSkill(bool $withBonus = true): int
    {
        $base = $this->blank->getCharacteristics()->getBallisticSkill() + $this->advancement->getCharacteristics()->getBallisticSkill();
        if (!$withBonus)
            return $base;
        $bonus = $this->getState()?->getCharacteristics()->getBallisticSkill() ?? 0;
        return $base - $bonus;
    }

    public function getToughness(bool $withBonus = true): int
    {
        $base = $this->blank->getCharacteristics()->getToughness() + $this->advancement->getCharacteristics()->getToughness();
        if (!$withBonus)
            return $base;
        $bonus = $this->getState()?->getCharacteristics()->getToughness() ?? 0;
        return $base - $bonus;
    }

    public function getWounds(bool $withBonus = true): int
    {
        $base = $this->blank->getCharacteristics()->getWounds() + $this->advancement->getCharacteristics()->getWounds();
        if (!$withBonus)
            return $base;
        $bonus = $this->getState()?->getCharacteristics()->getWounds() ?? 0;
        return $base - $bonus;
    }

    public function getLeadership(bool $withBonus = true): int
    {
        $base = $this->blank->getCharacteristics()->getLeadership() + $this->advancement->getCharacteristics()->getLeadership();
        if (!$withBonus)
            return $base;
        $bonus = $this->getState()?->getCharacteristics()->getLeadership() ?? 0;
        return $base - $bonus;
    }

    /**
     * Итоговое количество атак с учётом экипировки (два одноручных оружия = +1 атака)
     */
    public function getAttacks(bool $withBonus = true): int
    {
        $base = $this->blank->getCharacteristics()->getAttacks() + $this->advancement->getCharacteristics()->getAttacks();
        if (!$withBonus)
            return $base;
        $bonus = $this->getState()?->getCharacteristics()->getAttacks() ?? 0;
        if ($this->getState()?->getStatus() === Status::FRENZY) {
            $base *= 2;
        }
        if ($this->getState()?->hasActiveSpell(Spell::SWORD_OF_REZHEBEL)) {
            $bonus += 1;
            return $base + $bonus;
        }
        if ($this->getState()?->hasActiveSpell(Spell::BLACK_FURY)) {
            $bonus += 2;
            return $base + $bonus;
        }
        if ($this->equipmentManager->countOneHandedMeleeWeapons() >= 2) {
            $bonus += 1;
        }
        return $base + $bonus;
    }

    /**
     * Получить итоговую инициативу с учетом навыков
     */
    public function getInitiative(bool $withBonus = true): int
    {
        $base = $this->blank->getCharacteristics()->getInitiative() + $this->advancement->getCharacteristics()->getInitiative();
        if (!$withBonus)
            return $base;
        $bonus = $this->getState()?->getCharacteristics()->getInitiative() ?? 0;
        return $base - $bonus;
    }

    public function getClimbInitiative(): int
    {
        $bonus = 0;
        if ($this->equipmentManager->hasSpecialRule(SpecialRule::CLIMB)) {
            $bonus += 1;
        }
        return $this->getInitiative() + $bonus;
    }

    /**
     * Расчет весов для алгоритма передвижения
     */
    public function getMovementWeights(): callable
    {
        return function (FieldCell $from, FieldCell $to, $dx, $dy, $dz): float {
            $multiplier = $to->water ? 5.0 : 1.0;
            if ($dz !== 0) {
                if ($this->hasSpecialRule(SpecialRule::SCALE_SHEER_SURFACES)) return abs($multiplier * $dz);
                return abs($multiplier * 2 * $dz);
            }
            if ($dx !== 0 && $dy !== 0) return $multiplier * 0.7 * (abs($dx) + abs($dy));
            return $multiplier * (abs($dx) + abs($dy) + abs($dz));
        };
    }

    /**
     * Расчёт сейва с учётом всей экипировки и состояний
     */
    public function getArmourSave(?Equipment $attackerWeapon): int
    {
        $save = 0;
        if ($this->hasSpecialRule(SpecialRule::SAVE_2))
            $save = 2;
        elseif ($this->hasSpecialRule(SpecialRule::SAVE_3))
            $save = 2;
        elseif ($this->hasSpecialRule(SpecialRule::METALLIC_BODY))
            $save = 3;
        elseif ($this->hasSpecialRule(SpecialRule::SAVE_4))
            $save = 4;
        elseif ($this->hasSpecialRule(SpecialRule::SAVE_5))
            $save = 5;
        elseif ($this->hasSpecialRule(SpecialRule::SAVE_6))
            $save = 6;

        // Учет спецправила PLUS_1_ENEMY_ARMOUR_SAVE от оружия атакующего
        if ($save && $attackerWeapon?->hasSpecialRule(SpecialRule::PLUS_1_ENEMY_ARMOUR_SAVE)) {
            $save -= 1;
        }

        return $save;
    }

    public function getHitModifier(?Equipment $attackerWeapon): int
    {
        if (!$attackerWeapon)
            return 0;

        if ($attackerWeapon->hasSpecialRule(SpecialRule::ACCURACY))
            return 1;

        //SpecialRule::SHOOT_IN_HAND_TO_HAND_COMBAT ?

        return 0;
    }

    /**
     * Получить итоговое движение с учетом экипировки
     */
    public function getMoveRange(): int
    {
        $penalty = $this->equipmentManager->getMovementPenalty();
        return max(1, $this->getMovement() + $penalty); // движение не может быть меньше 1
    }

    /**
     * Максимальная диистанция для бега
     * @return int
     */
    public function getRunRange(): int
    {
        $moveMultiplier = $this->hasSpecialRule(SpecialRule::MAY_NOT_RUN) ? 1 : 2;
        $penalty = $this->equipmentManager->getMovementPenalty();
        $movePoints = $this->getMovement() * $moveMultiplier;
        return max(1, $movePoints + $penalty); // движение не может быть меньше 1
    }

    /**
     * Максимальная дистанция для Charge
     * @return int
     */
    public function getChargeRange(): int
    {
        $moveMultiplier = $this->hasSpecialRule(SpecialRule::SPRINT) ? 3 : 2;
        $penalty = $this->equipmentManager->getMovementPenalty();
        $movePoints = $this->getMovement() * $moveMultiplier;
        return max(1, $movePoints + $penalty); // движение не может быть меньше 1
    }

    /**
     * @param SpecialRule $specialRule
     * @return bool
     */
    public function hasSpecialRule(SpecialRule $specialRule): bool
    {
        return $this->getBlank()->hasSpecialRule($specialRule)
            || $this->getAdvancement()->hasSpecialRule($specialRule)
            || $this->getEquipmentManager()->hasSpecialRule($specialRule)
            || $this->getState()?->hasSpecialRule($specialRule);
    }

    /**
     * Оружие в зависимости от номера атаки
     */
    public function getWeaponByAttackIdx(Slot $slot, int $i): Equipment
    {
        if ($this->getState()?->hasActiveSpell(Spell::SWORD_OF_REZHEBEL))
            return Equipment::SWORD_OF_REZHEBEL;

        $offset = 0;
        foreach ($this->getEquipmentManager()->getItemsBySlot($slot) as $idx => $equipment) {
            if (in_array($equipment, [Equipment::BUCKLER, Equipment::SHIELD]))
                continue;
            if ($equipment->hasSpecialRule(SpecialRule::TWO_HANDED)) {
                $offset += 1;
            }
            if ($idx >= ($i - $offset))
                return $equipment;
        }

        return Equipment::FIST;
    }

    public function getRating():int
    {
        return $this->getBlank()->getRating() + $this->getAdvancement()->getExp();
    }
}


