<?php
namespace Mordheim;

class Turn
{
    /**
     * Проверка на бегство (Rout test) по правилам Mordheim
     * @param Warband $warband
     * @return bool true если тест пройден или не требуется, false если провален
     */
    public static function routTest(Warband $warband): bool
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
        return \Mordheim\Psychology::testRout($leader, $warband->fighters);
    }

    /**
     * Восстановление психологических состояний для бойцов активной банды
     * @param Warband $warband
     * @param Warband[] $warbands
     */
    public static function recoverPsychologyState(Warband $warband, array $warbands): void
    {
        if (!self::routTest($warband)) {
            // Если банда не прошла тест на бегство, все бойцы в PANIC
            foreach ($warband->fighters as $f) {
                if ($f->alive && $f->state !== \Mordheim\FighterState::OUT_OF_ACTION) {
                    $f->state = \Mordheim\FighterState::PANIC;
                }
            }
            printLog("Банда {$warband->name} не прошла тест на бегство! Все бойцы в панике.");
            return;
        }

        foreach ($warband->fighters as $fighter) {
            if (!$fighter->alive) continue;
            // --- PANIC recovery ---
            if ($fighter->state === \Mordheim\FighterState::PANIC) {
                if (\Mordheim\Psychology::leadershipTest($fighter, $warband->fighters)) {
                    $fighter->state = \Mordheim\FighterState::STANDING;
                    printLog("{$fighter->name} преодолел панику и возвращается в бой!");
                } else {
                    printLog("{$fighter->name} всё ещё в панике!");
                }
            }
            // --- All Alone ---
            if ($fighter->state === \Mordheim\FighterState::STANDING) {
                $allies = $warband->fighters;
                $enemies = [];
                foreach ($warbands as $otherWb) {
                    if ($otherWb !== $warband) {
                        foreach ($otherWb->fighters as $f) {
                            if ($f->alive) $enemies[] = $f;
                        }
                    }
                }
                $closeEnemies = array_filter($enemies, fn($e)=>$fighter->distance($e)<=1.99);
                $closeAllies = array_filter($allies, fn($a)=>$a!==$fighter && $a->alive && $a->state===\Mordheim\FighterState::STANDING && $fighter->distance($a)<=6);
                if (count($closeEnemies)>=2 && count($closeAllies)===0) {
                    if (!\Mordheim\Psychology::allAloneTest($fighter, $enemies, $allies)) {
                        $fighter->state = \Mordheim\FighterState::PANIC;
                        printLog("{$fighter->name} не выдержал одиночества и впадает в панику!");
                    }
                }
            }
            // --- Stupidity ---
            if ($fighter->hasSkill('Stupidity') && $fighter->state === \Mordheim\FighterState::STANDING) {
                if (!\Mordheim\Psychology::leadershipTest($fighter, $warband->fighters)) {
                    printLog("{$fighter->name} не прошёл тест тупости и стоит без дела!");
                } else {
                    printLog("{$fighter->name} прошёл тест тупости и может действовать нормально.");
                }
            }
            // --- Fear & Terror ---
            $enemies = [];
            foreach ($warbands as $otherWb) {
                if ($otherWb !== $warband) {
                    foreach ($otherWb->fighters as $f) {
                        if ($f->alive) $enemies[] = $f;
                    }
                }
            }
            foreach ($enemies as $enemy) {
                if ($enemy->hasSkill('Fear') && $fighter->distance($enemy)<=8) {
                    \Mordheim\Psychology::testFear($fighter, $enemy, $warband->fighters);
                }
                if ($enemy->hasSkill('Terror') && $fighter->distance($enemy)<=8) {
                    \Mordheim\Psychology::testTerror($fighter, $warband->fighters);
                }
            }
        }
    }
}
