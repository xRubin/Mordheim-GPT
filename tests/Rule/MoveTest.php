<?php

use Mordheim\Battle;
use Mordheim\GameField;
use Mordheim\SpecialRule;
use Mordheim\Warband;
use Mordheim\Fighter;

class MoveTest extends MordheimTestCase
{
    private function makeFighterMock($pos, $move = 3, $initiative = 3, $skills = [])
    {
        $fighterMock = $this->createMock(Fighter::class);

        $fighterMock->method('getMoveRange')->willReturn($move);
        $fighterMock->method('getInitiative')->willReturn($initiative);
        $fighterMock->method('getClimbInitiative')->willReturn($initiative);
        $fighterMock->method('getState')->willReturn(
            new \Mordheim\FighterState($pos, $this->createMock(\Mordheim\BattleStrategyInterface::class), 1)
        );
        $fighterMock->method('getName')->willReturn('TestFighter');
        $fighterMock->method('getMovementWeights')->willReturn(function ($dx, $dy, $dz) {
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
                new Warband('Attackers', $attackerFighters),
                new Warband('Defenders', $defenderFighters)
            ]
        );
    }

    public function testMoveThroughWaterInitiativeSuccess()
    {
        $waterCell = new \Mordheim\FieldCell();
        $waterCell->water = true;
        $fighter = $this->makeFighterMock([0, 0, 0], 3, 5);
        \Mordheim\Dice::setTestRolls([4]);
        $battle = $this->makeClearBattle([$fighter], []);
        $battle->getField()->setCell(1, 0, 0, $waterCell);
        \Mordheim\Rule\Move::common($battle, $fighter, [2, 0, 0], 1.0);
        $this->assertEquals([2, 0, 0], $fighter->getState()->getPosition(), 'Боец должен пройти через воду при успешном броске');
        $logs = \Mordheim\BattleLogger::getAll();
        $this->assertStringContainsString('бросает Initiative для воды', implode("\n", $logs));
    }

    public function testMoveThroughWaterInitiativeFail()
    {
        $waterCell = new \Mordheim\FieldCell();
        $waterCell->water = true;
        $fighter = $this->makeFighterMock([0, 0, 0], 3, 3);
        \Mordheim\Dice::setTestRolls([5]);
        $battle = $this->makeClearBattle([$fighter], []);
        $battle->getField()->setCell(1, 0, 0, $waterCell);
        try {
            \Mordheim\Rule\Move::common($battle, $fighter, [2, 0, 0], 1.0);
            $this->fail('Ожидалось исключение MoveInitiativeRollFailedException');
        } catch (\Mordheim\Exceptions\MoveInitiativeRollFailedException $e) {
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
        \Mordheim\Rule\Move::common($battle, $fighter, $target, 1.0);
        $this->assertEquals($target, $fighter->getState()->getPosition());
    }

    public function testMoveAdvancedTowardsReachGoal()
    {
        $fighter = $this->makeFighterMock([0, 0, 0], 3);
        $target = [3, 0, 0];
        $battle = $this->makeClearBattle([$fighter], []);
        \Mordheim\Rule\Move::common($battle, $fighter, $target, 1.0);
        $this->assertEquals($target, $fighter->getState()->getPosition());
    }

    public function testMoveAdvancedTowardsPartialMove()
    {
        $fighter = $this->makeFighterMock([0, 0, 0], 2);
        $target = [4, 0, 0];
        $battle = $this->makeClearBattle([$fighter], []);
        \Mordheim\Rule\Move::common($battle, $fighter, $target, 1.0);
        $this->assertEquals([2, 0, 0], $fighter->getState()->getPosition());
    }

    public function testMoveAdvancedTowardsNoPath()
    {
        $cell = new \Mordheim\FieldCell();
        $cell->obstacle = true;
        $fighter = $this->makeFighterMock([0, 0, 0], 3);
        $target = [2, 0, 0];
        $battle = $this->makeClearBattle([$fighter], []);
        $battle->getField()->setCell(1, 0, 0, $cell);
        \Mordheim\Rule\Move::common($battle, $fighter, $target, 1.0);
        $this->assertEquals([2, 0, 0], $fighter->getState()->getPosition());
    }

    public function testSprintBonusMovement()
    {
        $fighter = $this->makeFighterMock([0, 0, 0], 3, 3, [SpecialRule::SPRINT]);
        $target = [10, 0, 0];
        $battle = $this->makeClearBattle([$fighter], []);
        \Mordheim\Rule\Move::common($battle, $fighter, $target, 1.0);
        $this->assertGreaterThanOrEqual(3, $fighter->getState()->getPosition()[0]);
        $this->assertLessThanOrEqual(9, $fighter->getState()->getPosition()[0]);
    }

    public function testRun_NoEnemiesIn8Inches()
    {
        $fighter = $this->makeFighterMock([0, 0, 0], 3);
        $battle = $this->makeClearBattle([$fighter], []);
        \Mordheim\Rule\Move::runIfNoEnemies($battle, $fighter, [6, 0, 0], 1.0);
        $this->assertEquals([6, 0, 0], $fighter->getState()->getPosition(), 'Боец должен пробежать на 6 клеток (двойное движение)');
    }

    public function testRun_EnemyWithin8Inches()
    {
        $fighter = $this->makeFighterMock([0, 0, 0], 3);
        $enemy = $this->makeFighterMock([5, 0, 0], 3);
        $battle = $this->makeClearBattle([$fighter], [$enemy]);
        $this->expectException(\Mordheim\Exceptions\MoveRunDeprecatedException::class);
        \Mordheim\Rule\Move::runIfNoEnemies($battle, $fighter, [6, 0, 0], 1.0);
    }

    public function testRun_StopOnWater()
    {
        $fighter = $this->makeFighterMock([0, 0, 0], 4);
        $battle = $this->makeClearBattle([$fighter], []);
        $waterCell = new \Mordheim\FieldCell();
        $waterCell->water = true;
        $battle->getField()->setCell(3, 0, 0, $waterCell);
        \Mordheim\Rule\Move::runIfNoEnemies($battle, $fighter, [8, 0, 0], 1.0);
        $this->assertEquals([2, 0, 0], $fighter->getState()->getPosition(), 'Боец должен остановиться перед водой');
        $logs = \Mordheim\BattleLogger::getAll();
        $this->assertStringContainsString('не может бежать: на пути есть вода', implode("\n", $logs));
    }

    public function testRun_MaxDistance()
    {
        $fighter = $this->makeFighterMock([0, 0, 0], 5);
        $battle = $this->makeClearBattle([$fighter], []);
        \Mordheim\Rule\Move::runIfNoEnemies($battle, $fighter, [20, 0, 0], 1.0);
        $this->assertEquals([10, 0, 0], $fighter->getState()->getPosition(), 'Боец должен пробежать ровно на 10 клеток (двойное движение)');
    }
}