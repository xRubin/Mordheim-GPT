<?php

namespace Mordheim\Classic\Strategy\PhaseMove;

use Mordheim\Classic\BattleStrategyInterface;
use Mordheim\Classic\Battle;
use Mordheim\Classic\Rule\Move;
use Mordheim\Exceptions\ChargeFailedException;
use Mordheim\Classic\Fighter;
use Mordheim\Classic\Ruler;

class ChargeBlock
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

            if ($this->strategy->spentCharge)
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
                $battle->getActiveCombats()->add(
                    Move::charge($battle, $fighter, $target, $this->aggressiveness)
                );
                $this->strategy->spentCharge = true;
                $this->strategy->spentShoot = true;
                $this->strategy->spentMagic = true;
                $this->strategy->spentMove = true;
                return true;
            } catch (ChargeFailedException $e) {
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