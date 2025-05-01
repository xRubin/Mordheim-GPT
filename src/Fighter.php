<?php

namespace Mordheim;

use Mordheim\Strategy\BattleStrategyInterface;

class Fighter
{
    /** @var int Текущий опыт */
    public int $experience = 0;
    /** @var int Опыт, полученный за бой (для отчёта) */
    public int $battleExperience = 0;
    /** @var int Максимальное количество навыков (по правилам) */
    public int $maxSkills = 6;

    public string $name;
    public Characteristics $characteristics;
    /** @var Skill[] */
    public array $skills = [];
    public EquipmentManager $equipmentManager;
    public BattleStrategyInterface $battleStrategy;
    public array $position = [0, 0, 0]; // [x, y, z]
    public bool $alive = true;
    /**
     * Состояние бойца (enum FighterState)
     */
    public FighterState $state = FighterState::STANDING;

    public function __construct(
        string                  $name,
        Characteristics         $characteristics,
        array                   $skills,
        EquipmentManager        $equipmentManager,
        BattleStrategyInterface $battleStrategy,
        array                   $position = [0, 0, 0],
        FighterState            $state = FighterState::STANDING,
        int                     $experience = 0,
    )
    {
        $this->name = $name;
        $this->characteristics = $characteristics;
        $this->skills = $skills;
        $this->equipmentManager = $equipmentManager;
        $this->battleStrategy = $battleStrategy;
        $this->position = $position;
        $this->state = $state;
        $this->experience = $experience;
    }

    /**
     * Попытка избежать стана с помощью шлема (Avoid stun, 4+)
     * @return bool true — спасся (stun превращается в knockdown), false — не спасся
     */
    public function tryAvoidStun(): bool
    {
        if ($this->equipmentManager->hasHelmetProtection()) {
            $roll = \Mordheim\Dice::roll(6);
            if ($roll >= 4) {
                \Mordheim\BattleLogger::add("{$this->name} спасся от стана шлемом (бросок $roll)");
                return true;
            }
        }
        return false;
    }

    /**
     * Получить итоговое движение с учетом экипировки
     */
    public function getMovement(): int
    {
        $base = $this->characteristics->movement;
        $penalty = $this->equipmentManager->getMovementPenalty();
        return max(1, $base + $penalty); // движение не может быть меньше 1
    }

    /**
     * Итоговое количество атак с учётом экипировки (два одноручных оружия = +1 атака)
     */
    public function getAttacks(): int
    {
        $base = $this->characteristics->attacks;
        $bonus = 0;
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
        $base = $this->characteristics->initiative;
        $bonus = 0;
        if ($this->hasSkill('Nimble')) {
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
                if ($this->hasSkill('Scale Sheer Surfaces')) return 1.0;
                return 2.0;
            }
            if ($dx !== 0 && $dy !== 0) return 1.4;
            return 1.0;
        };
    }

    /**
     * Расчёт сейва с учётом всей экипировки через менеджер
     */
    public function getArmorSave(?Weapon $attackerWeapon): int
    {
        return $this->equipmentManager->getArmorSave($attackerWeapon);
    }

    public function isAdjacent(Fighter $other): bool
    {
        [$x1, $y1, $z1] = $this->position;
        [$x2, $y2, $z2] = $other->position;
        return abs($x1 - $x2) <= 1 && abs($y1 - $y2) <= 1 && abs($z1 - $z2) <= 1;
    }

    public function distance(Fighter $other): float
    {
        [$x1, $y1, $z1] = $this->position;
        [$x2, $y2, $z2] = $other->position;
        return sqrt(pow($x1 - $x2, 2) + pow($y1 - $y2, 2) + pow($z1 - $z2, 2));
    }

    /**
     * Добавить опыт бойцу
     */
    public function addExperience(int $amount): void
    {
        $this->experience += $amount;
        $this->battleExperience += $amount;
    }

    /**
     * Проверить, может ли боец повыситься (по таблице опыта)
     */
    public function canAdvance(): bool
    {
        $levels = [2, 4, 6, 8, 10, 12, 14, 16]; // стандартные пороги опыта
        foreach ($levels as $lvl) {
            if ($this->experience === $lvl) return true;
        }
        return false;
    }

    /**
     * Получить текущий уровень по опыту (0 = стартовый)
     */
    public function getExperienceLevel(): int
    {
        $levels = [2, 4, 6, 8, 10, 12, 14, 16];
        $level = 0;
        foreach ($levels as $lvl) {
            if ($this->experience >= $lvl) $level++;
        }
        return $level;
    }


    /**
     * Добавить навык бойцу (если не превышен лимит)
     */
    public function addSkill(Skill $skill): bool
    {
        if (count($this->skills) >= $this->maxSkills) return false;
        foreach ($this->skills as $s) {
            if ($s->name === $skill->name) return false;
        }
        $this->skills[] = $skill;
        return true;
    }

    /**
     * Базовая дистанция заряда (charge) для Mordheim — 8 дюймов
     * @return int
     */
    public function chargeRange(): int
    {
        return 8;
    }

    /**
     * @param string $skillName
     * @return bool
     */
    public function hasSkill(string $skillName): bool
    {
        foreach ($this->skills as $s) {
            if ($s->name === $skillName) return true;
        }
        return false;
    }
}


