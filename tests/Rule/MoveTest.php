<?php

use Mordheim\Battle;
use Mordheim\Characteristics;
use Mordheim\Data\Skills;
use Mordheim\EquipmentManager;
use Mordheim\Fighter;
use Mordheim\FighterState;
use Mordheim\GameField;
use Mordheim\Strategy\BattleStrategyInterface;
use Mordheim\Warband;
use PHPUnit\Framework\TestCase;

class MoveTest extends TestCase
{
    public function setUp(): void
    {
        \Mordheim\Dice::setTestRolls([]);
        \Mordheim\BattleLogger::clear();
        \Mordheim\BattleLogger::add("### Test: {$this->name()}");
    }

    public function tearDown(): void
    {
        \Mordheim\Dice::setTestRolls([]);
        \Mordheim\BattleLogger::print();
    }

    private function makeFighter($pos, $move = 3, $skills = [])
    {
        $char = new Characteristics(
            $move, // movement
            1, // weaponSkill
            1, // ballisticSkill
            1, // strength
            1, // toughness
            1, // wounds
            5, // initiative
            1, // attacks
            7  // leadership
        );
        return new Fighter(
            'Test', $char, $skills, new EquipmentManager(),
            $this->createMock(BattleStrategyInterface::class), $pos, FighterState::STANDING
        );
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
        $fighter = $this->makeFighter([0, 0, 0], 3);
        $fighter->characteristics->initiative = 5;
        \Mordheim\Dice::setTestRolls([4]); //
        $battle = $this->makeClearBattle([$fighter], []);
        // Клетка [1,0,0] — вода
        $battle->getField()->setCell(1, 0, 0, $waterCell);
        \Mordheim\Rule\Move::apply($battle, $fighter, [2, 0, 0], [], false);
        $this->assertEquals([2, 0, 0], $fighter->position, 'Боец должен пройти через воду при успешном броске');
        $logs = \Mordheim\BattleLogger::getAll();
        $this->assertStringContainsString('бросает Initiative для воды', implode("\n", $logs));
    }

    public function testMoveThroughWaterInitiativeFail()
    {
        $waterCell = new \Mordheim\FieldCell();
        $waterCell->water = true;
        $fighter = $this->makeFighter([0, 0, 0], 3);
        $fighter->characteristics->initiative = 3;
        \Mordheim\Dice::setTestRolls([5]); // провал
        $battle = $this->makeClearBattle([$fighter], []);
        // Клетка [1,0,0] — вода
        $battle->getField()->setCell(1, 0, 0, $waterCell);
        $this->expectException(\Mordheim\Exceptions\PathfinderInitiativeRollFailedException::class);
        \Mordheim\Rule\Move::apply($battle, $fighter, [2, 0, 0], [], false);
        $this->assertEquals([1, 0, 0], $fighter->position, 'Боец должен остановиться на воде при провале инициативы');
        $logs = \Mordheim\BattleLogger::getAll();
        $this->assertStringContainsString('Провал Initiative в воде', implode("\n", $logs));
    }

    public function testMoveAdvancedTowardsDiagonal()
    {
        $fighter = $this->makeFighter([0, 0, 0], 2); // move > 1.4
        $target = [1, 1, 0];
        $battle = $this->makeClearBattle([$fighter], []);
        \Mordheim\Rule\Move::apply($battle, $fighter, $target, [], false);
        $this->assertEquals($target, $fighter->position);
    }

    public function testMoveAdvancedTowardsReachGoal()
    {
        $fighter = $this->makeFighter([0, 0, 0], 3);
        $target = [3, 0, 0];
        $battle = $this->makeClearBattle([$fighter], []);
        \Mordheim\Rule\Move::apply($battle, $fighter, $target, [], false);
        $this->assertEquals($target, $fighter->position);
    }

    public function testMoveAdvancedTowardsPartialMove()
    {
        $fighter = $this->makeFighter([0, 0, 0], 2);
        $target = [4, 0, 0];
        $battle = $this->makeClearBattle([$fighter], []);
        \Mordheim\Rule\Move::apply($battle, $fighter, $target, [], true);
        $this->assertEquals([2, 0, 0], $fighter->position);
    }

    public function testMoveAdvancedTowardsNoPath()
    {
        $cell = new \Mordheim\FieldCell();
        $cell->obstacle = true;
        $fighter = $this->makeFighter([0, 0, 0], 3);
        $target = [2, 0, 0];
        $battle = $this->makeClearBattle([$fighter], []);
        $battle->getField()->setCell(1, 0, 0, $cell);
        \Mordheim\Rule\Move::apply($battle, $fighter, $target, [], true);
        // Ожидаем, что дойдет по диагонали
        $this->assertEquals([2, 0, 0], $fighter->position);
    }


    public function testSprintBonusMovement()
    {
        // Sprint skill: movement increases by D6
        $fighter = $this->makeFighter([0, 0, 0], 3, [Skills::getByName('Sprint')]);
        $target = [10, 0, 0];
        $battle = $this->makeClearBattle([$fighter], []);
        \Mordheim\Rule\Move::apply($battle, $fighter, $target);
        $this->assertGreaterThanOrEqual(3, $fighter->position[0]);
        $this->assertLessThanOrEqual(9, $fighter->position[0]); // не больше чем 3+6
    }
}