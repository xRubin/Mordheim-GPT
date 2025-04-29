<?php

namespace Mordheim\Rule;

use Mordheim\Exceptions\PathfinderInitiativeRollFailedException;
use Mordheim\Exceptions\PathfinderTargetUnreachableException;
use Mordheim\Fighter;

class Move
{
    /**
     * Продвинутое движение по правилам Mordheim: поверхность, труднопроходимость, опасность, прыжки, вода, лестницы, высота
     * Возвращает подробный лог хода
     * @param \Mordheim\GameField $field
     * @param Fighter $fighter
     * @param array $target
     * @param array $otherUnits
     * @param bool $partialMove Если true — двигаться максимально в направлении цели, даже если не хватает очков движения
     * @return void
     */
    public static function apply(\Mordheim\GameField $field, Fighter $fighter, array $target, array $otherUnits = [], bool $partialMove = false): void
    {
        $blockers = [];
        foreach ($otherUnits as $unit) {
            if ($unit !== $fighter && $unit->alive) {
                $blockers[] = $unit->position;
            }
        }
        $movePoints = $fighter->characteristics->movement;
        $sprintBonus = 0;
        // Sprint: +D6 к движению при беге (если partialMove == false)
        if ($fighter->hasSkill('Sprint') && !$partialMove) {
            $sprintBonus = \Mordheim\Dice::roll(6);
            \Mordheim\BattleLogger::add("{$fighter->name} использует Sprint: бонус к движению = $sprintBonus");
            $movePoints += $sprintBonus;
        }
        \Mordheim\BattleLogger::add("{$fighter->name}: movePoints = $movePoints (base: {$fighter->characteristics->movement}, sprintBonus: $sprintBonus)");
        // Получаем полный путь до цели
        $path = \Mordheim\PathFinder::findPath($field, $fighter->position, $target, $fighter->getMovementWeights(), $blockers);
        if (!$path || count($path) < 2)
            throw new PathfinderTargetUnreachableException();

        \Mordheim\BattleLogger::add("{$fighter->name}: путь до цели: " . json_encode(array_map(fn($p) => $p['pos'], $path)));

        // Определяем, куда реально можем дойти по накопленной стоимости пути
        $lastReachableIdx = 0;
        for ($i = 1; $i < count($path); $i++) {
            if ($path[$i]['cost'] > $movePoints + 1e-6) break; // допускаем погрешность для float
            $lastReachableIdx = $i;
        }
        // Если можем дойти до цели полностью
        if ($lastReachableIdx === count($path) - 1) {
            // обычная логика движения (до цели)
            $from = $fighter->position;
            for ($i = 1; $i < count($path); $i++) {
                $to = $path[$i]['pos'];
                \Mordheim\BattleLogger::add("{$fighter->name} перемещается с [" . implode(',', $from) . "] на [" . implode(',', $to) . "]");
                $from = $to;
            }
            $fighter->position = $from;
        } elseif ($lastReachableIdx > 0) {
            // Двигаемся максимально далеко по пути (даже если partialMove == false)
            $from = $fighter->position;
            for ($i = 1; $i <= $lastReachableIdx; $i++) {
                $to = $path[$i]['pos'];
                \Mordheim\BattleLogger::add("{$fighter->name} перемещается с [" . implode(',', $from) . "] на [" . implode(',', $to) . "] (максимальное движение)");
                $from = $to;
            }
            $fighter->position = $from;
            \Mordheim\BattleLogger::add('Двигаемся максимально в сторону цели, но цель недостижима за ход. Новая позиция: (' . implode(',', $from) . ')');
            return;
        } else
            throw new PathfinderTargetUnreachableException();

        $cur = $fighter->position;
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
                \Mordheim\BattleLogger::add("{$fighter->name} бросает Initiative для воды: $roll против {$fighter->getInitiative()}");
                if ($roll > $fighter->getInitiative()) {
                    $fighter->position = [$x, $y, $z];
                    \Mordheim\BattleLogger::add("Провал Initiative в воде — движение остановлено на клетке ($x,$y,$z)");
                    throw (new PathfinderInitiativeRollFailedException())->setField($cell);
                }
                $cost = 2;
            }
            // Dangerous terrain: test Initiative or fall
            if ($cell->dangerousTerrain) {
                $desc .= "Опасная местность: тест Initiative. ";
                $roll = \Mordheim\Dice::roll(6);
                \Mordheim\BattleLogger::add("{$fighter->name} бросает Initiative на опасной местности: $roll против {$fighter->getInitiative()}");
                if ($roll > $fighter->getInitiative()) {
                    $fighter->position = [$x, $y, $z];
                    \Mordheim\BattleLogger::add("Провал Initiative на опасной клетке ($x,$y,$z) — юнит упал");
                    throw (new PathfinderInitiativeRollFailedException())->setField($cell);
                }
            }
            // Прыжок через разрыв: если разница высот > 1
            if (abs($cell->height - $fromCell->height) > 1) {
                $desc .= "Прыжок: тест Initiative. ";
                $roll = \Mordheim\Dice::roll(6);
                \Mordheim\BattleLogger::add("{$fighter->name} бросает Initiative для прыжка: $roll против {$fighter->getInitiative()}");
                if ($roll > $fighter->getInitiative()) {
                    $fighter->position = [$x, $y, $z];
                    \Mordheim\BattleLogger::add("Провал Initiative при прыжке — юнит падает на ({$x},{$y},{$z})");
                    throw (new PathfinderInitiativeRollFailedException())->setField($cell);
                }
            }
            // Лестница: можно двигаться по вертикали
            if ($cell->ladder || $fromCell->ladder) {
                $desc .= "Лестница: разрешено движение по вертикали. ";
            }
            if ($cost > $movePoints) {
                \Mordheim\BattleLogger::add("Недостаточно очков движения для клетки ($x,$y,$z)");
                break;
            }
            $movePoints -= $cost;
            $cur = [$x, $y, $z];
            $stepsTaken++;
            \Mordheim\BattleLogger::add("Перемещён на ($x,$y,$z): $desc Осталось ОД: $movePoints");
        }
        $fighter->position = $cur;
        \Mordheim\BattleLogger::add("Движение завершено. Итоговая позиция: (" . implode(",", $cur) . ")");
    }
}