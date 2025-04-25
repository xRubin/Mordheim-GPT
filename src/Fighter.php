<?php

namespace Mordheim;

use Mordheim\Strategy\BattleStrategy;

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
    public BattleStrategy $battleStrategy;
    public array $position = [0, 0, 0]; // [x, y, z]
    public bool $alive = true;
    /**
     * Состояние бойца (enum FighterState)
     */
    public FighterState $state = FighterState::STANDING;

    public function __construct(
        string           $name,
        Characteristics  $characteristics,
        array            $skills,
        EquipmentManager $equipmentManager,
        BattleStrategy   $battleStrategy,
        array            $position = [0, 0, 0],
        FighterState     $state = FighterState::STANDING,
        int              $experience = 0,
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
     * Попытка переместиться к цели с учетом препятствий и других юнитов (старый метод)
     */
    public function moveTowards(array $target, \Mordheim\GameField $field, array $otherUnits = []): void
    {
        // Собираем позиции других юнитов (кроме себя)
        $blockers = [];
        foreach ($otherUnits as $unit) {
            if ($unit !== $this && $unit->alive) {
                $blockers[] = $unit->position;
            }
        }
        $path = \Mordheim\PathFinder::findPath($field, $this->position, $target, $this->getMovementWeights(), $blockers);
        if ($path && count($path) > 1) {
            $steps = min($this->characteristics->movement, count($path) - 1);
            $from = $this->position;
            for ($i = 1; $i <= $steps; $i++) {
                $to = $path[$i]['pos'];
                \Mordheim\BattleLogger::add("{$this->name} перемещается с [" . implode(',', $from) . "] на [" . implode(',', $to) . "]");
                $from = $to;
            }
            $this->position = $from;
        }
    }

    /**
     * Продвинутое движение по правилам Mordheim: поверхность, труднопроходимость, опасность, прыжки, вода, лестницы, высота
     * Возвращает подробный лог хода
     * @param array $target
     * @param \Mordheim\GameField $field
     * @param array $otherUnits
     * @param bool $partialMove Если true — двигаться максимально в направлении цели, даже если не хватает очков движения
     * @return array
     */
    public function moveAdvancedTowards(array $target, \Mordheim\GameField $field, array $otherUnits = [], bool $partialMove = false): array
    {
        $log = [];
        $blockers = [];
        foreach ($otherUnits as $unit) {
            if ($unit !== $this && $unit->alive) {
                $blockers[] = $unit->position;
            }
        }
        $movePoints = $this->characteristics->movement;
        $sprintBonus = 0;
        // Sprint: +D6 к движению при беге (если partialMove == false)
        if ($this->hasSkill('Sprint') && !$partialMove) {
            $sprintBonus = \Mordheim\Dice::roll(6);
            \Mordheim\BattleLogger::add("{$this->name} использует Sprint: бонус к движению = $sprintBonus");
            $movePoints += $sprintBonus;
        }
        \Mordheim\BattleLogger::add("{$this->name}: movePoints = $movePoints (base: {$this->characteristics->movement}, sprintBonus: $sprintBonus)");
        // Получаем полный путь до цели
        $path = \Mordheim\PathFinder::findPath($field, $this->position, $target, $this->getMovementWeights(), $blockers);
        if ($path) {
            \Mordheim\BattleLogger::add("{$this->name}: путь до цели: " . json_encode(array_map(fn($p) => $p['pos'], $path)));
        }
        if ((!$path || count($path) < 2) && !$partialMove) {
            $log[] = 'Нет пути к цели или недостаточно очков движения';
            return $log;
        }
        // Если partialMove и путь не найден
        if ($partialMove && (!$path || count($path) < 2)) {
            $log[] = 'Нет даже частичного пути к цели';
            return $log;
        }
        // Определяем, куда реально можем дойти по накопленной стоимости пути
        $lastReachableIdx = 0;
        for ($i = 1; $i < count($path); $i++) {
            if ($path[$i]['cost'] > $movePoints + 1e-6) break; // допускаем погрешность для float
            $lastReachableIdx = $i;
        }
        // Если можем дойти до цели полностью
        if ($lastReachableIdx === count($path) - 1) {
            // обычная логика движения (до цели)
            $from = $this->position;
            for ($i = 1; $i < count($path); $i++) {
                $to = $path[$i]['pos'];
                \Mordheim\BattleLogger::add("{$this->name} перемещается с [" . implode(',', $from) . "] на [" . implode(',', $to) . "]");
                $from = $to;
            }
            $this->position = $from;
        } elseif ($lastReachableIdx > 0) {
            // Двигаемся максимально далеко по пути (даже если partialMove == false)
            $from = $this->position;
            for ($i = 1; $i <= $lastReachableIdx; $i++) {
                $to = $path[$i]['pos'];
                \Mordheim\BattleLogger::add("{$this->name} перемещается с [" . implode(',', $from) . "] на [" . implode(',', $to) . "] (максимальное движение)");
                $from = $to;
            }
            $this->position = $from;
            $log[] = 'Двигаемся максимально в сторону цели, но цель недостижима за ход. Новая позиция: (' . implode(',', $from) . ')';
            return $log;
        } elseif ($lastReachableIdx === 0) {
            $log[] = 'Нет даже частичного пути к цели';
            return $log;
        } else {
            $log[] = 'Недостаточно очков движения для достижения цели';
            return $log;
        }
        $cur = $this->position;
        $stepsTaken = 0;
        for ($i = 1; $i < count($path) && $movePoints > 0; $i++) {
            [$x, $y, $z] = $path[$i]['pos'];
            $cell = $field->getCell($x, $y, $z);
            $fromCell = $field->getCell($cur[0], $cur[1], $cur[2]);
            $cost = 1;
            $desc = "";
            // Difficult terrain: double cost
            if ($cell->difficultTerrain) {
                $cost = 2;
                $desc .= "Труднопроходимая местность. ";
            }
            // Water: must swim, can't run, test Initiative or stop
            if ($cell->water) {
                $desc .= "Вода: требуется тест Initiative. ";
                $roll = \Mordheim\Dice::roll(6);
                \Mordheim\BattleLogger::add("{$this->name} бросает Initiative для воды: $roll против {$this->getInitiative()}");
                if ($roll > $this->getInitiative()) {
                    $msg = "Провал Initiative в воде — движение остановлено на клетке ($x,$y,$z)";
                    \Mordheim\BattleLogger::add($msg);
                    $log[] = $msg;
                    break;
                }
                $cost = 2;
            }
            // Dangerous terrain: test Initiative or fall
            if ($cell->dangerousTerrain) {
                $desc .= "Опасная местность: тест Initiative. ";
                $roll = \Mordheim\Dice::roll(6);
                \Mordheim\BattleLogger::add("{$this->name} бросает Initiative на опасной местности: $roll против {$this->getInitiative()}");
                if ($roll > $this->getInitiative()) {
                    $msg = "Провал Initiative на опасной клетке ($x,$y,$z) — юнит упал";
                    \Mordheim\BattleLogger::add($msg);
                    $this->position = [$x, $y, $z];
                    $log[] = $msg;
                    return $log;
                }
            }
            // Прыжок через разрыв: если разница высот > 1
            if (abs($cell->height - $fromCell->height) > 1) {
                $desc .= "Прыжок: тест Initiative. ";
                $roll = \Mordheim\Dice::roll(6);
                \Mordheim\BattleLogger::add("{$this->name} бросает Initiative для прыжка: $roll против {$this->getInitiative()}");
                if ($roll > $this->getInitiative()) {
                    $msg = "Провал Initiative при прыжке — юнит падает на ({$x},{$y},{$z})";
                    \Mordheim\BattleLogger::add($msg);
                    $this->position = [$x, $y, $z];
                    $log[] = $msg;
                    return $log;
                }
            }
            // Лестница: можно двигаться по вертикали
            if ($cell->ladder || $fromCell->ladder) {
                $desc .= "Лестница: разрешено движение по вертикали. ";
            }
            if ($cost > $movePoints) {
                $msg = "Недостаточно очков движения для клетки ($x,$y,$z)";
                \Mordheim\BattleLogger::add($msg);
                $log[] = $msg;
                break;
            }
            $movePoints -= $cost;
            $cur = [$x, $y, $z];
            $stepsTaken++;
            $log[] = "Перемещён на ($x,$y,$z): $desc Осталось ОД: $movePoints";
        }
        $this->position = $cur;
        $log[] = "Движение завершено. Итоговая позиция: (" . implode(",", $cur) . ")";
        return $log;
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


