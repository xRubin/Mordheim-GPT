<?php

namespace Mordheim\Classic\Rule;

use Mordheim\BattleLogger;
use Mordheim\Classic\Battle;
use Mordheim\Classic\CloseCombat;
use Mordheim\Classic\Exceptions\ChargeFailedException;
use Mordheim\Classic\Exceptions\MoveInitiativeRollFailedException;
use Mordheim\Classic\Exceptions\MoveRunDeprecatedException;
use Mordheim\Classic\Exceptions\PathfinderTargetUnreachableException;
use Mordheim\Classic\Fighter;
use Mordheim\Classic\PathFinder;
use Mordheim\Classic\Ruler;
use Mordheim\Dice;

class Move
{
    /**
     * Продвинутое движение по правилам Mordheim: поверхность, труднопроходимость, опасность, прыжки, вода, лестницы, высота
     * Возвращает подробный лог хода
     * @param Battle $battle
     * @param Fighter $fighter
     * @param array $target
     * @param float $aggressiveness
     * @return void
     * @throws PathfinderTargetUnreachableException
     */
    public static function common(Battle $battle, Fighter $fighter, array $target, float $aggressiveness): void
    {
        $blockers = self::prepareBlockers($battle, $fighter);
        $movePoints = $fighter->getMoveRange();
        [$path, $lastReachableIdx] = self::findPathAndReachableIndex($battle, $fighter, $target, $aggressiveness, $blockers, $movePoints);
        self::moveAlongPath($battle, $fighter, $path, $lastReachableIdx, $movePoints);
    }

    /**
     * нельзя бежать если в начале хода есть враг в 8" (20.32 см),
     * @param Battle $battle
     * @param Fighter $fighter
     * @param array $target
     * @param float $aggressiveness
     * @return void
     * @throws MoveRunDeprecatedException
     * @throws PathfinderTargetUnreachableException
     */
    public static function runIfNoEnemies(Battle $battle, Fighter $fighter, array $target, float $aggressiveness): void
    {
        foreach ($battle->getEnemiesFor($fighter) as $enemy) {
            if (!$enemy->getState()->getStatus()->canAct())
                continue;
            if (Ruler::distance($fighter, $enemy) < 8) { // 8 клеток = 8"
                BattleLogger::add("{$fighter->getName()} не может бежать: враг слишком близко (меньше 8\")");
                throw new MoveRunDeprecatedException();
            }
        }
        self::run($battle, $fighter, $target, $aggressiveness);
    }

    /**
     * Бег по правилам Mordheim 1999: удвоенное движение, нельзя бежать если в начале хода есть враг в 8" (20.32 см), нельзя бежать по воде
     * @param Battle $battle
     * @param Fighter $fighter
     * @param array $target
     * @param float $aggressiveness
     * @return void
     * @throws PathfinderTargetUnreachableException
     */
    public static function run(Battle $battle, Fighter $fighter, array $target, float $aggressiveness): void
    {
        $blockers = self::prepareBlockers($battle, $fighter);
        $movePoints = $fighter->getRunRange();
        BattleLogger::add("{$fighter->getName()}: runPoints = $movePoints (run range)");
        [$path, $lastReachableIdx] = self::findPathAndReachableIndex($battle, $fighter, $target, $aggressiveness, $blockers, $movePoints);
        // Проверяем, есть ли на пути вода (бег по воде невозможен)
        for ($i = 1; $i <= $lastReachableIdx; $i++) {
            [$x, $y, $z] = $path[$i]['pos'];
            $cell = $battle->getField()->getCell($x, $y, $z);
            if ($cell->water) {
                BattleLogger::add("{$fighter->getName()} не может бежать: на пути есть вода (клетка $x,$y,$z)");
                // Останавливаемся на предыдущей клетке
                $lastReachableIdx = $i - 1;
                break;
            }
        }
        self::moveAlongPath($battle, $fighter, $path, $lastReachableIdx, $movePoints);
    }

    /**
     * Выполнить попытку charge (атаки с разбега) по правилам Mordheim 1999
     * Возвращает объект CloseCombat при успехе
     * @param Battle $battle
     * @param Fighter $attacker
     * @param Fighter $defender
     * @param float $aggressiveness
     * @return CloseCombat|null
     * @throws ChargeFailedException
     */
    public static function charge(Battle $battle, Fighter $attacker, Fighter $defender, float $aggressiveness): ?CloseCombat
    {
        // Charge запрещён, если атакующий уже вовлечён в ближний бой
        if ($battle->getActiveCombats()->isFighterInCombat($attacker)) {
            BattleLogger::add("{$attacker->getName()} не может объявить charge: уже вовлечён в ближний бой.");
            throw new ChargeFailedException();
        }

        $targetPos = self::getNearestChargePosition($battle, $attacker, $defender);
        if (null === $targetPos) {
            BattleLogger::add("{$attacker->getName()} не может совершить charge: зона вокруг с целью заблокирована.");
            throw new ChargeFailedException();
        }

        if ($battle->hasObstacleBetween($attacker->getState()->getPosition(), $defender->getState()->getPosition())) {
            if (Ruler::distance($attacker, $defender) > 4)
                throw new ChargeFailedException();
            // Проверка инициативы для скрытой цели
            $roll = Dice::roll(6);
            BattleLogger::add("{$attacker->getName()} бросает Initiative для hidden цели: $roll против {$defender->getInitiative()}");
            if ($roll <= $defender->getInitiative()) {
                BattleLogger::add("{$attacker->getName()} не может совершить charge: не прошёл проверку инициативы для атаки на скрытую цель.");
                throw new ChargeFailedException();
            }
            BattleLogger::add("{$attacker->getName()} прошёл проверку инициативы для атаки на скрытую цель.");
        }

        // Проверить, хватает ли движения
        $movePoints = $attacker->getChargeRange();
        $blockers = Move::prepareBlockers($battle, $attacker);
        try {
            [$path, $lastReachableIdx] = Move::findPathAndReachableIndex($battle, $attacker, $targetPos, $aggressiveness, $blockers, $movePoints);
        } catch (PathfinderTargetUnreachableException $e) {
            BattleLogger::add("{$attacker->getName()} не может совершить charge: путь к цели заблокирован.");
            throw new ChargeFailedException();
        }
        $cost = $path[count($path) - 1]['cost'];
        if ($cost > $movePoints) {
            BattleLogger::add("{$attacker->getName()} не может совершить charge: не хватает движения (нужно $cost, есть $movePoints).");
            throw new ChargeFailedException();
        }

        // Переместить бойца
        BattleLogger::add("{$attacker->getName()} совершает charge на {$defender->getName()}! Перемещение на [" . implode(',', $targetPos) . "]");
        Move::moveAlongPath($battle, $attacker, $path, $lastReachableIdx, $movePoints);
        return new CloseCombat($attacker, $defender);
    }

    /**
     * @param Battle $battle
     * @param Fighter $attacker
     * @param Fighter $defender
     * @return array|null
     */
    public static function getNearestChargePosition(Battle $battle, Fighter $attacker, Fighter $defender): ?array
    {
        // Определить клетки adjacent к цели
        $adjacent = self::getAdjacentPositions($battle, $defender->getState()->getPosition());
        $minDist = INF;
        $targetPos = null;
        foreach ($adjacent as $pos) {
            $dist = Ruler::distance($attacker, $pos);
            if ($dist < $minDist) {
                $minDist = $dist;
                $targetPos = $pos;
            }
        }
        return $targetPos;
    }

    /**
     * Получить список позиций, смежных с данной
     */
    public static function getAdjacentPositions(Battle $battle, array $position): array
    {
        $fightersPos = array_values(
            array_filter(
                $battle->getFighters(),
                fn(Fighter $fighter) => $fighter->getState()->getStatus()->isAlive() ? $fighter->getState()->getPosition() : null
            )
        );

        $adj = [];
        for ($dx = -1; $dx <= 1; $dx++) {
            for ($dy = -1; $dy <= 1; $dy++) {
                if ($dx === 0 && $dy === 0) continue;
                $cell = $battle->getField()->getCell($position[0] + $dx, $position[1] + $dy, $position[2]);
                if (null === $cell)
                    continue;
                if ($cell->obstacle) continue;
                if (in_array([$position[0] + $dx, $position[1] + $dy, $position[2]], $fightersPos)) continue;
                $adj[] = [$position[0] + $dx, $position[1] + $dy, $position[2]];
            }
        }
        return $adj;
    }

    /**
     * Подготовка массива блокирующих позиций
     */
    public static function prepareBlockers(Battle $battle, Fighter $attacker): array
    {
        return array_filter(
            array_map(
                function (Fighter $fighter) use ($attacker) {
                    if ($fighter === $attacker)
                        return null;
                    if (!$fighter->getState()->getStatus()->isAlive())
                        return null;
                    return $fighter->getState()->getPosition();
                },
                $battle->getFighters()
            )
        );
    }

    /**
     * Поиск пути и определение индекса достижимой точки
     * @throws PathfinderTargetUnreachableException
     */
    public static function findPathAndReachableIndex(Battle $battle, Fighter $fighter, array $target, float $aggressiveness, array $blockers, int $movePoints): array
    {
        $path = PathFinder::findPath($battle->getField(), $fighter->getState()->getPosition(), $target, $fighter->getMovementWeights(), $aggressiveness, $blockers);
        if (!$path || count($path) < 2) {
            $error = new PathfinderTargetUnreachableException();
            $error->setPosition($fighter->getState()->getPosition());
            $error->setTarget($target);
            throw $error;
        }
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
    public static function moveAlongPath(Battle $battle, Fighter $fighter, array $path, int $lastReachableIdx, int $movePoints): void
    {
        $cur = $fighter->getState()->getPosition();
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
                BattleLogger::add("Недостаточно очков движения для клетки ($x,$y,$z)");
                break;
            }
            $movePoints -= $cost;
            BattleLogger::add("{$fighter->getName()} перемещается с [" . implode(',', $cur) . "] на [" . implode(',', [$x, $y, $z]) . "]: $desc Осталось ОД: $movePoints");
            $cur = [$x, $y, $z];
        }
        $fighter->getState()->setPosition($cur);
        BattleLogger::add("Движение завершено. Итоговая позиция: (" . implode(",", $cur) . ")");
    }

    /**
     * Обработка особенностей клетки (вода, труднопроходимость, опасность, прыжки, лестницы, лазание)
     */
    protected static function processCellFeatures(Battle $battle, Fighter $fighter, $cell, $fromCell, $x, $y, $z, $cur, &$cost, &$desc): void
    {
        // Difficult terrain: double cost
        if ($cell->difficultTerrain) {
            $cost = 2;
            $desc .= "Труднопроходимая местность. ";
        }
        // Water: must swim, can't run, test Initiative or stop
        if ($cell->water) {
            $desc .= "Вода: требуется тест Initiative. ";
            $roll = Dice::roll(6);
            BattleLogger::add("{$fighter->getName()} бросает Initiative для воды: $roll против {$fighter->getInitiative()}");
            if ($roll > $fighter->getInitiative()) {
                $fighter->getState()->setPosition([$x, $y, $z]);
                BattleLogger::add("Провал Initiative в воде — движение остановлено на клетке ($x,$y,$z)");
                throw (new MoveInitiativeRollFailedException())->setField($cell);
            }
            $cost = 2;
        }
        // Dangerous terrain: test Initiative or fall
        if ($cell->dangerousTerrain) {
            $desc .= "Опасная местность: тест Initiative. ";
            $roll = Dice::roll(6);
            BattleLogger::add("{$fighter->getName()} бросает Initiative на опасной местности: $roll против {$fighter->getInitiative()}");
            if ($roll > $fighter->getInitiative()) {
                $fighter->getState()->setPosition([$x, $y, $z]);
                BattleLogger::add("Провал Initiative на опасной клетке ($x,$y,$z) — юнит упал");
                throw (new MoveInitiativeRollFailedException())->setField($cell);
            }
        }
        // Прыжок через разрыв: если разница высот > 1
        if (abs($cell->height - $fromCell->height) > 1) {
            $desc .= "Прыжок: тест Initiative. ";
            $roll = Dice::roll(6);
            BattleLogger::add("{$fighter->getName()} бросает Initiative для прыжка: $roll против {$fighter->getInitiative()}");
            if ($roll > $fighter->getInitiative()) {
                $fighter->getState()->setPosition([$x, $y, $z]);
                BattleLogger::add("Провал Initiative при прыжке — юнит падает на ({$x},{$y},{$z})");
                throw (new MoveInitiativeRollFailedException())->setField($cell);
            }
        }
        // Лестница: можно двигаться по вертикали
        if ($cell->ladder || $fromCell->ladder) {
            $desc .= "Лестница: разрешено движение по вертикали. ";
        } else {
            if ($z > $cur[2] && abs($x - $cur[0]) + abs($y - $cur[1]) == 1) {
                $desc .= "Лазание: тест Initiative. ";
                $roll = Dice::roll(6);
                BattleLogger::add("{$fighter->getName()} бросает Initiative для лазания: $roll против {$fighter->getClimbInitiative()}");
                if ($roll > $fighter->getClimbInitiative()) {
                    $fighter->getState()->setPosition([$x, $y, $z]);
                    BattleLogger::add("Провал Initiative при прыжке — юнит падает на ({$x},{$y},{$z})");
                    throw (new MoveInitiativeRollFailedException())->setField($cell);
                }
            }
        }
    }
}