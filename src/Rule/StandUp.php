<?php

namespace Mordheim\Rule;

use Mordheim\Fighter;
use Mordheim\FighterState;

class StandUp
{
    /**
     * Попытка встать с земли (KNOCKED_DOWN)
     * Если есть Jump Up — встаёт автоматически без траты движения.
     * Без навыка — тратит всё движение, чтобы встать.
     * @return bool true если потратил движение
     */
    public static function apply(Fighter $fighter): bool
    {
        if ($fighter->state !== FighterState::KNOCKED_DOWN) return false;
        if ($fighter->hasSkill('Jump Up')) {
            \Mordheim\BattleLogger::add("{$fighter->name} использует Jump Up и мгновенно встаёт!");
            $fighter->state = FighterState::STANDING;
            return false;
        }
        // Без навыка: встаёт, но тратит всё движение
        \Mordheim\BattleLogger::add("{$fighter->name} встаёт с земли, тратя всё движение.");
        $fighter->state = FighterState::STANDING;
        return true;
    }
}