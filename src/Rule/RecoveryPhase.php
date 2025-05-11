<?php

namespace Mordheim\Rule;

use Mordheim\Ruler;
use Mordheim\SpecialRule;
use Mordheim\Warband;
use Mordheim\FighterInterface;

class RecoveryPhase
{
    /**
     * Проверка теста на бегство для банды
     * @return bool true — если тест пройден, false — если не пройден (все в PANIC)
     */
    public static function applyRoutTest(Warband $warband, array $warbands): bool
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
    public static function applyPsychology(FighterInterface $fighter, Warband $warband, array $warbands): bool
    {
        if (!$fighter->getState()->getStatus()->isAlive()) return false;
        // --- PANIC recovery ---
        if ($fighter->getState()->getStatus() === \Mordheim\Status::PANIC) {
            if (Psychology::leadershipTest($fighter, $warband->fighters)) {
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
            $allies = $warband->fighters;
            $enemies = [];
            foreach ($warbands as $otherWb) {
                if ($otherWb !== $warband) {
                    foreach ($otherWb->fighters as $enemyFighter) {
                        if ($enemyFighter->getState()->getStatus()->isAlive()) $enemies[] = $enemyFighter;
                    }
                }
            }
            $closeEnemies = array_filter($enemies, fn($enemy) => Ruler::distance($fighter->getState()->getPosition(), $enemy->getState()->getPosition()) <= 1.99);
            $closeAllies = array_filter($allies, fn($ally) => $ally !== $fighter && $ally->getState()->getStatus()->canAct()
                && Ruler::distance($fighter->getState()->getPosition(), $ally->getState()->getPosition()) <= 6);
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
            if (!Psychology::leadershipTest($fighter, $warband->fighters)) {
                \Mordheim\BattleLogger::add("{$fighter->getName()} не прошёл тест тупости и стоит без дела!");
                return false;
            } else {
                \Mordheim\BattleLogger::add("{$fighter->getName()} прошёл тест тупости и может действовать нормально.");
            }
        }
        // --- Fear ---
        $enemies = [];
        foreach ($warbands as $otherWb) {
            if ($otherWb !== $warband) {
                foreach ($otherWb->fighters as $enemyFighter) {
                    if ($enemyFighter->getState()->getStatus()->isAlive()) $enemies[] = $enemyFighter;
                }
            }
        }
        foreach ($enemies as $enemy) {
            if ($enemy->hasSpecialRule(SpecialRule::CAUSE_FEAR) && Ruler::distance($fighter->getState()->getPosition(), $enemy->getState()->getPosition()) <= 8) {
                if (!Psychology::testFear($fighter, $enemy, $warband->fighters))
                    return false;
            }
        }
        return $fighter->getState()->getStatus()->canAct();
    }
}