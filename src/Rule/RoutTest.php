<?php
namespace Mordheim\Rule;

use Mordheim\Warband;

class RoutTest
{
    /**
     * Проверка на бегство (Rout test) по правилам Mordheim
     * @param Warband $warband
     * @return bool true если тест пройден или не требуется, false если провален
     */
    public static function apply(Warband $warband): bool
    {
        $total = count($warband->fighters);
        $ooa = 0;
        foreach ($warband->fighters as $f) {
            if ($f->state === \Mordheim\FighterState::OUT_OF_ACTION) $ooa++;
        }
        if ($total === 0 || $ooa / $total < 0.25) return true; // тест не требуется
        // Классические правила: тест при >= 25% OOA
        if ($ooa / $total < 0.25) return true; // тест не требуется
        // Если OOA >= 25%, требуется тест
        // По правилам бросает лидер (первый живой)
        $leader = null;
        foreach ($warband->fighters as $f) {
            if ($f->alive && $f->state !== \Mordheim\FighterState::OUT_OF_ACTION) {
                $leader = $f;
                break;
            }
        }
        if (!$leader) return false; // все бойцы выбиты
        return \Mordheim\Rule\Psychology::testRout($leader, $warband->fighters);
    }
}
