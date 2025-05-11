<?php

namespace Mordheim\Strategy;

use Mordheim\Battle;
use Mordheim\FighterInterface;
use Mordheim\Slot;
use Mordheim\SpecialRule;

class CowardlyStrategy extends BaseBattleStrategy
{
    public float $aggressiveness = 0.6;

    protected function onMovePhase(Battle $battle, FighterInterface $fighter, array $enemies): void
    {
        if ($battle->getActiveCombats()->isFighterInCombat($fighter))
            return;

        // Ищем лидера в радиусе 6" (Ld bubble)
        $leader = null;
        foreach ($battle->getAlliesFor($fighter) as $ally) {
            if ($ally->hasSpecialRule(SpecialRule::LEADER) && $this->getDistance($fighter, $ally) <= 6) {
                $leader = $ally;
                break;
            }
        }
        if (!$leader) {
            // Нет лидера рядом — двигаемся к ближайшему лидеру
            $nearest = null;
            $minDist = PHP_INT_MAX;
            foreach ($battle->getAlliesFor($fighter) as $ally) {
                if ($ally->hasSpecialRule(SpecialRule::LEADER) && $this->getDistance($fighter, $ally) <= 6) {
                    $dist = $this->getDistance($fighter, $ally);
                    if ($dist < $minDist) {
                        $minDist = $dist;
                        $nearest = $ally;
                    }
                }
            }
            if ($nearest) {
                \Mordheim\Rule\Move::common($battle, $fighter, $nearest->getState()->getPosition(), $this->aggressiveness);
                return;
            }
        }
        if (empty($enemies)) return;
        $target = $this->getNearestEnemy($fighter, $enemies);
        if (!$target) return;
        $canAct = $this->canActAgainst($battle, $fighter, $target);
        if (!$canAct) {
            // Не прошёл тест — не действует
            return;
        }
        if ($this->getDistance($fighter, $target) < 4) {
            // Уходит от врага
            [$fx, $fy, $fz] = $fighter->getState()->getPosition(); // остальная логика ниже
            [$tx, $ty, $tz] = $target->getState()->getPosition();
            $dx = $fx - $tx;
            $dy = $fy - $ty;
            $dz = $fz - $tz;
            $move = [$fx + ($dx !== 0 ? ($dx > 0 ? 1 : -1) : 0), $fy + ($dy !== 0 ? ($dy > 0 ? 1 : -1) : 0), $fz + ($dz !== 0 ? ($dz > 0 ? 1 : -1) : 0)];
            if ($move[0] >= 0 && $move[1] >= 0 && $move[2] >= 0 && $move[0] < 64 && $move[1] < 64 && $move[2] < 4 && !$battle->getField()->getCell($move[0], $move[1], $move[2])->obstacle) {
                \Mordheim\Rule\Move::common($battle, $fighter, $move, $this->aggressiveness);
                return;
            }
        } else {
            // Проверяем наличие стрелкового оружия и его дальность
            $ranged = $fighter->getEquipmentManager()->getMainWeapon(Slot::RANGED);
            if ($ranged && $this->getDistance($fighter, $target) > $ranged->getRange()) {
                // Если не в радиусе, двигаемся к цели
                \Mordheim\Rule\Move::common($battle, $fighter, $target->getState()->getPosition(), $this->aggressiveness);
                return;
            }
        }
    }

    protected function onShootPhase(Battle $battle, FighterInterface $fighter, array $enemies): void
    {
        if (empty($enemies)) return;
        $ranged = $fighter->getEquipmentManager()->getMainWeapon(Slot::RANGED);
        if (!$ranged) return;
        $target = $this->getNearestEnemy($fighter, $enemies);
        if ($target && $this->getDistance($fighter, $target) <= $ranged->getRange()) {
            \Mordheim\Rule\Attack::ranged($battle, $fighter, $target, $this->spentMove);
        }
    }

    protected function onMagicPhase(Battle $battle, FighterInterface $fighter, array $enemies): void
    {
        // TODO: check active skills
        $spells = $fighter->getAdvancement()->getSpells();
        foreach ($spells as $wizardSpell) {
            $difficulty = $wizardSpell->getDifficulty();
            $spell = $wizardSpell->getSpell();
            $roll = \Mordheim\Dice::roll(6) + \Mordheim\Dice::roll(6);
            \Mordheim\BattleLogger::add("{$fighter->getName()} бросает 2d6=$roll для заклинания {$spell->name} (сложность {$difficulty})");
            if ($roll < $difficulty) {
                \Mordheim\BattleLogger::add("{$fighter->getName()} не смог применить заклинание {$spell->name}.");
                continue;
            }
            \Mordheim\BattleLogger::add("{$fighter->getName()} применяет заклинание {$spell->name}!");
            if ($spell->onPhaseMagic($battle, $fighter))
                return;
        }
    }

    protected function onCloseCombatPhase(Battle $battle, FighterInterface $fighter, array $enemies): void
    {
        // Обычно не атакует в рукопашную, если только не окружён
    }
}
