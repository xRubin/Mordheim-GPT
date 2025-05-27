<?php

namespace Mordheim\Classic\Scenarios;

use Mordheim\Band;
use Mordheim\BattleLogger;
use Mordheim\Classic\Rule\RoutTest;
use Mordheim\Classic\Ruler;
use Mordheim\Classic\Status;
use Mordheim\Scenario;

class DefendTheFindScenario extends Scenario
{
    public function isScenarioOver(): bool
    {
        foreach ($this->getWarbands() as $warband) {
            if (!RoutTest::apply($warband)) {
                // Если банда не прошла тест на бегство, все бойцы в PANIC
                foreach ($warband->fighters as $fighter) {
                    if ($fighter->getState()->getStatus()->canAct()) {
                        $fighter->getState()->setStatus(Status::PANIC);
                    }
                }
                BattleLogger::add("Банда {$warband->name} не прошла тест на бегство! Все бойцы в панике.");
                return true;
            }
        }

        $canActAttackers = array_reduce($this->attackerBand->fighters, function ($carry, $fighter) {
            return $fighter->getState()->getStatus()->canAct() ? ++$carry : $carry;
        }, 0);

        $canActDefenders = array_reduce($this->defenderBand->fighters, function ($carry, $fighter) {
            return $fighter->getState()->getStatus()->canAct() ? ++$carry : $carry;
        }, 0);

        return $canActAttackers > $canActDefenders;
    }

    public function getDeployArea(Band $band): array
    {
        $result = [];
        if ($band === $this->defenderBand) {
            for ($i = $this->getGameField()->getWidth() / 2 - 6; $i <= $this->getGameField()->getWidth() / 2 + 6; $i++)
                for ($j = $this->getGameField()->getLength() / 2 - 6; $j <= $this->getGameField()->getLength() / 2 + 6; $j++)
                    if (!$this->getGameField()->getCell($i, $j, 0)->obstacle && Ruler::distance([$this->getGameField()->getWidth() / 2, $this->getGameField()->getLength() / 2, 0], [$i, $j, 0]) <= 6)
                        $result[] = [$i, $j, 0];
        } else {
            for ($i = 0; $i < 6; $i++)
                for ($j = 0; $j < $this->getGameField()->getLength(); $j++)
                    $result[] = [$i, $j, 0];
        }
        return $result;
    }

    public function getObjectiveArea(Band $band): array
    {
        $result = [];
        for ($i = $this->getGameField()->getWidth() / 2 - 6; $i <= $this->getGameField()->getWidth() / 2 + 6; $i++)
            for ($j = $this->getGameField()->getLength() / 2 - 6; $j <= $this->getGameField()->getLength() / 2 + 6; $j++)
                if (!$this->getGameField()->getCell($i, $j, 0)->obstacle && Ruler::distance([$this->getGameField()->getWidth() / 2, $this->getGameField()->getLength() / 2, 0], [$i, $j, 0]) <= 6)
                    $result[] = [$i, $j, 0];
        return $result;
    }

    public function isAttacker(Band $band): bool
    {
        $warbands = $this->getWarbands();
        usort($warbands, fn(Band $a, Band $b) => count($b->fighters) <=> count($a->fighters));
        return $band === $warbands[0];
    }
}
