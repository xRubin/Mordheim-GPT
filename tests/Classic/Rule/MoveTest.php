<?php

namespace Classic\Rule;

use Mordheim\Band;
use Mordheim\Classic\Battle;
use Mordheim\Classic\Exceptions\MoveInitiativeRollFailedException;
use Mordheim\Classic\Exceptions\MoveRunDeprecatedException;
use Mordheim\Classic\Fighter;
use Mordheim\Classic\Rule\Move;
use Mordheim\Classic\SpecialRule;
use Mordheim\GameField;
use Mordheim\GameFieldCell;

class MoveTest extends \MordheimTestCase
{
    private function makeFighterMock($pos, $move = 3, $initiative = 3, $skills = [])
    {
        $fighterMock = $this->createMock(Fighter::class);

        $fighterMock->method('getMoveRange')->willReturn($move);
        $fighterMock->method('getInitiative')->willReturn($initiative);
        $fighterMock->method('getClimbInitiative')->willReturn($initiative);
        $fighterMock->method('getState')->willReturn(
            new \Mordheim\Classic\FighterState($pos, $this->createMock(\Mordheim\Classic\BattleStrategyInterface::class), 1)
        );
        $fighterMock->method('getName')->willReturn('TestFighter');
        $fighterMock->method('getMovementWeights')->willReturn(function (GameFieldCell $from, GameFieldCell $to, $dx, $dy, $dz): float {
            if ($dz !== 0) return abs(2.0 * $dz);
            if ($dx !== 0 && $dy !== 0) return 0.7 * (abs($dx) + abs($dy));
            return abs($dx) + abs($dy) + abs($dz);
        });
        $fighterMock->method('getRunRange')->willReturn($move * 2);
        $fighterMock->method('getChargeRange')->willReturn($move * (in_array(SpecialRule::SPRINT, $skills) ? 3 : 2));
        $fighterMock->method('hasSpecialRule')->willReturnCallback(function ($rule) use ($skills) {
            return in_array($rule, $skills);
        });
        return $fighterMock;
    }

    private function makeClearBattle(array $attackerFighters, array $defenderFighters)
    {
        return new Battle(
            new GameField(),
            [
                new Band('Attackers', $attackerFighters),
                new Band('Defenders', $defenderFighters)
            ]
        );
    }

    public function testMoveThroughWaterInitiativeSuccess()
    {
        $fighter = $this->makeFighterMock([0, 0, 0], 3, 5);
        \Mordheim\Dice::setTestRolls([4]);
        $battle = $this->makeClearBattle([$fighter], []);
        $battle->getField()->setCell(1, 0, 0, new GameFieldCell(0, water: true));
        Move::common($battle, $fighter, [2, 0, 0], 1.0);
        $this->assertEquals([2, 0, 0], $fighter->getState()->getPosition(), 'Боец должен пройти через воду при успешном броске');
        $logs = \Mordheim\BattleLogger::getAll();
        $this->assertStringContainsString('бросает Initiative для воды', implode("\n", $logs));
    }

    public function testMoveThroughWaterInitiativeFail()
    {
        $fighter = $this->makeFighterMock([0, 0, 0], 3, 3);
        \Mordheim\Dice::setTestRolls([5]);
        $battle = $this->makeClearBattle([$fighter], []);
        $battle->getField()->setCell(1, 0, 0, new GameFieldCell(0, water: true));
        try {
            Move::common($battle, $fighter, [2, 0, 0], 1.0);
            $this->fail('Ожидалось исключение MoveInitiativeRollFailedException');
        } catch (MoveInitiativeRollFailedException $e) {
            $this->assertEquals([1, 0, 0], $fighter->getState()->getPosition(), 'Боец должен остановиться на воде при провале инициативы');
            $logs = \Mordheim\BattleLogger::getAll();
            $this->assertStringContainsString('Провал Initiative в воде', implode("\n", $logs));
        }
    }

    public function testMoveAdvancedTowardsDiagonal()
    {
        $fighter = $this->makeFighterMock([0, 0, 0], 2);
        $target = [1, 1, 0];
        $battle = $this->makeClearBattle([$fighter], []);
        Move::common($battle, $fighter, $target, 1.0);
        $this->assertEquals($target, $fighter->getState()->getPosition());
    }

    public function testMoveAdvancedTowardsReachGoal()
    {
        $fighter = $this->makeFighterMock([0, 0, 0], 3);
        $target = [3, 0, 0];
        $battle = $this->makeClearBattle([$fighter], []);
        Move::common($battle, $fighter, $target, 1.0);
        $this->assertEquals($target, $fighter->getState()->getPosition());
    }

    public function testMoveAdvancedTowardsPartialMove()
    {
        $fighter = $this->makeFighterMock([0, 0, 0], 2);
        $target = [4, 0, 0];
        $battle = $this->makeClearBattle([$fighter], []);
        Move::common($battle, $fighter, $target, 1.0);
        $this->assertEquals([2, 0, 0], $fighter->getState()->getPosition());
    }

    public function testMoveAdvancedTowardsNoPath()
    {
        $fighter = $this->makeFighterMock([0, 0, 0], 3);
        $target = [2, 0, 0];
        $battle = $this->makeClearBattle([$fighter], []);
        $battle->getField()->setCell(1, 0, 0, new GameFieldCell(0, obstacle: true));
        Move::common($battle, $fighter, $target, 1.0);
        $this->assertEquals([2, 0, 0], $fighter->getState()->getPosition());
    }

    public function testSprintBonusMovement()
    {
        $fighter = $this->makeFighterMock([0, 0, 0], 3, 3, [SpecialRule::SPRINT]);
        $target = [10, 0, 0];
        $battle = $this->makeClearBattle([$fighter], []);
        Move::common($battle, $fighter, $target, 1.0);
        $this->assertGreaterThanOrEqual(3, $fighter->getState()->getPosition()[0]);
        $this->assertLessThanOrEqual(9, $fighter->getState()->getPosition()[0]);
    }

    public function testRun_NoEnemiesIn8Inches()
    {
        $fighter = $this->makeFighterMock([0, 0, 0], 3);
        $battle = $this->makeClearBattle([$fighter], []);
        Move::runIfNoEnemies($battle, $fighter, [6, 0, 0], 1.0);
        $this->assertEquals([6, 0, 0], $fighter->getState()->getPosition(), 'Боец должен пробежать на 6 клеток (двойное движение)');
    }

    public function testRun_EnemyWithin8Inches()
    {
        $fighter = $this->makeFighterMock([0, 0, 0], 3);
        $enemy = $this->makeFighterMock([5, 0, 0], 3);
        $battle = $this->makeClearBattle([$fighter], [$enemy]);
        $this->expectException(MoveRunDeprecatedException::class);
        Move::runIfNoEnemies($battle, $fighter, [6, 0, 0], 1.0);
    }

    public function testRun_StopOnWater()
    {
        $fighter = $this->makeFighterMock([0, 0, 0], 4);
        $battle = $this->makeClearBattle([$fighter], []);
        $battle->getField()->setCell(3, 0, 0, new GameFieldCell(0, water: true));
        Move::runIfNoEnemies($battle, $fighter, [8, 0, 0], 1.0);
        $this->assertEquals([2, 0, 0], $fighter->getState()->getPosition(), 'Боец должен остановиться перед водой');
        $logs = \Mordheim\BattleLogger::getAll();
        $this->assertStringContainsString('не может бежать: на пути есть вода', implode("\n", $logs));
    }

    public function testRun_MaxDistance()
    {
        $fighter = $this->makeFighterMock([0, 0, 0], 5);
        $battle = $this->makeClearBattle([$fighter], []);
        Move::runIfNoEnemies($battle, $fighter, [20, 0, 0], 1.0);
        $this->assertEquals([10, 0, 0], $fighter->getState()->getPosition(), 'Боец должен пробежать ровно на 10 клеток (двойное движение)');
    }
}