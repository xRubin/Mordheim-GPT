<?php

namespace Mordheim;

class Ruler
{
    /**
     * Возвращает расстояние между двумя координатами
     * @param array|Fighter $from [x,y,z]
     * @param array|Fighter $to [x,y,z]
     * @return float
     */
    public static function distance(array|Fighter $from, array|Fighter $to): float
    {
        $a = is_array($from) ? $from : $from->getState()->getPosition();
        $b = is_array($to) ? $to : $to->getState()->getPosition();
        return sqrt(pow($a[0] - $b[0], 2) + pow($a[1] - $b[1], 2) + pow($a[2] - $b[2], 2));
    }

    /**
     * Проверяет соприкасаются ли координаты
     * @param array|Fighter $from [x,y,z]
     * @param array|Fighter $to [x,y,z]
     * @return bool
     */
    public static function isAdjacent(array|Fighter $from, array|Fighter $to): bool
    {
        $a = is_array($from) ? $from : $from->getState()->getPosition();
        $b = is_array($to) ? $to : $to->getState()->getPosition();
        return abs($a[0] - $b[0]) <= 1 && abs($a[1] - $b[1]) <= 1 && abs($a[2] - $b[2]) <= 1;
    }
}