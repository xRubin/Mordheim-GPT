<?php

namespace Mordheim\Rule;

use Mordheim\Fighter;
use Mordheim\SpecialRule;
use Mordheim\Status;

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
        if ($fighter->getState()->getStatus() !== Status::KNOCKED_DOWN) return false;
        if ($fighter->hasSpecialRule(SpecialRule::JUMP_UP)) {
            \Mordheim\BattleLogger::add("{$fighter->getName()} использует Jump Up и мгновенно встаёт!");
            $fighter->getState()->setStatus(Status::STANDING);
            return false;
        }
        // Без навыка: встаёт, но тратит всё движение
        \Mordheim\BattleLogger::add("{$fighter->getName()} встаёт с земли, тратя всё движение.");
        $fighter->getState()->setStatus(Status::STANDING);
        return true;
    }
}