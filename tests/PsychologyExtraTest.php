<?php
use PHPUnit\Framework\TestCase;
use Mordheim\Fighter;
use Mordheim\Characteristics;
use Mordheim\FighterState;
use Mordheim\Psychology;
use Mordheim\Dice;

class PsychologyExtraTest extends TestCase
{
    protected function setUp(): void
    {
        Dice::setTestRolls([]);
    }
    protected function tearDown(): void
    {
        Dice::setTestRolls([]);
    }
    private function makeFighter($name, $ld, $state = FighterState::STANDING, $alive = true, $attacks = 1, $pos = [0,0,0]) {
        $char = new Characteristics(4,4,3,3,2,4,1,$attacks,$ld);
        return new Fighter($name, $char, [], new \Mordheim\EquipmentManager([]), new \Mordheim\Strategy\AggressiveStrategy(), $pos, $state, 0);
    }
    public function testRoutTestLeaderAlive() {
        Dice::setTestRolls([4,4]); // 8 <= 9 успех
        $leader = $this->makeFighter('Leader', 9, FighterState::STANDING, true);
        $a = $this->makeFighter('A', 6, FighterState::STANDING, true);
        $b = $this->makeFighter('B', 6, FighterState::OUT_OF_ACTION, false);
        $warband = [$leader, $a, $b];
        $this->assertTrue(Psychology::routTest($warband, $leader));
        \Mordheim\BattleLogger::print();
    }
    public function testRoutTestLeaderDown() {
        Dice::setTestRolls([5,4]); // 9 > 6 провал
        $leader = $this->makeFighter('Leader', 9, FighterState::OUT_OF_ACTION, false);
        $alt = $this->makeFighter('Alt', 6, FighterState::STANDING, true);
        $b = $this->makeFighter('B', 6, FighterState::OUT_OF_ACTION, false);
        $warband = [$leader, $alt, $b];
        $this->assertFalse(Psychology::routTest($warband, $leader));
        \Mordheim\BattleLogger::print();
    }
    public function testAllAloneTestSuccess() {
        Dice::setTestRolls([4,4]); // 8 <= 9 успех
        $hero = $this->makeFighter('Hero', 9, FighterState::STANDING, true);
        $enemy1 = $this->makeFighter('E1', 6, FighterState::STANDING, true, 1, [1,0,0]);
        $enemy2 = $this->makeFighter('E2', 6, FighterState::STANDING, true, 1, [1,1,0]);
        $this->assertTrue(Psychology::allAloneTest($hero, [$enemy1, $enemy2], []));
        \Mordheim\BattleLogger::print();
    }
    public function testAllAloneTestFail() {
        Dice::setTestRolls([5,4]); // 9 > 6 провал
        $hero = $this->makeFighter('Hero', 6, FighterState::STANDING, true);
        $enemy1 = $this->makeFighter('E1', 6, FighterState::STANDING, true, 1, [1,0,0]);
        $enemy2 = $this->makeFighter('E2', 6, FighterState::STANDING, true, 1, [1,1,0]);
        $this->assertFalse(Psychology::allAloneTest($hero, [$enemy1, $enemy2], []));
        \Mordheim\BattleLogger::print();
    }
    public function testFearTest() {
        Dice::setTestRolls([4,4]); // 8 <= 9 успех
        $hero = $this->makeFighter('Hero', 9, FighterState::STANDING, true);
        $this->assertTrue(Psychology::fearTest($hero));
        \Mordheim\BattleLogger::print();
    }
    public function testFrenzyEffect() {
        $hero = $this->makeFighter('Hero', 9, FighterState::STANDING, true, 2);
        $enemy = $this->makeFighter('E', 6, FighterState::STANDING, true, 1, [6,0,0]);
        $result = Psychology::frenzyEffect($hero, [$enemy]);
        $this->assertTrue($result['mustCharge']);
        $this->assertEquals(4, $result['attacks']);
        \Mordheim\BattleLogger::print();
    }
    public function testHatredEffect() {
        $this->assertTrue(Psychology::hatredEffect(true));
        $this->assertFalse(Psychology::hatredEffect(false));
    }
    public function testStupidityTest() {
        Dice::setTestRolls([4,4]); // 8 <= 9 успех
        $hero = $this->makeFighter('Hero', 9, FighterState::STANDING, true);
        $this->assertTrue(Psychology::stupidityTest($hero));
        \Mordheim\BattleLogger::print();
        Dice::setTestRolls([5,4]); // 9 > 6 провал
        $hero = $this->makeFighter('Hero', 6, FighterState::STANDING, true);
        $this->assertFalse(Psychology::stupidityTest($hero));
        \Mordheim\BattleLogger::print();
    }
}
