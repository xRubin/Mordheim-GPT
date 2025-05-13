<?php

namespace Mordheim;

class Fighter
{
    private string $name = '';

    public function __construct(
        private readonly BlankInterface              $blank,
        private readonly FighterAdvancement $advancement,
        private readonly EquipmentManager            $equipmentManager,
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

    public function getBlank(): BlankInterface
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

    public function getState(): FighterState
    {
        return $this->fighterState;
    }

    public function getMovement(): int
    {
        return $this->blank->getCharacteristics()->getMovement() + $this->advancement->getCharacteristics()->getMovement();
    }

    public function getStrength(): int
    {
        return $this->blank->getCharacteristics()->getStrength() + $this->advancement->getCharacteristics()->getStrength();
    }

    public function getWeaponSkill(): int
    {
        return $this->blank->getCharacteristics()->getWeaponSkill() + $this->advancement->getCharacteristics()->getWeaponSkill();
    }

    public function getBallisticSkill(): int
    {
        return $this->blank->getCharacteristics()->getBallisticSkill() + $this->advancement->getCharacteristics()->getBallisticSkill();
    }

    public function getToughness(): int
    {
        return $this->blank->getCharacteristics()->getToughness() + $this->advancement->getCharacteristics()->getToughness();
    }

    public function getWounds(): int
    {
        return $this->blank->getCharacteristics()->getWounds() + $this->advancement->getCharacteristics()->getWounds();
    }

    public function getLeadership(): int
    {
        return $this->blank->getCharacteristics()->getLeadership() + $this->advancement->getCharacteristics()->getLeadership();
    }

    /**
     * Итоговое количество атак с учётом экипировки (два одноручных оружия = +1 атака)
     */
    public function getAttacks(): int
    {
        $base = $this->blank->getCharacteristics()->getAttacks() + $this->advancement->getCharacteristics()->getAttacks();
        $bonus = 0;
        if ($this->getState()?->getStatus() === Status::FRENZY) {
            $base *= 2;
        }
        if ($this->equipmentManager->countOneHandedMeleeWeapons() >= 2) {
            $bonus = 1;
        }
        return $base + $bonus;
    }

    /**
     * Получить итоговую инициативу с учетом навыков
     */
    public function getInitiative(): int
    {
        return $this->blank->getCharacteristics()->getInitiative() + $this->advancement->getCharacteristics()->getInitiative();
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
        return function ($dx, $dy, $dz) {
            if ($dz !== 0) {
                if ($this->hasSpecialRule(SpecialRule::SCALE_SHEER_SURFACES)) return abs(1.0 * $dz);
                return abs(2.0 * $dz);
            }
            if ($dx !== 0 && $dy !== 0) return 0.7 * (abs($dx) + abs($dy));
            return abs($dx) + abs($dy) + abs($dz);
        };
    }

    /**
     * Расчёт сейва с учётом всей экипировки и состояний
     */
    public function getArmorSave(?EquipmentInterface $attackerWeapon): int
    {
        if ($this->hasSpecialRule(SpecialRule::SAVE_2))
            return 2;
        if ($this->hasSpecialRule(SpecialRule::SAVE_3))
            return 2;
        if ($this->hasSpecialRule(SpecialRule::METALLIC_BODY))
            return 3;
        if ($this->hasSpecialRule(SpecialRule::SAVE_4))
            return 4;
        if ($this->hasSpecialRule(SpecialRule::SAVE_5))
            return 5;
        if ($this->hasSpecialRule(SpecialRule::SAVE_6))
            return 6;

        return 0;
    }

    public function getHitModifier(?EquipmentInterface $attackerWeapon): int
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
}


