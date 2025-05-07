<?php

namespace Mordheim;

class Ruler
{
    /**
     * Возвращает расстояние между двумя координатами
     * @param array $a [x,y,z]
     * @param array $b [x,y,z]
     * @return float
     */
    public static function distance(array $a, array $b): float
    {
        return sqrt(pow($a[0] - $b[0], 2) + pow($a[1] - $b[1], 2) + pow($a[2] - $b[2], 2));
    }

    /**
     * Проверяет соприкасаются ли координаты
     * @param array $a [x,y,z]
     * @param array $b [x,y,z]
     * @return bool
     */
    public static function isAdjacent(array $a, array $b): bool
    {
        return abs($a[0] - $b[0]) <= 1 && abs($a[1] - $b[1]) <= 1 && abs($a[2] - $b[2]) <= 1;
    }

    /**
     * Проверяет есть ли препятствие между двумя координатами
     * @param Battle $battle
     * @param array $start [x,y,z]
     * @param array $end [x,y,z]
     * @return bool
     */
    public static function hasObstacleBetween(Battle $battle, array $start, array $end): bool
    {
        $x1 = $start[0];
        $y1 = $start[1];
        $z1 = $start[2];
        $x2 = $end[0];
        $y2 = $end[1];
        $z2 = $end[2];

        // Если координаты совпадают, нет препятствия
        if ($x1 === $x2 && $y1 === $y2 && $z1 === $z2) {
            return false;
        }

        // Проверяем все целые точки на пути между координатами
        $dx = abs($x2 - $x1);
        $dy = abs($y2 - $y1);
        $dz = abs($z2 - $z1);
        $x = $x1;
        $y = $y1;
        $z = $z1;
        $n = 1 + $dx + $dy + $dz;
        $x_inc = ($x2 > $x1) ? 1 : -1;
        $y_inc = ($y2 > $y1) ? 1 : -1;
        $z_inc = ($z2 > $z1) ? 1 : -1;
        $error = $dx - $dy;
        $error2 = $dx - $dz;

        for ($i = 0; $i < $n; $i++) {
            if ($battle->getField()->getCell($x, $y, $z)->obstacle) {
                return true;
            }

            $e2 = 2 * $error;
            if ($e2 > -$dy) {
                $error -= $dy;
                $x += $x_inc;
            }
            if ($e2 < $dx) {
                $error += $dx;
                $y += $y_inc;
            }

            $e2 = 2 * $error2;
            if ($e2 > -$dz) {
                $error2 -= $dz;
                $x += $x_inc;
            }
            if ($e2 < $dx) {
                $error2 += $dx;
                $z += $z_inc;
            }
        }

        return false;
    }
}