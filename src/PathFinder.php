<?php

namespace Mordheim;

class PathFinder
{
    /**
     * Проверяет возможность лазания
     * @param GameField $field
     * @param array $start [x,y,z]
     * @param array $goal [x,y,z]
     * @return bool
     */
    public static function canClimb(GameField $field, array $start, array $goal): bool
    {
        [$x, $y, $z] = $start;
        [$nx, $ny, $nz] = $goal;
        // Проверка координат
        if (!is_int($x) || !is_int($y) || !is_int($z) || !is_int($nx) || !is_int($ny) || !is_int($nz)) {
            throw new \Exception('Некорректные координаты в canClimb: ' . var_export([$x, $y, $z, $nx, $ny, $nz], true));
        }
        // Лазание только на строго соседнюю клетку по x или y (не по диагонали!), вверх на 1 уровень
        if (abs($nx - $x) + abs($ny - $y) != 1 || $nz - $z != 1) return false;
        // В целевой ячейке не должно быть препятствия
        if (!is_int($nx) || !is_int($ny) || !is_int($nz)) {
            throw new \Exception('Некорректные координаты в canClimb (целевой ячейки): ' . var_export([$nx, $ny, $nz], true));
        }
        if ($field->getCell($nx, $ny, $nz)->obstacle) return false;
        // Под целевой ячейкой должен быть obstacle=TRUE (явно выставлен)
        $cellBelow = $nz == 0 ? null : $field->getCell($nx, $ny, $nz - 1);
        if ($nz == 0 || !$cellBelow || !$cellBelow->obstacle) return false;
        // Строго: obstacle рядом с бойцом должен быть именно под целевой ячейкой
        if (!($x + 1 == $nx && $y == $ny && $field->isObstacle($x + 1, $y, $z))
            && !($x - 1 == $nx && $y == $ny && $field->isObstacle($x - 1, $y, $z))
            && !($y + 1 == $ny && $x == $nx && $field->isObstacle($x, $y + 1, $z))
            && !($y - 1 == $ny && $x == $nx && $field->isObstacle($x, $y - 1, $z)))
            return false;
        // В ячейке бойца не должно быть препятствия
        if ($field->isObstacle($x, $y, $z)) return false;
        // Новый критерий: под бойцом должен быть obstacle (то есть боец стоит на твёрдой поверхности)
        $cellBelowFighter = $z == 0 ? null : $field->getCell($x, $y, $z - 1);
        if ($z > 0 && (!$cellBelowFighter || !$cellBelowFighter->obstacle)) return false;
        return true;
    }

    /**
     * Проверяет возможность прыжка через пустое пространство
     * @param GameField $field
     * @param array $start [x,y,z]
     * @param array $goal [x,y,z]
     * @return bool false если прыжок невозможен
     */
    public static function canJumpOverGap(GameField $field, array $start, array $goal): bool
    {
        [$x, $y, $z] = $start;
        [$nx, $ny, $nz] = $goal;
        // Прыжок только между крышами одной высоты
        if ($z !== $nz) return false;
        // Прыжок только по одной из осей
        $dx = abs($nx - $x);
        $dy = abs($ny - $y);
        if ($dx === 0 && $dy === 0) return false;
        if ($dx !== 0 && $dy !== 0) return false;
        // В точке старта и приземления не должно быть препятствий
        if ($field->isObstacle($x, $y, $z)) return false;
        if ($field->isObstacle($nx, $ny, $nz)) return false;
        // Под стартом и под целью должны быть obstacle=TRUE (явно выставлен)
        $cellBelowStart = $z == 0 ? null : $field->getCell($x, $y, $z - 1);
        $cellBelowGoal = $nz == 0 ? null : $field->getCell($nx, $ny, $nz - 1);
        if ($z == 0 || !$cellBelowStart || !$cellBelowStart->obstacle) return false;
        if ($nz == 0 || !$cellBelowGoal || !$cellBelowGoal->obstacle) return false;
        // Между стартом и целью должна быть пустота
        if ($dx === 0) {
            for ($i = min($y, $ny); $i <= max($y, $ny); $i++)
                if ($field->isObstacle($x, $i, $z)) return false;
        }
        if ($dy === 0) {
            for ($i = min($x, $nx); $i <= max($x, $nx); $i++)
                if ($field->isObstacle($i, $y, $z)) return false;
        }
        return true;
    }

    /**
     * Проверяет возможность прыжка вниз
     * @param GameField $field
     * @param array $start [x,y,z]
     * @param array $goal [x,y,z]
     * @return bool
     */
    public static function canJumpUpDown(GameField $field, array $start, array $goal): bool
    {
        [$x, $y, $z] = $start;
        [$nx, $ny, $nz] = $goal;
        $heightDiff = $nz - $z;
        // Прыжки вверх запрещены
        if ($heightDiff >= 0) return false;
        // По правилам Mordheim
        if ($heightDiff <= -6) return false;
        // Прыжок только на соседнюю клетку по x или y (или по диагонали)
        if (abs($nx - $x) > 1 || abs($ny - $y) > 1) return false;
        // В точке старта и приземления не должно быть препятствий
        if ($field->getCell($x, $y, $z)->obstacle) return false;
        if ($field->getCell($nx, $ny, $nz)->obstacle) return false;
        // Под стартом должен быть obstacle (например, край крыши)
        if ($z == 0 || !$field->getCell($x, $y, $z - 1)->obstacle) return false;
        // Под целью должен быть obstacle, если nz > 0 (если nz==0 — земля, не проверяем)
        if ($nz > 0 && !$field->getCell($nx, $ny, $nz - 1)->obstacle) return false;
        // Промежуточные клетки между z и nz должны быть пустыми
        for ($i = $nz + 1; $i < $z; $i++) {
            if ($field->getCell($nx, $ny, $i)->obstacle) return false;
        }
        return true;
    }

    /**
     * Поиск пути по правилам Mordheim 1999 года
     * @param GameField $field
     * @param array $start [x,y,z]
     * @param array $goal [x,y,z]
     * @param callable $weights функция весов движения (from, to, dx, dy, dz) => float
     * @param float $aggressiveness агрессивность бойца (0-1), заменяет броски кубика
     * @param array $blockers массив позиций [[x,y,z], ...]
     * @return array|null путь в виде массива [['pos'=>[x,y,z], 'cost'=>float], ...] или null если нет пути
     */
    public static function findPath(GameField $field, array $start, array $goal, callable $weights, float $aggressiveness, array $blockers = []): ?array
    {
        $queue = new \SplPriorityQueue();
        $queue->insert([['pos' => $start, 'cost' => 0.0]], 0);
        $costSoFar = [];
        $costSoFar[implode(',', $start)] = 0.0;
        $blockSet = array_map(fn($b) => implode(',', $b), $blockers);
        $dirs = [
            [1, 0, 0], [-1, 0, 0], [0, 1, 0], [0, -1, 0],
            [1, 1, 0], [1, -1, 0], [-1, 1, 0], [-1, -1, 0],
            [0, 0, 1], [0, 0, -1],
        ];
        $iteration = 0;
        $maxIterations = 1000;
        while (!$queue->isEmpty()) {
            if (++$iteration > $maxIterations) return null;
            $path = $queue->extract();
            if (empty($path) || !is_array($path)) {
                throw new \Exception('Из очереди извлечён невалидный путь: ' . var_export($path, true));
            }
            $lastStep = end($path);
            $pos = $lastStep['pos'];
            if ($pos === $goal) return $path;
            [$x, $y, $z] = $pos;
            if (!is_int($x) || !is_int($y) || !is_int($z)) {
                throw new \Exception('Некорректные координаты в findPath (pos): ' . var_export($pos, true));
            }
            $currCost = $lastStep['cost'];
            foreach ($dirs as [$dx, $dy, $dz]) {
                $nx = $x + $dx;
                $ny = $y + $dy;
                $nz = $z + $dz;
                if (!is_int($nx) || !is_int($ny) || !is_int($nz)) {
                    throw new \Exception('Некорректные координаты в findPath (dirs): ' . var_export([$nx, $ny, $nz], true));
                }
                if ($field->isOutOfBounds($nx, $ny, $nz) || self::isBlocked($blockSet, [$nx, $ny, $nz])) continue;
                $cell = $field->getCell($nx, $ny, $nz);
                $fromCell = $field->getCell($x, $y, $z);
                if ($dz === 0 && $nz > $z) continue;
                if ($dz === 1) {
                    if ($dx !== 0 || $dy !== 0) continue;
                    if (!$fromCell->ladder || $cell->obstacle) continue;
                    $moveCost = $weights($fromCell, $cell, $dx, $dy, $dz);
                } else if ($dz === 0) {
                    if ($cell->obstacle) continue;
                    $moveCost = $weights($fromCell, $cell, $dx, $dy, $dz);
                } else if ($dz === -1) {
                    if ($fromCell->ladder && !$cell->obstacle) {
                        $moveCost = $weights($fromCell, $cell, $dx, $dy, $dz);
                    } else {
                        if (!self::canJumpUpDown($field, [$x, $y, $z], [$nx, $ny, $nz]) || $aggressiveness < 0.9) continue;
                        $moveCost = $weights($fromCell, $cell, $dx, $dy, $dz);
                    }
                } else {
                    continue;
                }
                $newCost = $currCost + $moveCost;
                $key = self::getCostKey([$nx, $ny, $nz]);
                if (isset($costSoFar[$key]) && $newCost >= $costSoFar[$key]) continue;
                $costSoFar[$key] = $newCost;
                // Проверка перед вставкой в очередь
                if (empty($path) || !is_array($path)) {
                    throw new \Exception('В очередь пытаются вставить невалидный путь: ' . var_export($path, true));
                }
                $queue->insert(array_merge($path, [[
                    'pos' => [$nx, $ny, $nz],
                    'cost' => $newCost
                ]]), -$newCost);
            }
            // climb и jump_gap можно вынести в отдельные методы для компактности, если потребуется
            // --- Добавляем обработку лазания ---
            // Проверяем все соседние клетки по x/y, вверх на 1 уровень
            $climbDirs = [[1, 0, 1], [-1, 0, 1], [0, 1, 1], [0, -1, 1]];
            foreach ($climbDirs as [$dx, $dy, $dz]) {
                $nx = $x + $dx;
                $ny = $y + $dy;
                $nz = $z + $dz;
                if (!is_int($nx) || !is_int($ny) || !is_int($nz)) {
                    throw new \Exception('Некорректные координаты в findPath (climbDirs): ' . var_export([$nx, $ny, $nz], true));
                }
                if ($field->isOutOfBounds($nx, $ny, $nz) || self::isBlocked($blockSet, [$nx, $ny, $nz])) continue;
                if (!self::canClimb($field, [$x, $y, $z], [$nx, $ny, $nz])) continue;
                $moveCost = $weights($field->getCell($x, $y, $z), $field->getCell($nx, $ny, $nz), $dx, $dy, $dz);
                $newCost = $currCost + $moveCost;
                $key = self::getCostKey([$nx, $ny, $nz]);
                if (isset($costSoFar[$key]) && $newCost >= $costSoFar[$key]) continue;
                $costSoFar[$key] = $newCost;
                // Проверка перед вставкой в очередь
                if (empty($path) || !is_array($path)) {
                    throw new \Exception('В очередь пытаются вставить невалидный путь (climb): ' . var_export($path, true));
                }
                $queue->insert(array_merge($path, [[
                    'pos' => [$nx, $ny, $nz],
                    'cost' => $newCost
                ]]), -$newCost);
            }
            // --- Добавляем обработку прыжков через пустое пространство (gap) ---
            // Перебираем возможные прыжки по x и y (только по одной оси, на том же уровне)
            $maxGap = $aggressiveness > 0.8 ? 3 : 2; // максимальная длина прыжка через gap (можно скорректировать)
            foreach ([[1, 0], [0, 1], [-1, 0], [0, -1]] as [$dx, $dy]) {
                for ($len = 2; $len <= $maxGap; $len++) {
                    $nx = $x + $dx * $len;
                    $ny = $y + $dy * $len;
                    $nz = $z;
                    if (!is_int($nx) || !is_int($ny) || !is_int($nz)) {
                        throw new \Exception('Некорректные координаты в findPath (jump_gap): ' . var_export([$nx, $ny, $nz], true));
                    }
                    if ($field->isOutOfBounds($nx, $ny, $nz) || self::isBlocked($blockSet, [$nx, $ny, $nz])) continue;
                    if (!self::canJumpOverGap($field, [$x, $y, $z], [$nx, $ny, $nz])) continue;
                    $moveCost = $weights($field->getCell($x, $y, $z), $field->getCell($nx, $ny, $nz), $dx * $len, $dy * $len, 0);
                    $newCost = $currCost + $moveCost;
                    $key = self::getCostKey([$nx, $ny, $nz]);
                    if (isset($costSoFar[$key]) && $newCost >= $costSoFar[$key]) continue;
                    $costSoFar[$key] = $newCost;
                    // Проверка перед вставкой в очередь
                    if (empty($path) || !is_array($path)) {
                        throw new \Exception('В очередь пытаются вставить невалидный путь (jump_gap): ' . var_export($path, true));
                    }
                    $queue->insert(array_merge($path, [[
                        'pos' => [$nx, $ny, $nz],
                        'cost' => $newCost
                    ]]), -$newCost);
                }
            }
        }
        return null;
    }

    /**
     * @param array $position [x,y,z]
     * @return string
     */
    private static function getCostKey(array $position): string
    {
        [$x, $y, $z] = $position;
        return "$x,$y,$z";
    }

    /**
     * @param array $blockSet
     * @param array $position [x,y,z]
     * @return bool
     */
    private static function isBlocked(array $blockSet, array $position): bool
    {
        return in_array(self::getCostKey($position), $blockSet);
    }
}
