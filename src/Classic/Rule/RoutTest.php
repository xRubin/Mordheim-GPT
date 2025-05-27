<?php

namespace Mordheim\Classic\Rule;

use Mordheim\Band;
use Mordheim\Classic\Exceptions\RoutTestLeaderNotFoundException;
use Mordheim\Classic\Fighter;
use Mordheim\Classic\SpecialRule;

class RoutTest
{
    private const OOA_LIMIT = 0.25; // 25%

    /**
     * Проверка на бегство (Rout test) по правилам Mordheim
     * @param Band $band
     * @return bool true если тест пройден или не требуется, false если провален
     */
    public static function apply(Band $band): bool
    {
        $total = count($band->fighters);
        if ($total === 0) return true; // тест не требуется
        if (self::countOOA($band) / $total < self::OOA_LIMIT) return true; // тест не требуется
        // Если OOA >= OOA_LIMIT, требуется тест
        try {
            // По правилам бросает лидер (первый живой)
            return Psychology::testRout(self::findLeader($band), $band->fighters);
        } catch (RoutTestLeaderNotFoundException $e) {
            return false;
        }
    }

    /**
     * Подсчет выведенных из строя бойцов
     * @param Band $band
     * @return int
     */
    private static function countOOA(Band $band): int
    {
        $ooa = 0;
        foreach ($band->fighters as $fighter) {
            if (!$fighter->getState()->getStatus()->isAlive()) $ooa++;
        }
        return $ooa;
    }

    /**
     * По правилам бросает лидер (первый живой)
     * @param Band $band
     * @return Fighter
     */
    private static function findLeader(Band $band): Fighter
    {
        foreach ($band->fighters as $fighter) {
            if ($fighter->getState()->getStatus()->canAct() && $fighter->hasSpecialRule(SpecialRule::LEADER)) {
                return $fighter;
            }
        }
        throw new RoutTestLeaderNotFoundException();
    }
}
