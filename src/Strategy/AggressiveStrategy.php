<?php

namespace Mordheim\Strategy;

use Mordheim\Battle;
use Mordheim\Exceptions\FighterAbnormalStateException;
use Mordheim\Fighter;
use Mordheim\FighterState;
use Mordheim\GameField;

class AggressiveStrategy extends BaseBattleStrategy implements BattleStrategy
{
    public function movePhase(Battle $battle, Fighter $fighter, array $enemies, GameField $field): void
    {
        try {
            $fighter->state->validate();
        } catch (FighterAbnormalStateException $e) {
            if ($e->getState() === FighterState::KNOCKED_DOWN) {
                $this->movedThisTurn = \Mordheim\Rule\StandUp::apply($fighter);
            } else {
                \Mordheim\BattleLogger::add("{$fighter->name} не может действовать из-за состояния {$fighter->state->value}.");
                return;
            }
        }

        if (empty($enemies)) return;
        $target = $this->getNearestEnemy($fighter, $enemies);

        if (!$fighter->isAdjacent($target)) {
            if ($closeCombat = \Mordheim\Rule\Charge::attempt($field, $fighter, $target)) {
                $battle->addCombat($closeCombat);
            } else {
                \Mordheim\Rule\Move::apply($field, $fighter, $target->position);
                $this->movedThisTurn = true;
            }
        }
    }

    public function shootPhase(Battle $battle, Fighter $fighter, array $enemies, GameField $field): void
    {
        try {
            $fighter->state->validate();
        } catch (FighterAbnormalStateException $e) {
            \Mordheim\BattleLogger::add("{$fighter->name} не может действовать из-за состояния {$fighter->state->value}.");
            return;
        }
        $ranged = $this->getRangedWeapon($fighter);
        if (!$ranged || empty($enemies)) return;
        $target = $this->getNearestEnemy($fighter, $enemies);
        if ($target && $fighter->distance($target) <= $ranged->range) {
            \Mordheim\Rule\Shoot::apply($fighter, $target, false);
        }
    }

    public function magicPhase(Battle $battle, Fighter $fighter, array $enemies, GameField $field): void
    {
        try {
            $fighter->state->validate();
        } catch (FighterAbnormalStateException $e) {
            \Mordheim\BattleLogger::add("{$fighter->name} не может действовать из-за состояния {$fighter->state->value}.");
            return;
        }
        // TODO: реализовать заклинания
    }

    public function closeCombatPhase(Battle $battle, Fighter $fighter, array $enemies, GameField $field): void
    {
        try {
            $fighter->state->validate();
        } catch (FighterAbnormalStateException $e) {
            \Mordheim\BattleLogger::add("{$fighter->name} не может действовать из-за состояния {$fighter->state->value}.");
            return;
        }
        if (empty($enemies)) return;
        $target = $this->getNearestEnemy($fighter, $enemies);
        if ($target && $fighter->isAdjacent($target)) {
            $canAttack = $this->canActAgainst($fighter, $target, $field);
            if ($canAttack) {
                \Mordheim\Rule\Attack::apply($fighter, $target);
            }
        }
    }
}
