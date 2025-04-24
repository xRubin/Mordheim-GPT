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
                // Можно использовать BattleLogger для вывода
                // \Mordheim\BattleLogger::add("{$this->name} спасся от стана шлемом (бросок $roll)");
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
        $path = \Mordheim\PathFinder::findPath($field, $this->position, $target, $blockers);
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
     */
    /**
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
        $path = \Mordheim\PathFinder::findPath($field, $this->position, $target, $blockers);
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
                \Mordheim\BattleLogger::add("{$this->name} бросает Initiative для воды: $roll против {$this->characteristics->initiative}");
                if ($roll > $this->characteristics->initiative) {
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
                \Mordheim\BattleLogger::add("{$this->name} бросает Initiative на опасной местности: $roll против {$this->characteristics->initiative}");
                if ($roll > $this->characteristics->initiative) {
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
                \Mordheim\BattleLogger::add("{$this->name} бросает Initiative для прыжка: $roll против {$this->characteristics->initiative}");
                if ($roll > $this->characteristics->initiative) {
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
     * Ближний бой по правилам Mordheim (https://mordheimer.net/docs/rules/close-combat)
     * Учитывает WS, силу, брони, оружие, навыки, эффекты (оглушение, выбивание, крит, дубины, топоры и др.)
     * Возвращает true если нанесён урон, false если нет
     * Выполняет атаку по правилам Mordheim с учётом спецправил оружия и навыков.
     * @param Fighter $target
     * @return bool true если нанесён урон, false если промах/парирование/сейв
     */
    public function attack(Fighter $target): bool
    {
        // Учет психологических и физических состояний
        $invalidStates = [
            FighterState::PANIC,
            FighterState::KNOCKED_DOWN,
            FighterState::STUNNED,
            FighterState::OUT_OF_ACTION
        ];
        if (in_array($this->state, $invalidStates, true)) {
            \Mordheim\BattleLogger::add("{$this->name} не может атаковать из-за состояния: {$this->state->value}.");
            return false;
        }
        if (in_array($target->state, [FighterState::OUT_OF_ACTION], true)) {
            \Mordheim\BattleLogger::add("{$target->name} не может быть атакован: состояние {$target->state->value}.");
            return false;
        }
        if (!$this->alive || !$target->alive || !$this->isAdjacent($target)) return false;
        \Mordheim\BattleLogger::add("{$this->name} атакует {$target->name}!");
        $weapon = $this->equipmentManager->getMainWeapon();
        $attackerWS = $this->characteristics->weaponSkill;
        $defenderWS = $target->characteristics->weaponSkill;
        $toHitMod = $weapon ? $weapon->toHitModifier : 0;

        // Особые правила для атак по KNOCKED_DOWN/STUNNED
        if ($target->state === FighterState::STUNNED) {
            \Mordheim\BattleLogger::add("Атака по оглушённому (STUNNED): попадание и ранение автоматически успешны, сейв невозможен.");
            $injuryMod = $this->equipmentManager->getInjuryModifier($weapon);
            $specialRules = $weapon ? $weapon->specialRules : [];
            $injuryRoll = \Mordheim\Dice::roll(6);
            \Mordheim\BattleLogger::add("Бросок на травму: $injuryRoll (модификатор: $injuryMod)");
            $target->rollInjury($weapon ? $weapon->name : '', $injuryMod, $specialRules, $injuryRoll);
            return true;
        }
        if ($target->state === FighterState::KNOCKED_DOWN) {
            \Mordheim\BattleLogger::add("Атака по сбитому с ног (KNOCKED_DOWN): попадание автоматически успешно.");
            // Пропускаем бросок на попадание, но остальное — как обычно
            $parried = false;
            $defenderWeapon = $target->equipmentManager->getMainWeapon();
            // Flail игнорирует парирование
            $canBeParried = $this->equipmentManager->canBeParried($weapon, $defenderWeapon, 6); // максимальный бросок, чтобы не дать шанс парировать
            if ($canBeParried) {
                $parryRoll = \Mordheim\Dice::roll(6);
                \Mordheim\BattleLogger::add("{$target->name} пытается парировать: $parryRoll против 6");
                if ($parryRoll >= 6) {
                    $parried = true;
                    \Mordheim\BattleLogger::add("Парирование удалось!");
                } else {
                    \Mordheim\BattleLogger::add("Парирование не удалось.");
                }
            }
            if ($target->hasSkill('Step Aside') && !$parried) {
                $stepAsideRoll = \Mordheim\Dice::roll(6);
                \Mordheim\BattleLogger::add("{$target->name} использует Step Aside: $stepAsideRoll (нужно 5+)");
                if ($stepAsideRoll >= 5) {
                    $parried = true;
                    \Mordheim\BattleLogger::add("Step Aside сработал!");
                } else {
                    \Mordheim\BattleLogger::add("Step Aside не сработал.");
                }
            }
            if ($parried) {
                \Mordheim\BattleLogger::add("Атака парирована!");
                return false;
            }
            // Дальше обычный бросок на ранение и сейв
            $attackerS = $this->characteristics->strength + ($weapon ? $weapon->strength : 0);
            $resilientMod = $this->equipmentManager->getResilientModifier($target);
            $defenderT = $target->characteristics->toughness + $resilientMod;
            $toWound = 4;
            if ($attackerS > $defenderT) $toWound = 3;
            if ($attackerS >= 2 * $defenderT) $toWound = 2;
            if ($attackerS < $defenderT) $toWound = 5;
            if ($attackerS * 2 <= $defenderT) $toWound = 6;
            \Mordheim\BattleLogger::add("Сила атакующего: $attackerS, Стойкость защищающегося: $defenderT, модификатор Resilient: $resilientMod, итоговое значение для ранения: $toWound+");
            $woundRoll = \Mordheim\Dice::roll(6);
            \Mordheim\BattleLogger::add("{$this->name} бросает на ранение: $woundRoll (нужно $toWound+)");
            \Mordheim\BattleLogger::add("[DEBUG] attackerS={$attackerS}, defenderT={$defenderT}, resilientMod={$resilientMod}, toWound={$toWound}, woundRoll={$woundRoll}");
            if ($woundRoll < $toWound) {
                \Mordheim\BattleLogger::add("Ранение не удалось!");
                \Mordheim\BattleLogger::add("[DEBUG] result=false (woundRoll < toWound)");
                return false;
            }
            $armorSave = $target->getArmorSave($weapon);
            $armorSaveMod = $this->equipmentManager->getArmorSaveModifier($weapon);
            $armorSave += $armorSaveMod;
            \Mordheim\BattleLogger::add("Сэйв защищающегося: $armorSave (модификатор: $armorSaveMod)");
            $saveRoll = null;
            if ($armorSave > 0) {
                $saveRoll = \Mordheim\Dice::roll(6);
                \Mordheim\BattleLogger::add("{$target->name} бросает на сэйв: $saveRoll (нужно $armorSave+)");
                \Mordheim\BattleLogger::add("[DEBUG] armorSave={$armorSave}, saveRoll={$saveRoll}");
                if ($saveRoll >= $armorSave) {
                    \Mordheim\BattleLogger::add("Сэйв удался! Урон не нанесён.");
                    \Mordheim\BattleLogger::add("[DEBUG] result=false (saveRoll >= armorSave)");
                    return false;
                } else {
                    \Mordheim\BattleLogger::add("Сэйв не удался.");
                }
            }
            $injuryMod = $this->equipmentManager->getInjuryModifier($weapon);
            $specialRules = $weapon ? $weapon->specialRules : [];
            $injuryRoll = \Mordheim\Dice::roll(6);
            \Mordheim\BattleLogger::add("Бросок на травму: $injuryRoll (модификатор: $injuryMod)");
            $target->rollInjury($weapon ? $weapon->name : '', $injuryMod, $specialRules, $injuryRoll);
            \Mordheim\BattleLogger::add("[DEBUG] result=true (damage inflicted)");
            return true;
        }

        // --- Обычный бой (по стандартным правилам) ---
        $attackerWS = $this->characteristics->weaponSkill;
        $defenderWS = $target->characteristics->weaponSkill;
        $toHitMod = $weapon ? $weapon->toHitModifier : 0;
// 1. Roll to hit (WS vs WS)
        $toHit = 4;
        if ($attackerWS > $defenderWS) $toHit = 3;
        if ($attackerWS >= 2 * $defenderWS) $toHit = 2;
        if ($attackerWS < $defenderWS) $toHit = 5;
        if ($attackerWS * 2 <= $defenderWS) $toHit = 6;
        $toHit += $toHitMod;
        \Mordheim\BattleLogger::add("WS атакующего: $attackerWS, WS защищающегося: $defenderWS, модификаторы: Weapon {$toHitMod}, итоговое значение для попадания: $toHit+");
        $hitRoll = \Mordheim\Dice::roll(6);
        \Mordheim\BattleLogger::add("{$this->name} бросает на попадание: $hitRoll (нужно $toHit+)");
        $parried = false;
        $defenderWeapon = $target->equipmentManager->getMainWeapon();
        $canBeParried = $this->equipmentManager->canBeParried($weapon, $defenderWeapon, $hitRoll);
        if ($canBeParried) {
            $parryRoll = \Mordheim\Dice::roll(6);
            \Mordheim\BattleLogger::add("{$target->name} пытается парировать: $parryRoll против $hitRoll");
            if ($parryRoll >= $hitRoll) {
                $parried = true;
                \Mordheim\BattleLogger::add("Парирование удалось!");
            } else {
                \Mordheim\BattleLogger::add("Парирование не удалось.");
            }
        }
        if ($target->hasSkill('Step Aside') && !$parried) {
            $stepAsideRoll = \Mordheim\Dice::roll(6);
            \Mordheim\BattleLogger::add("{$target->name} использует Step Aside: $stepAsideRoll (нужно 5+)");
            if ($stepAsideRoll >= 5) {
                $parried = true;
                \Mordheim\BattleLogger::add("Step Aside сработал!");
            } else {
                \Mordheim\BattleLogger::add("Step Aside не сработал.");
            }
        }
        if ($parried) {
            \Mordheim\BattleLogger::add("Атака парирована!");
            return false;
        }
        if ($hitRoll < $toHit) {
            \Mordheim\BattleLogger::add("Промах!");
            return false;
        }
// 2. Roll to wound (S vs T)
        $attackerS = $this->characteristics->strength + ($weapon ? $weapon->strength : 0);
        $defenderT = $target->characteristics->toughness;
        $resilientMod = $this->equipmentManager->getResilientModifier($target);
        $attackerS -= $resilientMod;
        $toWound = 4;
        if ($attackerS > $defenderT) $toWound = 3;
        if ($attackerS >= 2 * $defenderT) $toWound = 2;
        if ($attackerS < $defenderT) $toWound = 5;
        if ($attackerS * 2 <= $defenderT) $toWound = 6;
        \Mordheim\BattleLogger::add("Сила атакующего: $attackerS, Стойкость защищающегося: $defenderT, модификатор Resilient: $resilientMod, итоговое значение для ранения: $toWound+");
        $woundRoll = \Mordheim\Dice::roll(6);
        \Mordheim\BattleLogger::add("{$this->name} бросает на ранение: $woundRoll (нужно $toWound+)");
        if ($woundRoll < $toWound) {
            \Mordheim\BattleLogger::add("Ранение не удалось!");
            return false;
        }
        $armorSave = $target->getArmorSave($weapon);
        $armorSaveMod = $this->equipmentManager->getArmorSaveModifier($weapon);
        $armorSave += $armorSaveMod;
        \Mordheim\BattleLogger::add("Сэйв защищающегося: $armorSave (модификатор: $armorSaveMod)");
        if ($armorSave > 0) {
            $saveRoll = \Mordheim\Dice::roll(6);
            \Mordheim\BattleLogger::add("{$target->name} бросает на сэйв: $saveRoll (нужно $armorSave+)");
            if ($saveRoll >= $armorSave) {
                \Mordheim\BattleLogger::add("Сэйв удался! Урон не нанесён.");
                return false;
            } else {
                \Mordheim\BattleLogger::add("Сэйв не удался.");
            }
        }
        $injuryMod = $this->equipmentManager->getInjuryModifier($weapon);
        $specialRules = $weapon ? $weapon->specialRules : [];
// Critical: если woundRoll==6, всегда крит (для совместимости с тестом)
        $isCritical = isset($woundRoll) && $woundRoll == 6;
        if ($isCritical) {
            \Mordheim\BattleLogger::add("Критическое ранение!");
            $injuryRoll = \Mordheim\Dice::roll(6);
            $target->rollInjury('CRITICAL', $injuryMod, $specialRules, $injuryRoll);
        } else {
            $needsInjury = false;
            if ($weapon && ($weapon->hasRule(\Mordheim\SpecialRule::CLUB) || $weapon->hasRule(\Mordheim\SpecialRule::CONCUSSION))) {
                $needsInjury = true;
                \Mordheim\BattleLogger::add("Особое правило: дубина/конкашн — всегда injury table");
            } else if ($target->characteristics->wounds > 1) {
                $target->characteristics->wounds -= 1;
                \Mordheim\BattleLogger::add("У {$target->name} осталось {$target->characteristics->wounds} ран(а/ий)");
                if ($target->characteristics->wounds <= 0) {
                    $needsInjury = true;
                }
            } else {
                $needsInjury = true;
            }
            if ($needsInjury) {
                $injuryRoll = \Mordheim\Dice::roll(6);
                \Mordheim\BattleLogger::add("Бросок на травму: $injuryRoll (модификатор: $injuryMod)");
                $target->rollInjury($weapon ? $weapon->name : '', $injuryMod, $specialRules, $injuryRoll);
            }
        }
        return true;
    }

    /**
     * Таблица ранений Mordheim: определяет состояние бойца (knocked down, stunned, out of action)
     * $special — название оружия (может влиять на результат)
     * @param string $special
     * @param int $injuryMod
     * @param SpecialRule[] $specialRules
     * @param int|null $injuryRoll
     */
    public function rollInjury(string $special = '', int $injuryMod = 0, array $specialRules = [], int $injuryRoll = null): void
    {
        if ($injuryRoll === null) {
            $roll = \Mordheim\Dice::roll(6) + $injuryMod;
        } else {
            $roll = $injuryRoll + $injuryMod;
        }
        if ($roll < 1) $roll = 1;
        if ($roll > 6) $roll = 6;
        // Critical: если woundRoll=6 и есть Critical, сразу OutOfAction
        if (in_array(\Mordheim\SpecialRule::CRITICAL, $specialRules, true) && $special === 'CRITICAL') {
            $this->state = FighterState::OUT_OF_ACTION;
            $this->alive = false;
            return;
        }
        // Club/Mace/Hammer/Concussion: 1 — выбыл, 2 — knockdown, 3-6 — stun
        if (in_array(\Mordheim\SpecialRule::CLUB, $specialRules, true) || in_array(\Mordheim\SpecialRule::CONCUSSION, $specialRules, true)) {
            if ($roll == 1) {
                $this->state = FighterState::OUT_OF_ACTION;
                $this->alive = false;
            } elseif ($roll == 2) {
                $this->state = FighterState::KNOCKED_DOWN;
            } else {
                $this->state = $this->tryAvoidStun() ? FighterState::KNOCKED_DOWN : FighterState::STUNNED;
            }
            return;
        }
        // Обычная таблица
        if ($roll == 1 || $roll == 2) {
            $this->state = FighterState::KNOCKED_DOWN;
        } elseif ($roll == 3 || $roll == 4 || $roll == 5) {
            $this->state = $this->tryAvoidStun() ? FighterState::KNOCKED_DOWN : FighterState::STUNNED;
        } else {
            $this->state = FighterState::OUT_OF_ACTION;
            $this->alive = false;
        }
    }

    /**
     * Стрельба по другому бойцу по правилам Mordheim
     * Учитывает Ballistic Skill, модификаторы (дальность, движение, укрытие, размер цели и т.д.)
     */
    /**
     * Стрельба по правилам Mordheim с учётом спецправил, навыков, критических попаданий
     */
    public function shoot(Fighter $target, bool $moved = false, bool $targetInCover = false, bool $targetIsLarge = false, int $shots = 1): bool
    {
        if (!$this->alive || !$target->alive) return false;
        $ranged = null;
        foreach ($this->equipmentManager->getWeapons() as $w) {
            if ($w->damageType === 'Ranged') {
                $ranged = $w;
                break;
            }
        }
        if (!$ranged) return false;
        if ($this->distance($target) > $ranged->range) return false;
        // Move Or Fire: если оружие содержит спецправило, нельзя стрелять после движения
        if ($moved && $ranged->hasRule(\Mordheim\SpecialRule::MOVE_OR_FIRE)) {
            \Mordheim\BattleLogger::add("{$this->name} не может стрелять из {$ranged->name} после движения (Move or Fire).");
            return false;
        }

        $bs = $this->characteristics->ballisticSkill;
        // Mordheim: 2=6+, 3=5+, 4=4+, 5=3+, 6=2+
        $toHitBase = 7 - $bs;
        if ($toHitBase < 2) $toHitBase = 2;
        if ($toHitBase > 6) $toHitBase = 6;

        // Модификаторы
        $mod = 0;
        // Дальний выстрел (больше половины дистанции)
        if ($this->distance($target) > $ranged->range / 2) $mod += 1;
        // Двигался
        if ($moved) $mod += 1;
        // Цель в укрытии
        if ($targetInCover) $mod += 1;
        // Множественный выстрел
        if ($shots > 1) $mod += 1;
        // Большая цель
        if ($targetIsLarge) $mod -= 1;
        // Модификатор оружия
        $mod += $ranged->toHitModifier;

        $toHit = $toHitBase + $mod;
        if ($toHit > 6) $toHit = 6;
        if ($toHit < 2) $toHit = 2;

        $hit = false;
        for ($i = 0; $i < $shots; $i++) {
            $roll = \Mordheim\Dice::roll(6);
            // Критическое попадание: 6 на попадание — автоматическое ранение, сейв невозможен
            if ($roll == 6) {
                $target->characteristics->wounds -= 1;
                if ($target->characteristics->wounds <= 0) {
                    $target->alive = false;
                }
                $hit = true;
                continue;
            }
            if ($roll >= $toHit) {
                // Навык Dodge: 5+ save против стрельбы
                $hasDodge = $target->hasSkill('Dodge');
                if ($hasDodge && \Mordheim\Dice::roll(6) >= 5) continue; // уклонился

                // Особые эффекты оружия: игнор сейва/повтор броска
                $ignoreSave = $ranged->hasRule(\Mordheim\SpecialRule::IGNORE_ARMOR_SAVE);

                $armorSave = $ignoreSave ? 0 : $target->getArmorSave($ranged);
                $saveRoll = $armorSave > 0 ? \Mordheim\Dice::roll(6) : 7;
                if ($saveRoll < $armorSave) {
                    $target->characteristics->wounds -= 1;
                    if ($target->characteristics->wounds <= 0) {
                        $target->alive = false;
                    }
                    $hit = true;
                }
            }
            // Навык Quick Shot: если есть, стреляет дважды, если не двигался
            $hasQuickShot = $this->hasSkill('Quick Shot');
            if ($hasQuickShot && $shots == 1 && !$moved) {
                $shots = 2;
            }
        }
        return $hit;
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


