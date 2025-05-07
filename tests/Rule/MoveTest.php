<?php

use Mordheim\AdvancementInterface;
use Mordheim\Battle;
use Mordheim\BlankInterface;
use Mordheim\Data\Skills;
use Mordheim\EquipmentManager;
use Mordheim\FighterStateInterface;
use Mordheim\GameField;
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

    private function makeFighter($pos, $move = 3, $initiative = 3, $skills = [])
    {
        return new class(
            \Mordheim\Data\Blank::REIKLAND_CHAMPION,
            new \Mordheim\FighterAdvancement(\Mordheim\Characteristics::empty(), $skills),
            new \Mordheim\EquipmentManager(),
            new \Mordheim\FighterState(
                $pos,
                $this->createMock(\Mordheim\Strategy\BattleStrategyInterface::class),
                1
            ),
            $move,
            $initiative
        ) extends \Mordheim\Fighter {
            public function __construct(
                private readonly BlankInterface       $blank,
                private readonly AdvancementInterface $advancement,
                private readonly EquipmentManager     $equipmentManager,
                private ?FighterStateInterface        $fighterState = null,
                private int                           $move,
                private int                           $initiative
            )
            {
                parent::__construct(
                    $blank,
                    $advancement,
                    $equipmentManager,
                    $fighterState,
                );
            }

            public function getMovement(): int
            {
                return $this->move;
            }

            public function getInitiative(): int
            {
                return $this->initiative;
            }
        };
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
        $fighter = $this->makeFighter([0, 0, 0], 3, 5);
        \Mordheim\Dice::setTestRolls([4]); //
        $battle = $this->makeClearBattle([$fighter], []);
        // Клетка [1,0,0] — вода
        $battle->getField()->setCell(1, 0, 0, $waterCell);
        \Mordheim\Rule\Move::apply($battle, $fighter, [2, 0, 0], 1.0, [], false);
        $this->assertEquals([2, 0, 0], $fighter->getState()->getPosition(), 'Боец должен пройти через воду при успешном броске');
        $logs = \Mordheim\BattleLogger::getAll();
        $this->assertStringContainsString('бросает Initiative для воды', implode("\n", $logs));
    }

    public function testMoveThroughWaterInitiativeFail()
    {
        $waterCell = new \Mordheim\FieldCell();
        $waterCell->water = true;
        $fighter = $this->makeFighter([0, 0, 0], 3, 3);
        \Mordheim\Dice::setTestRolls([5]); // провал
        $battle = $this->makeClearBattle([$fighter], []);
        // Клетка [1,0,0] — вода
        $battle->getField()->setCell(1, 0, 0, $waterCell);
        $this->expectException(\Mordheim\Exceptions\PathfinderInitiativeRollFailedException::class);
        \Mordheim\Rule\Move::apply($battle, $fighter, [2, 0, 0], 1.0, [], false);
        $this->assertEquals([1, 0, 0], $fighter->getState()->getPosition(), 'Боец должен остановиться на воде при провале инициативы');
        $logs = \Mordheim\BattleLogger::getAll();
        $this->assertStringContainsString('Провал Initiative в воде', implode("\n", $logs));
    }

    public function testMoveAdvancedTowardsDiagonal()
    {
        $fighter = $this->makeFighter([0, 0, 0], 2); // move > 1.4
        $target = [1, 1, 0];
        $battle = $this->makeClearBattle([$fighter], []);
        \Mordheim\Rule\Move::apply($battle, $fighter, $target, 1.0, [], false);
        $this->assertEquals($target, $fighter->getState()->getPosition());
    }

    public function testMoveAdvancedTowardsReachGoal()
    {
        $fighter = $this->makeFighter([0, 0, 0], 3);
        $target = [3, 0, 0];
        $battle = $this->makeClearBattle([$fighter], []);
        \Mordheim\Rule\Move::apply($battle, $fighter, $target, 1.0, [], false);
        $this->assertEquals($target, $fighter->getState()->getPosition());
    }

    public function testMoveAdvancedTowardsPartialMove()
    {
        $fighter = $this->makeFighter([0, 0, 0], 2);
        $target = [4, 0, 0];
        $battle = $this->makeClearBattle([$fighter], []);
        \Mordheim\Rule\Move::apply($battle, $fighter, $target, 1.0, [], true);
        $this->assertEquals([2, 0, 0], $fighter->getState()->getPosition());
    }

    public function testMoveAdvancedTowardsNoPath()
    {
        $cell = new \Mordheim\FieldCell();
        $cell->obstacle = true;
        $fighter = $this->makeFighter([0, 0, 0], 3);
        $target = [2, 0, 0];
        $battle = $this->makeClearBattle([$fighter], []);
        $battle->getField()->setCell(1, 0, 0, $cell);
        \Mordheim\Rule\Move::apply($battle, $fighter, $target, 1.0, [], true);
        // Ожидаем, что дойдет по диагонали
        $this->assertEquals([2, 0, 0], $fighter->getState()->getPosition());
    }


    public function testSprintBonusMovement()
    {
        // Sprint skill: movement increases by D6
        $fighter = $this->makeFighter([0, 0, 0], 3, 3, [Skills::getByName('Sprint')]);
        $target = [10, 0, 0];
        $battle = $this->makeClearBattle([$fighter], []);
        \Mordheim\Rule\Move::apply($battle, $fighter, $target, 1.0);
        $this->assertGreaterThanOrEqual(3, $fighter->getState()->getPosition()[0]);
        $this->assertLessThanOrEqual(9, $fighter->getState()->getPosition()[0]); // не больше чем 3+6
    }
}