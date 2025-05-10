<?php

namespace Mordheim\Rule;

use Mordheim\SpecialRule;
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
            foreach ($warband->fighters as $fighter) {
                if ($fighter->getState()->getStatus()->canAct()) {
                    $fighter->getState()->setStatus(\Mordheim\Status::PANIC);
                }
            }
            \Mordheim\BattleLogger::add("Банда {$warband->name} не прошла тест на бегство! Все бойцы в панике.");
            return;
        }

        foreach ($warband->fighters as $fighter) {
            if (!$fighter->getState()->getStatus()->isAlive()) continue;
            // --- PANIC recovery ---
            if ($fighter->getState()->getStatus() === \Mordheim\Status::PANIC) {
                if (Psychology::leadershipTest($fighter, $warband->fighters)) {
                    $fighter->getState()->setStatus(\Mordheim\Status::STANDING);
                    \Mordheim\BattleLogger::add("{$fighter->getName()} преодолел панику и возвращается в бой!");
                } else {
                    \Mordheim\BattleLogger::add("{$fighter->getName()} всё ещё в панике!");
                }
            }
            // --- All Alone ---
            if ($fighter->getState()->getStatus() === \Mordheim\Status::STANDING) {
                $allies = $warband->fighters;
                $enemies = [];
                foreach ($warbands as $otherWb) {
                    if ($otherWb !== $warband) {
                        foreach ($otherWb->fighters as $fighter) {
                            if ($fighter->getState()->getStatus()->isAlive()) $enemies[] = $fighter;
                        }
                    }
                }
                $closeEnemies = array_filter($enemies, fn($enemy) => $fighter->getDistance($enemy) <= 1.99);
                $closeAllies = array_filter($allies, fn($ally) => $ally !== $fighter && $ally->getState()->getStatus()->canAct() && $fighter->getDistance($ally) <= 6);
                if (count($closeEnemies) >= 2 && count($closeAllies) === 0) {
                    if (!Psychology::allAloneTest($fighter, $enemies, $allies)) {
                        $fighter->getState()->setStatus(\Mordheim\Status::PANIC);
                        \Mordheim\BattleLogger::add("{$fighter->getName()} не выдержал одиночества и впадает в панику!");
                    }
                }
            }
            // --- Stupidity ---
            // TODO
            if ($fighter->hasSpecialRule(SpecialRule::STUPIDITY) && $fighter->getState()->getStatus() === \Mordheim\Status::STANDING) {
                if (!Psychology::leadershipTest($fighter, $warband->fighters)) {
                    \Mordheim\BattleLogger::add("{$fighter->getName()} не прошёл тест тупости и стоит без дела!");
                } else {
                    \Mordheim\BattleLogger::add("{$fighter->getName()} прошёл тест тупости и может действовать нормально.");
                }
            }
            // --- Fear & Terror ---
            $enemies = [];
            foreach ($warbands as $otherWb) {
                if ($otherWb !== $warband) {
                    foreach ($otherWb->fighters as $fighter) {
                        if ($fighter->getState()->getStatus()->isAlive()) $enemies[] = $fighter;
                    }
                }
            }
            foreach ($enemies as $enemy) {
                if ($enemy->hasSpecialRule(SpecialRule::CAUSE_FEAR) && $fighter->getDistance($enemy) <= 8) {
                    Psychology::testFear($fighter, $enemy, $warband->fighters);
                }
            }
        }
    }
}