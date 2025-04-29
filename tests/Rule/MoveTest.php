<?php

use Mordheim\Characteristics;
use Mordheim\EquipmentManager;
use Mordheim\Fighter;
use Mordheim\FighterState;
use Mordheim\Strategy\BattleStrategy;
use PHPUnit\Framework\TestCase;
use Mordheim\Data\Skills;

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
            $this->createMock(BattleStrategy::class), $pos, FighterState::STANDING
        );
    }

    public function testMoveThroughWaterInitiativeSuccess()
    {
        \Mordheim\BattleLogger::clear();
        $field = new \Mordheim\GameField();
        // Клетка [1,0,0] — вода
        $waterCell = new \Mordheim\FieldCell();
        $waterCell->water = true;
        $field->setCell(1, 0, 0, $waterCell);
        $fighter = $this->makeFighter([0, 0, 0], 3);
        $fighter->characteristics->initiative = 5;
        \Mordheim\Dice::setTestRolls([4]); // успех
        \Mordheim\Rule\Move::apply($field, $fighter, [2, 0, 0], [], false);
        $this->assertEquals([2, 0, 0], $fighter->position, 'Боец должен пройти через воду при успешном броске');
        $logs = \Mordheim\BattleLogger::getAll();
        $this->assertStringContainsString('бросает Initiative для воды', implode("\n", $logs));
        \Mordheim\Dice::setTestRolls([]);
    }

    public function testMoveThroughWaterInitiativeFail()
    {
        \Mordheim\BattleLogger::clear();
        $field = new \Mordheim\GameField();
        $waterCell = new \Mordheim\FieldCell();
        $waterCell->water = true;
        $field->setCell(1, 0, 0, $waterCell);
        $fighter = $this->makeFighter([0, 0, 0], 3);
        $fighter->characteristics->initiative = 3;
        \Mordheim\Dice::setTestRolls([5]); // провал
        try {
            \Mordheim\Rule\Move::apply($field, $fighter, [2, 0, 0], [], false);
            $this->fail('Ожидалось исключение PathfinderInitiativeRollFailedException');
        } catch (\Mordheim\Exceptions\PathfinderInitiativeRollFailedException $e) {
            // Ожидаемое поведение
        }
        $this->assertEquals([1, 0, 0], $fighter->position, 'Боец должен остановиться на воде при провале инициативы');
        $logs = \Mordheim\BattleLogger::getAll();
        $this->assertStringContainsString('Провал Initiative в воде', implode("\n", $logs));
        \Mordheim\Dice::setTestRolls([]);
    }

    public function testMoveAdvancedTowardsDiagonal()
    {
        $field = new \Mordheim\GameField();
        $fighter = $this->makeFighter([0, 0, 0], 2); // move > 1.4
        $target = [1, 1, 0];
        \Mordheim\Rule\Move::apply($field, $fighter, $target, [], false);
        $this->assertEquals($target, $fighter->position);
    }

    public function testMoveAdvancedTowardsReachGoal()
    {
        $field = new \Mordheim\GameField();
        $fighter = $this->makeFighter([0, 0, 0], 3);
        $target = [3, 0, 0];
        \Mordheim\Rule\Move::apply($field, $fighter, $target, [], false);
        $this->assertEquals($target, $fighter->position);
    }

    public function testMoveAdvancedTowardsPartialMove()
    {
        $field = new \Mordheim\GameField();
        $fighter = $this->makeFighter([0, 0, 0], 2);
        $target = [4, 0, 0];
        \Mordheim\Rule\Move::apply($field, $fighter, $target, [], true);
        $this->assertEquals([2, 0, 0], $fighter->position);
    }

    public function testMoveAdvancedTowardsNoPath()
    {
        $field = new \Mordheim\GameField();
        $cell = new \Mordheim\FieldCell();
        $cell->obstacle = true;
        $field->setCell(1, 0, 0, $cell);
        $fighter = $this->makeFighter([0, 0, 0], 3);
        $target = [2, 0, 0];
        \Mordheim\Rule\Move::apply($field, $fighter, $target, [], true);
        // Ожидаем, что дойдет по диагонали
        $this->assertEquals([2, 0, 0], $fighter->position);
    }


    public function testSprintBonusMovement()
    {
        // Sprint skill: movement increases by D6
        $field = new \Mordheim\GameField();
        $fighter = $this->makeFighter([0, 0, 0], 3, [Skills::getByName('Sprint')]);
        $target = [10, 0, 0];
        \Mordheim\Rule\Move::apply($field, $fighter, $target);
        $this->assertGreaterThanOrEqual(3, $fighter->position[0]);
        $this->assertLessThanOrEqual(9, $fighter->position[0]); // не больше чем 3+6
    }
}