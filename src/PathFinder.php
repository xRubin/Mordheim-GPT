<?php
namespace Mordheim;

class PathFinder
{
    /**
     * Поиск пути с учетом стоимости переходов
     * @param GameField $field
     * @param array $start [x,y,z]
     * @param array $goal [x,y,z]
     * @param callable $weights функция весов движения (dx, dy, dz) => float
     * @param array $blockers массив позиций [[x,y,z], ...]
     * @return array|null путь в виде массива [['pos'=>[x,y,z], 'cost'=>float], ...] или null если нет пути
     */
    public static function findPath(GameField $field, array $start, array $goal, callable $weights, array $blockers = []): ?array
    {
        // Приоритетная очередь: массив из [стоимость, путь]
        $queue = new \SplPriorityQueue();
        // SplPriorityQueue извлекает элементы с наибольшим приоритетом, поэтому используем отрицательную стоимость
        $queue->insert([['pos'=>$start,'cost'=>0.0]], 0);
        $costSoFar = [];
        $costSoFar[implode(',', $start)] = 0.0;
        $blockSet = array_map(fn($b) => implode(',', $b), $blockers);
        // 8 направлений по горизонтали + вертикаль
        $dirs = [
            [1,0,0], [-1,0,0], [0,1,0], [0,-1,0],
            [1,1,0], [1,-1,0], [-1,1,0], [-1,-1,0], // диагонали
            [0,0,1], [0,0,-1] // этажи
        ];
        while (!$queue->isEmpty()) {
            $path = $queue->extract();
            $lastStep = end($path);
            $pos = $lastStep['pos'];
            $currKey = implode(',', $pos);
            $currCost = $lastStep['cost'];
            if ($pos === $goal) {
                return $path;
            }
            [$x, $y, $z] = $pos;
            foreach ($dirs as [$dx, $dy, $dz]) {
                $nx = $x + $dx; $ny = $y + $dy; $nz = $z + $dz;
                if ($nx < 0 || $ny < 0 || $nz < 0 || $nx >= 64 || $ny >= 64 || $nz >= 4) continue;
                $key = "$nx,$ny,$nz";
                if (in_array($key, $blockSet)) continue;
                $cell = $field->getCell($nx, $ny, $nz);
                if ($cell->obstacle) continue;
                // Движение по этажам только если ladder=true в текущей клетке
                if ($nz != $z) {
                    $fromCell = $field->getCell($x, $y, $z);
                    if (!$fromCell->ladder) continue;
                }
                $moveCost = $weights($dx, $dy, $dz);
                $newCost = $currCost + $moveCost;
                if (!isset($costSoFar[$key]) || $newCost < $costSoFar[$key]) {
                    $costSoFar[$key] = $newCost;
                    $newPath = array_merge($path, [[
                        'pos' => [$nx, $ny, $nz],
                        'cost' => $newCost
                    ]]);
                    // Используем отрицательную стоимость для приоритета
                    $queue->insert($newPath, -$newCost);
                }
            }
        }
        return null;
    }
}
