<?php

namespace Mordheim\Rule;

use Mordheim\Battle;
use Mordheim\Fighter;
use Mordheim\Ruler;
use Mordheim\SpecialRule;
use Mordheim\Band;

class RecoveryPhase
{
    /**
     * Проверка теста на бегство для банды
     * @return bool true — если тест пройден, false — если не пройден (все в PANIC)
     */
    public static function applyRoutTest(Band $warband): bool
    {
        if (!RoutTest::apply($warband)) {
            // Если банда не прошла тест на бегство, все бойцы в PANIC
            foreach ($warband->fighters as $fighter) {
                if ($fighter->getState()->getStatus()->canAct()) {
                    $fighter->getState()->setStatus(\Mordheim\Status::PANIC);
                }
            }
            \Mordheim\BattleLogger::add("Банда {$warband->name} не прошла тест на бегство! Все бойцы в панике.");
            return false;
        }
        return true;
    }

    /**
     * Применяет психологические проверки к бойцу
     * @return bool true — если боец может действовать после всех проверок
     */
    public static function applyPsychology(Battle $battle, Fighter $fighter, Band $band, array $warbands): bool
    {
        if (!$fighter->getState()->getStatus()->isAlive()) return false;
        foreach ($fighter->getState()->getActiveSpells() as $spell)
            $spell->getProcessor()?->onPhaseRecovery($battle, $fighter);
        // --- PANIC recovery ---
        if ($fighter->getState()->getStatus() === \Mordheim\Status::PANIC) {
            if (Psychology::leadershipTest($fighter, $band->fighters)) {
                $fighter->getState()->setStatus(\Mordheim\Status::STANDING);
                \Mordheim\BattleLogger::add("{$fighter->getName()} преодолел панику и возвращается в бой!");
                // TODO: в этот ход может только колдовать
            } else {
                \Mordheim\BattleLogger::add("{$fighter->getName()} всё ещё в панике!");
                return false;
            }
        }
        // --- All Alone ---
        if ($fighter->getState()->getStatus() === \Mordheim\Status::STANDING) {
            $allies = $band->fighters;
            $enemies = [];
            foreach ($warbands as $otherWb) {
                if ($otherWb !== $band) {
                    foreach ($otherWb->fighters as $enemyFighter) {
                        if ($enemyFighter->getState()->getStatus()->isAlive()) $enemies[] = $enemyFighter;
                    }
                }
            }
            $closeEnemies = array_filter($enemies, fn($enemy) => Ruler::distance($fighter, $enemy) < 2);
            $closeAllies = array_filter($allies, fn($ally) => $ally !== $fighter && $ally->getState()->getStatus()->canAct()
                && Ruler::distance($fighter, $ally) <= 6);
            if (count($closeEnemies) >= 2 && count($closeAllies) === 0) {
                if (!Psychology::allAloneTest($fighter, $enemies, $allies)) {
                    $fighter->getState()->setStatus(\Mordheim\Status::PANIC);
                    \Mordheim\BattleLogger::add("{$fighter->getName()} не выдержал одиночества и впадает в панику!");
                    return false;
                }
            }
        }
        // --- Stupidity ---
        if ($fighter->hasSpecialRule(SpecialRule::STUPIDITY) && $fighter->getState()->getStatus() === \Mordheim\Status::STANDING) {
            if (!Psychology::leadershipTest($fighter, $band->fighters)) {
                \Mordheim\BattleLogger::add("{$fighter->getName()} не прошёл тест тупости и стоит без дела!");
                return false;
            } else {
                \Mordheim\BattleLogger::add("{$fighter->getName()} прошёл тест тупости и может действовать нормально.");
            }
        }
        // --- Fear ---
        $enemies = [];
        foreach ($warbands as $otherWb) {
            if ($otherWb !== $band) {
                foreach ($otherWb->fighters as $enemyFighter) {
                    if ($enemyFighter->getState()->getStatus()->isAlive()) $enemies[] = $enemyFighter;
                }
            }
        }
        foreach ($enemies as $enemy) {
            if ($enemy->hasSpecialRule(SpecialRule::CAUSE_FEAR) && Ruler::distance($fighter, $enemy) <= 8) {
                if (!Psychology::testFear($fighter, $enemy, $band->fighters))
                    return false;
            }
        }
        return $fighter->getState()->getStatus()->canAct();
    }
}