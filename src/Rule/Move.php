<?php

namespace Mordheim\Rule;

use Mordheim\Battle;
use Mordheim\Exceptions\MoveRunDeprecatedException;
use Mordheim\Exceptions\PathfinderTargetUnreachableException;
use Mordheim\FighterInterface;
use Mordheim\Ruler;

class Move
{
    /**
     * Продвинутое движение по правилам Mordheim: поверхность, труднопроходимость, опасность, прыжки, вода, лестницы, высота
     * Возвращает подробный лог хода
     * @param Battle $battle
     * @param FighterInterface $fighter
     * @param array $target
     * @param float $aggressiveness
     * @return void
     */
    public static function common(Battle $battle, FighterInterface $fighter, array $target, float $aggressiveness): void
    {
        $blockers = self::prepareBlockers($battle, $fighter);
        $movePoints = $fighter->getMoveRange();
        [$path, $lastReachableIdx] = self::findPathAndReachableIndex($battle, $fighter, $target, $aggressiveness, $blockers, $movePoints);
        self::moveAlongPath($battle, $fighter, $path, $lastReachableIdx);
    }

    /**
     * Бег по правилам Mordheim 1999: удвоенное движение, нельзя бежать если в начале хода есть враг в 8" (20.32 см), нельзя бежать по воде
     * @param Battle $battle
     * @param FighterInterface $fighter
     * @param array $target
     * @param float $aggressiveness
     * @return void
     * @throws PathfinderTargetUnreachableException
     * @throws MoveRunDeprecatedException
     */
    public static function run(Battle $battle, FighterInterface $fighter, array $target, float $aggressiveness): void
    {
        foreach ($battle->getEnemiesFor($fighter) as $enemy) {
            if (Ruler::distance($fighter->getState()->getPosition(), $enemy->getState()->getPosition()) < 8) { // 8 клеток = 8"
                \Mordheim\BattleLogger::add("{$fighter->getName()} не может бежать: враг слишком близко (меньше 8\")");
                throw new MoveRunDeprecatedException();
            }
        }
        $blockers = self::prepareBlockers($battle, $fighter);
        $movePoints = $fighter->getRunRange();
        \Mordheim\BattleLogger::add("{$fighter->getName()}: runPoints = $movePoints (run range)");
        [$path, $lastReachableIdx] = self::findPathAndReachableIndex($battle, $fighter, $target, $aggressiveness, $blockers, $movePoints);
        // Проверяем, есть ли на пути вода (бег по воде невозможен)
        for ($i = 1; $i <= $lastReachableIdx; $i++) {
            [$x, $y, $z] = $path[$i]['pos'];
            $cell = $battle->getField()->getCell($x, $y, $z);
            if ($cell->water) {
                \Mordheim\BattleLogger::add("{$fighter->getName()} не может бежать: на пути есть вода (клетка $x,$y,$z)");
                // Останавливаемся на предыдущей клетке
                $lastReachableIdx = $i - 1;
                break;
            }
        }
        self::moveAlongPath($battle, $fighter, $path, $lastReachableIdx, true);
    }

    // --- Общие защищённые методы ---

    /**
     * Подготовка массива блокирующих позиций
     */
    protected static function prepareBlockers(Battle $battle, FighterInterface $fighter): array
    {
        return array_filter(
            array_map(
                fn(FighterInterface $fighter) => $fighter->getState()->getStatus()->isAlive() ? $fighter->getState()->getPosition() : null,
                $battle->getFighters()
            )
        );
    }

    /**
     * Поиск пути и определение индекса достижимой точки
     */
    protected static function findPathAndReachableIndex(Battle $battle, FighterInterface $fighter, array $target, float $aggressiveness, array $blockers, int $movePoints): array
    {
        $path = \Mordheim\PathFinder::findPath($battle->getField(), $fighter->getState()->getPosition(), $target, $fighter->getMovementWeights(), $aggressiveness, $blockers);
        if (!$path || count($path) < 2)
            throw new PathfinderTargetUnreachableException();
        $lastReachableIdx = 0;
        for ($i = 1; $i < count($path); $i++) {
            if ($path[$i]['cost'] > $movePoints + 1e-6) break;
            $lastReachableIdx = $i;
        }
        return [$path, $lastReachableIdx];
    }

    /**
     * Движение по пути до lastReachableIdx (включительно)
     * Если reachedTarget == true, то это бег/движение до максимума, иначе обычное движение
     */
    protected static function moveAlongPath(Battle $battle, FighterInterface $fighter, array $path, int $lastReachableIdx, bool $run = false): void
    {
        $cur = $fighter->getState()->getPosition();
        $movePoints = $run ? $fighter->getRunRange() : $fighter->getMoveRange();
        for ($i = 1; $i <= $lastReachableIdx && $movePoints > 0; $i++) {
            [$x, $y, $z] = $path[$i]['pos'];
            if (!is_int($x) || !is_int($y) || !is_int($z)) {
                throw new \Exception('Некорректные координаты в пути: ' . var_export($path[$i]['pos'], true));
            }
            $cell = $battle->getField()->getCell($x, $y, $z);
            $fromCell = $battle->getField()->getCell($cur[0], $cur[1], $cur[2]);
            $cost = 1;
            $desc = "";
            // Обработка особенностей клетки
            self::processCellFeatures($battle, $fighter, $cell, $fromCell, $x, $y, $z, $cur, $cost, $desc);
            if ($cost > $movePoints) {
                \Mordheim\BattleLogger::add("Недостаточно очков движения для клетки ($x,$y,$z)");
                break;
            }
            $movePoints -= $cost;
            \Mordheim\BattleLogger::add("{$fighter->getName()} перемещается с [" . implode(',', $cur) . "] на [" . implode(',', [$x, $y, $z]) . "]" . ($run ? " (бег)" : "") . ": $desc Осталось ОД: $movePoints");
            $cur = [$x, $y, $z];
        }
        $fighter->getState()->setPosition($cur);
        \Mordheim\BattleLogger::add("Движение завершено. Итоговая позиция: (" . implode(",", $cur) . ")");
    }

    /**
     * Обработка особенностей клетки (вода, труднопроходимость, опасность, прыжки, лестницы, лазание)
     */
    protected static function processCellFeatures(Battle $battle, FighterInterface $fighter, $cell, $fromCell, $x, $y, $z, $cur, &$cost, &$desc): void
    {
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
                throw (new \Mordheim\Exceptions\MoveInitiativeRollFailedException())->setField($cell);
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
                throw (new \Mordheim\Exceptions\MoveInitiativeRollFailedException())->setField($cell);
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
                throw (new \Mordheim\Exceptions\MoveInitiativeRollFailedException())->setField($cell);
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
                    throw (new \Mordheim\Exceptions\MoveInitiativeRollFailedException())->setField($cell);
                }
            }
        }
    }
}