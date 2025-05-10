<?php

namespace Mordheim\Rule;

use Mordheim\Battle;
use Mordheim\Exceptions\PathfinderInitiativeRollFailedException;
use Mordheim\Exceptions\PathfinderTargetUnreachableException;
use Mordheim\FighterInterface;
use Mordheim\SpecialRule;

class Move
{
    /**
     * Продвинутое движение по правилам Mordheim: поверхность, труднопроходимость, опасность, прыжки, вода, лестницы, высота
     * Возвращает подробный лог хода
     * @param Battle $battle
     * @param FighterInterface $fighter
     * @param array $target
     * @param float $aggressiveness
     * @param array $otherUnits
     * @param bool $partialMove Если true — двигаться максимально в направлении цели, даже если не хватает очков движения
     * @return void
     */
    public static function apply(Battle $battle, FighterInterface $fighter, array $target, float $aggressiveness, array $otherUnits = [], bool $partialMove = false): void
    {
        $blockers = [];
        foreach ($otherUnits as $unit) {
            if ($unit !== $fighter && $unit->alive) {
                $blockers[] = $unit->position;
            }
        }
        $movePoints = $fighter->getMovement();
        $sprintBonus = 0;
        // Sprint: +D6 к движению при беге (если partialMove == false)
        if ($fighter->hasSpecialRule(SpecialRule::SPRINT) && !$partialMove) {
            $sprintBonus = \Mordheim\Dice::roll(6);
            \Mordheim\BattleLogger::add("{$fighter->getName()} использует Sprint: бонус к движению = $sprintBonus");
            $movePoints += $sprintBonus;
        }
        \Mordheim\BattleLogger::add("{$fighter->getName()}: movePoints = $movePoints (base: {$fighter->getMovement()}, sprintBonus: $sprintBonus)");
        // Получаем полный путь до цели
        $path = \Mordheim\PathFinder::findPath($battle->getField(), $fighter->getState()->getPosition(), $target, $fighter->getMovementWeights(), $aggressiveness, $blockers);
        if (!$path || count($path) < 2)
            throw new PathfinderTargetUnreachableException();

        \Mordheim\BattleLogger::add("{$fighter->getName()}: путь до цели: " . json_encode(array_map(fn($p) => $p['pos'], $path)));

        // Определяем, куда реально можем дойти по накопленной стоимости пути
        $lastReachableIdx = 0;
        for ($i = 1; $i < count($path); $i++) {
            if ($path[$i]['cost'] > $movePoints + 1e-6) break; // допускаем погрешность для float
            $lastReachableIdx = $i;
        }
        // Если можем дойти до цели полностью
        if ($lastReachableIdx === count($path) - 1) {
            // обычная логика движения (до цели)
            $from = $fighter->getState()->getPosition();
            for ($i = 1; $i < count($path); $i++) {
                $to = $path[$i]['pos'];
                \Mordheim\BattleLogger::add("{$fighter->getName()} перемещается с [" . implode(',', $from) . "] на [" . implode(',', $to) . "]");
                $from = $to;
            }
            $fighter->getState()->setPosition($from);
        } elseif ($lastReachableIdx > 0) {
            // Двигаемся максимально далеко по пути (даже если partialMove == false)
            $from = $fighter->getState()->getPosition();
            for ($i = 1; $i <= $lastReachableIdx; $i++) {
                $to = $path[$i]['pos'];
                \Mordheim\BattleLogger::add("{$fighter->getName()} перемещается с [" . implode(',', $from) . "] на [" . implode(',', $to) . "] (максимальное движение)");
                $from = $to;
            }
            $fighter->getState()->setPosition($from);
            \Mordheim\BattleLogger::add('Двигаемся максимально в сторону цели, но цель недостижима за ход. Новая позиция: (' . implode(',', $from) . ')');
            return;
        } else
            throw new PathfinderTargetUnreachableException();

        $cur = $fighter->getState()->getPosition();
        $stepsTaken = 0;
        for ($i = 1; $i < count($path) && $movePoints > 0; $i++) {
            [$x, $y, $z] = $path[$i]['pos'];
            $cell = $battle->getField()->getCell($x, $y, $z);
            $fromCell = $battle->getField()->getCell($cur[0], $cur[1], $cur[2]);
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
                \Mordheim\BattleLogger::add("{$fighter->getName()} бросает Initiative для воды: $roll против {$fighter->getInitiative()}");
                if ($roll > $fighter->getInitiative()) {
                    $fighter->getState()->setPosition([$x, $y, $z]);
                    \Mordheim\BattleLogger::add("Провал Initiative в воде — движение остановлено на клетке ($x,$y,$z)");
                    throw (new PathfinderInitiativeRollFailedException())->setField($cell);
                }
                $cost = 2;
            }
            // Dangerous terrain: test Initiative or fall
            if ($cell->dangerousTerrain) {
                $desc .= "Опасная местность: тест Initiative. ";
                $roll = \Mordheim\Dice::roll(6);
                \Mordheim\BattleLogger::add("{$fighter->getName()} бросает Initiative на опасной местности: $roll против {$fighter->getInitiative()}");
                if ($roll > $fighter->getInitiative()) {
                    $fighter->getState()->setPosition([$x, $y, $z]);
                    \Mordheim\BattleLogger::add("Провал Initiative на опасной клетке ($x,$y,$z) — юнит упал");
                    throw (new PathfinderInitiativeRollFailedException())->setField($cell);
                }
            }
            // Прыжок через разрыв: если разница высот > 1
            if (abs($cell->height - $fromCell->height) > 1) {
                $desc .= "Прыжок: тест Initiative. ";
                $roll = \Mordheim\Dice::roll(6);
                \Mordheim\BattleLogger::add("{$fighter->getName()} бросает Initiative для прыжка: $roll против {$fighter->getInitiative()}");
                if ($roll > $fighter->getInitiative()) {
                    $fighter->getState()->setPosition([$x, $y, $z]);
                    \Mordheim\BattleLogger::add("Провал Initiative при прыжке — юнит падает на ({$x},{$y},{$z})");
                    throw (new PathfinderInitiativeRollFailedException())->setField($cell);
                }
            }
            // Лестница: можно двигаться по вертикали
            if ($cell->ladder || $fromCell->ladder) {
                $desc .= "Лестница: разрешено движение по вертикали. ";
            } else {
                if ($z > $cur[2] && abs($x - $cur[0]) + abs($y - $cur[1]) == 1) {
                    $desc .= "Лазание: тест Initiative. ";
                    $roll = \Mordheim\Dice::roll(6);
                    \Mordheim\BattleLogger::add("{$fighter->getName()} бросает Initiative для лазания: $roll против {$fighter->getClimbInitiative()}");
                    if ($roll > $fighter->getClimbInitiative()) {
                        $fighter->getState()->setPosition([$x, $y, $z]);
                        \Mordheim\BattleLogger::add("Провал Initiative при прыжке — юнит падает на ({$x},{$y},{$z})");
                        throw (new PathfinderInitiativeRollFailedException())->setField($cell);
                    }
                }
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
        $fighter->getState()->setPosition($cur);
        \Mordheim\BattleLogger::add("Движение завершено. Итоговая позиция: (" . implode(",", $cur) . ")");
    }
}