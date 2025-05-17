<?php

namespace Mordheim\Strategy\PhaseMove;

use Mordheim\Battle;
use Mordheim\BattleStrategyInterface;
use Mordheim\Exceptions\PathfinderTargetUnreachableException;
use Mordheim\Fighter;
use Mordheim\Rule\Move;
use Mordheim\Ruler;

class MoveBlock
{
    public function __construct(
        private readonly BattleStrategyInterface $strategy,
        private readonly string                  $targetAlgorithm = 'nearest',
        private readonly float                   $aggressiveness = 0.6
    )
    {

    }

    public function __invoke()
    {
        return function (Battle $battle, Fighter $fighter): bool {
            if ($this->strategy->spentMove)
                return false;

            $enemies = $battle->getEnemiesFor($fighter);
            if (empty($enemies)) return false;

            if ($this->targetAlgorithm === 'nearest') {
                $target = $this->getNearestEnemy($fighter, $enemies);
            } else {
                return false;
            }

            if (!$target)
                return false;

            if (Ruler::isAdjacent($fighter, $target))
                return false;

            try {
                Move::common($battle, $fighter, $target->getState()->getPosition(), $this->aggressiveness);
                $this->strategy->spentShoot = true;
                $this->strategy->spentMove = true;
                return true;
            } catch (PathfinderTargetUnreachableException $e) {
                return false;
            }
        };
    }

    /**
     * Найти ближайшего врага
     */
    protected function getNearestEnemy(Fighter $fighter, array $enemies): ?Fighter
    {
        if (empty($enemies)) return null;
        usort($enemies, fn($a, $b) => Ruler::distance($fighter, $a) <=> Ruler::distance($fighter, $b));
        return $enemies[0];
    }
}