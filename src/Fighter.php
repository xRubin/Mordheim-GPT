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
        $this->name = uniqid();
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

    public function getStrength(?Weapon $weapon = null): int
    {
        return $this->blank->getCharacteristics()->strength
            + $this->advancement->getCharacteristics()->strength
            + ($weapon ? $weapon->strength : 0);
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
        if ($this->hasSkill('Frenzy')) {
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
        if ($this->hasSkill('Nimble')) { // TODO: check
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
                if ($this->hasSkill('Scale Sheer Surfaces')) return abs(1.0 * $dz);
                return abs(2.0 * $dz);
            }
            if ($dx !== 0 && $dy !== 0) return 0.7 * (abs($dx) + abs($dy));
            return abs($dx) + abs($dy) + abs($dz);
        };
    }

    /**
     * Расчёт сейва с учётом всей экипировки через менеджер
     */
    public function getArmorSave(?Weapon $attackerWeapon): int
    {
        return $this->equipmentManager->getArmorSave($attackerWeapon);
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
        $moveMultiplier = $this->hasSkill('Sprint') ? 3 : 2;
        $movePoints = $this->getMovement() * $moveMultiplier;
        \Mordheim\BattleLogger::add("{$this->getName()} может Charge на: $movePoints, множитель: $moveMultiplier");
        return $movePoints;
    }

    /**
     * @param string $skillName
     * @return bool
     */
    public function hasSkill(string $skillName): bool
    {
        foreach ($this->blank->getSpecialRules() as $s) {
            if ($s->name === $skillName) return true;
        }
        foreach ($this->advancement->getSpecialRules() as $s) {
            if ($s->name === $skillName) return true;
        }
        return false;
    }

    public function isAdjacent(FighterInterface $target): bool
    {
        return Ruler::isAdjacent($this->getState()->getPosition(), $target->getState()->getPosition());
    }

    public function getDistance(FighterInterface $fighter): bool
    {
        return Ruler::distance($this->getState()->getPosition(), $fighter->getState()->getPosition());
    }
}


