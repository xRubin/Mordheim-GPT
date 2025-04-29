<?php

use Mordheim\Characteristics;
use Mordheim\EquipmentManager;
use Mordheim\Fighter;
use Mordheim\FighterState;
use Mordheim\Strategy\AggressiveStrategy;
use Mordheim\Warband;
use PHPUnit\Framework\TestCase;

class RoutTestTest extends TestCase
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

    private function makeFighter($name, $state = FighterState::STANDING)
    {
        $char = new Characteristics(4, 3, 3, 3, 3, 1, 3, 1, 7);
        $eq = new EquipmentManager([], []);
        $fighter = new Fighter($name, $char, [], $eq, new AggressiveStrategy(), [0, 0, 0], $state);
        $fighter->alive = ($state !== FighterState::OUT_OF_ACTION);
        return $fighter;
    }

    public function testRoutTestFailsWhenOOAOver25Percent()
    {
        $fighters = [
            $this->makeFighter('A', FighterState::OUT_OF_ACTION),
            $this->makeFighter('B', FighterState::OUT_OF_ACTION),
            $this->makeFighter('C'),
            $this->makeFighter('D'),
        ];
        $warband = new Warband('Test', $fighters);
        // Лидер с Leadership=2, чтобы гарантированно провалить тест (2d6 > 2)
        $fighters[2]->characteristics->leadership = 2;
        $result = \Mordheim\Rule\RoutTest::apply($warband);
        $this->assertFalse($result, 'Rout test должен провалиться при 50% OOA');
    }

    public function testRoutTestPassesWhenOOAUnder25Percent()
    {
        $fighters = [
            $this->makeFighter('A'),
            $this->makeFighter('B'),
            $this->makeFighter('C'),
            $this->makeFighter('D', FighterState::OUT_OF_ACTION),
        ];
        $warband = new Warband('Test', $fighters);
        // Лидер с Leadership=12, чтобы гарантированно пройти тест (2d6 <= 12)
        $fighters[0]->characteristics->leadership = 12;
        $result = \Mordheim\Rule\RoutTest::apply($warband);
        $this->assertTrue($result, 'Rout test должен пройти при 25% OOA');
    }
}
