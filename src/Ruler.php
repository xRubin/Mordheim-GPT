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
}