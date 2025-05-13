<?php

namespace Mordheim\Rule;

use Mordheim\Exceptions\RoutTestLeaderNotFoundException;
use Mordheim\Fighter;
use Mordheim\SpecialRule;
use Mordheim\Warband;

class RoutTest
{
    private const OOA_LIMIT = 0.25; // 25%

    /**
     * Проверка на бегство (Rout test) по правилам Mordheim
     * @param Warband $warband
     * @return bool true если тест пройден или не требуется, false если провален
     */
    public static function apply(Warband $warband): bool
    {
        $total = count($warband->fighters);
        if ($total === 0) return true; // тест не требуется
        if (self::countOOA($warband) / $total < self::OOA_LIMIT) return true; // тест не требуется
        // Если OOA >= OOA_LIMIT, требуется тест
        try {
            // По правилам бросает лидер (первый живой)
            return \Mordheim\Rule\Psychology::testRout(self::findLeader($warband), $warband->fighters);
        } catch (RoutTestLeaderNotFoundException $e) {
            return false;
        }
    }

    /**
     * Подсчет выведенных из строя бойцов
     * @param Warband $warband
     * @return int
     */
    private static function countOOA(Warband $warband): int
    {
        $ooa = 0;
        foreach ($warband->fighters as $fighter) {
            if (!$fighter->getState()->getStatus()->isAlive()) $ooa++;
        }
        return $ooa;
    }

    /**
     * По правилам бросает лидер (первый живой)
     * @param Warband $warband
     * @return Fighter
     */
    private static function findLeader(Warband $warband): Fighter
    {
        foreach ($warband->fighters as $fighter) {
            if ($fighter->getState()->getStatus()->canAct() && $fighter->hasSpecialRule(SpecialRule::LEADER)) {
                return $fighter;
            }
        }
        throw new RoutTestLeaderNotFoundException();
    }
}
