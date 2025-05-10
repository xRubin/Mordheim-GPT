<?php

namespace Mordheim;

class Fighter implements FighterInterface
{
    private string $name = '';

    public function __construct(
        private readonly BlankInterface       $blank,
        private readonly AdvancementInterface $advancement,
        private readonly EquipmentManager     $equipmentManager,
        private ?FighterStateInterface        $fighterState = null,
    )
    {
        $this->name = $blank->name;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getEquipmentManager(): EquipmentManager
    {
        return $this->equipmentManager;
    }

    public function getState(): FighterStateInterface
    {
        return $this->fighterState;
    }

    public function setFighterState(?FighterStateInterface $fighterState): static
    {
        $this->fighterState = $fighterState;
        return $this;
    }

    /**
     * Получить итоговое движение с учетом экипировки
     */
    public function getMovement(): int
    {
        $base = $this->blank->getCharacteristics()->movement + $this->advancement->getCharacteristics()->movement;
        $penalty = $this->equipmentManager->getMovementPenalty();
        return max(1, $base + $penalty); // движение не может быть меньше 1
    }

    public function getStrength(): int
    {
        return $this->blank->getCharacteristics()->strength + $this->advancement->getCharacteristics()->strength;
    }

    public function getWeaponSkill(): int
    {
        return $this->blank->getCharacteristics()->weaponSkill + $this->advancement->getCharacteristics()->weaponSkill;
    }

    public function getBallisticSkill(): int
    {
        return $this->blank->getCharacteristics()->ballisticSkill + $this->advancement->getCharacteristics()->ballisticSkill;
    }

    public function getToughness(): int
    {
        return $this->blank->getCharacteristics()->toughness + $this->advancement->getCharacteristics()->toughness;
    }

    public function getLeadership(): int
    {
        return $this->blank->getCharacteristics()->leadership + $this->advancement->getCharacteristics()->leadership;
    }

    /**
     * Итоговое количество атак с учётом экипировки (два одноручных оружия = +1 атака)
     */
    public function getAttacks(): int
    {
        $base = $this->blank->getCharacteristics()->attacks + $this->advancement->getCharacteristics()->attacks;
        $bonus = 0;
        if ($this->getState()->getStatus() === Status::FRENZY) {
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
        $base = $this->blank->getCharacteristics()->initiative + $this->advancement->getCharacteristics()->initiative;
        $bonus = 0;
        if ($this->hasSpecialRule(SpecialRule::NIMBLE)) {  // TODO: check rules
            $bonus = 1;
        }
        return $base + $bonus;
    }

    public function getClimbInitiative(): int
    {
        $base = $this->blank->getCharacteristics()->initiative + $this->advancement->getCharacteristics()->initiative;
        $bonus = 0;
        if ($this->equipmentManager->hasSpecialRule(SpecialRule::CLIMB)) {
            $bonus = 1;
        }
        return $base + $bonus;
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
     * Расчёт сейва с учётом всей экипировки через менеджер
     */
    public function getArmorSave(?EquipmentInterface $attackerWeapon): int
    {
        return $this->equipmentManager->getArmorSave($attackerWeapon);
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
     * Максимальная диистанция для бега
     * @return int
     */
    public function getRunRange(): int
    {
        $moveMultiplier = 2;
        $movePoints = $this->getMovement() * $moveMultiplier;
        \Mordheim\BattleLogger::add("{$this->getName()} может бежать на: $movePoints, множитель: $moveMultiplier");
        return $movePoints;
    }

    /**
     * Максимальная дистанция для Charge
     * @return int
     */
    public function getChargeRange(): int
    {
        $moveMultiplier = $this->hasSpecialRule(SpecialRule::SPRINT) ? 3 : 2;
        $movePoints = $this->getMovement() * $moveMultiplier;
        \Mordheim\BattleLogger::add("{$this->getName()} может Charge на: $movePoints, множитель: $moveMultiplier");
        return $movePoints;
    }

    /**
     * @param SpecialRule $specialRule
     * @return bool
     */
    public function hasSpecialRule(SpecialRule $specialRule): bool
    {
        return in_array($specialRule, $this->blank->getSpecialRules())
            || in_array($specialRule, $this->advancement->getSpecialRules())
            || $this->equipmentManager->hasSpecialRule($specialRule);
    }

    public function isAdjacent(FighterInterface $target): bool
    {
        return Ruler::isAdjacent($this->getState()->getPosition(), $target->getState()->getPosition());
    }

    public function getDistance(FighterInterface $target): float
    {
        return Ruler::distance($this->getState()->getPosition(), $target->getState()->getPosition());
    }
}


