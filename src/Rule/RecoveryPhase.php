<?php

namespace Mordheim\Rule;

use Mordheim\Warband;

class RecoveryPhase
{
    /**
     * Восстановление психологических состояний для бойцов активной банды
     * @param Warband $warband
     * @param Warband[] $warbands
     */
    public static function apply(Warband $warband, array $warbands): void
    {
        if (!RoutTest::apply($warband)) {
            // Если банда не прошла тест на бегство, все бойцы в PANIC
            foreach ($warband->fighters as $f) {
                if ($f->alive && $f->state !== \Mordheim\FighterState::OUT_OF_ACTION) {
                    $f->state = \Mordheim\FighterState::PANIC;
                }
            }
            \Mordheim\BattleLogger::add("Банда {$warband->name} не прошла тест на бегство! Все бойцы в панике.");
            return;
        }

        foreach ($warband->fighters as $fighter) {
            if (!$fighter->alive) continue;
            // --- PANIC recovery ---
            if ($fighter->state === \Mordheim\FighterState::PANIC) {
                if (\Mordheim\Rule\Psychology::leadershipTest($fighter, $warband->fighters)) {
                    $fighter->state = \Mordheim\FighterState::STANDING;
                    \Mordheim\BattleLogger::add("{$fighter->name} преодолел панику и возвращается в бой!");
                } else {
                    \Mordheim\BattleLogger::add("{$fighter->name} всё ещё в панике!");
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
                    if (!\Mordheim\Rule\Psychology::allAloneTest($fighter, $enemies, $allies)) {
                        $fighter->state = \Mordheim\FighterState::PANIC;
                        \Mordheim\BattleLogger::add("{$fighter->name} не выдержал одиночества и впадает в панику!");
                    }
                }
            }
            // --- Stupidity ---
            if ($fighter->hasSkill('Stupidity') && $fighter->state === \Mordheim\FighterState::STANDING) {
                if (!\Mordheim\Rule\Psychology::leadershipTest($fighter, $warband->fighters)) {
                    \Mordheim\BattleLogger::add("{$fighter->name} не прошёл тест тупости и стоит без дела!");
                } else {
                    \Mordheim\BattleLogger::add("{$fighter->name} прошёл тест тупости и может действовать нормально.");
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
                    \Mordheim\Rule\Psychology::testFear($fighter, $enemy, $warband->fighters);
                }
                if ($enemy->hasSkill('Terror') && $fighter->distance($enemy)<=8) {
                    \Mordheim\Rule\Psychology::testTerror($fighter, $warband->fighters);
                }
            }
        }
    }
}