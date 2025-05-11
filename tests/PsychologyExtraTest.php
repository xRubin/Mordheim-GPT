<?php

use Mordheim\Dice;
use Mordheim\Rule\Psychology;
use Mordheim\Status;

class PsychologyExtraTest extends MordheimTestCase
{
    private function makeFighterMock($ld, $state = Status::STANDING, $attacks = 1, $pos = [0, 0, 0])
    {
        $fighter = $this->createMock(\Mordheim\FighterInterface::class);
        $fighter->method('getLeadership')->willReturn($ld);
        $fighter->method('getAttacks')->willReturn($attacks);
        $fighter->method('getWeaponSkill')->willReturn(3); // по умолчанию, если нужно — можно расширить
        $fighter->method('getEquipmentManager')->willReturn(new \Mordheim\EquipmentManager([]));
        $fighter->method('getChargeRange')->willReturn(8);
        $fighter->method('getState')->willReturn(
            new \Mordheim\FighterState($pos, $this->createMock(\Mordheim\BattleStrategyInterface::class), 1, $state)
        );
        return $fighter;
    }

    public function testRoutTestLeaderAlive()
    {
        Dice::setTestRolls([4, 4]); // 8 <= 9 успех
        $leader = $this->makeFighterMock(9, Status::STANDING);
        $a = $this->makeFighterMock(6, Status::STANDING);
        $b = $this->makeFighterMock(6, Status::OUT_OF_ACTION);
        $warband = [$leader, $a, $b];
        $this->assertTrue(Psychology::routTest($warband, $leader));
    }

    public function testRoutTestLeaderDown()
    {
        Dice::setTestRolls([5, 4]); // 9 > 6 провал
        $leader = $this->makeFighterMock(9, Status::OUT_OF_ACTION);
        $alt = $this->makeFighterMock(6, Status::STANDING);
        $b = $this->makeFighterMock(6, Status::OUT_OF_ACTION);
        $warband = [$leader, $alt, $b];
        $this->assertFalse(Psychology::routTest($warband, $leader));
    }

    public function testAllAloneTestSuccess()
    {
        Dice::setTestRolls([4, 4]); // 8 <= 9 успех
        $hero = $this->makeFighterMock(9, Status::STANDING);
        $enemy1 = $this->makeFighterMock(6, Status::STANDING, 1, [1, 0, 0]);
        $enemy2 = $this->makeFighterMock(6, Status::STANDING, 1, [1, 1, 0]);
        $this->assertTrue(Psychology::allAloneTest($hero, [$enemy1, $enemy2], []));
    }

    public function testAllAloneTestFail()
    {
        Dice::setTestRolls([5, 4]); // 9 > 6 провал
        $hero = $this->makeFighterMock(6, Status::STANDING);
        $enemy1 = $this->makeFighterMock(6, Status::STANDING, 1, [1, 0, 0]);
        $enemy2 = $this->makeFighterMock(6, Status::STANDING, 1, [1, 1, 0]);
        $this->assertFalse(Psychology::allAloneTest($hero, [$enemy1, $enemy2], []));
    }

    public function testFearTest()
    {
        Dice::setTestRolls([4, 4]); // 8 <= 9 успех
        $hero = $this->makeFighterMock(9, Status::STANDING);
        $this->assertTrue(Psychology::fearTest($hero));
    }

    public function testFrenzyEffect()
    {
        $hero = $this->makeFighterMock(9, Status::FRENZY, 2);
        $enemy = $this->makeFighterMock(6, Status::STANDING, 1, [6, 0, 0]);
        $result = Psychology::frenzyEffect($hero, [$enemy]);
        $this->assertTrue($result['mustCharge']);
        $this->assertEquals(4, $result['attacks']);
    }

    public function testHatredEffect()
    {
        $this->assertTrue(Psychology::hatredEffect(true));
        $this->assertFalse(Psychology::hatredEffect(false));
    }

    public function testStupidityTest()
    {
        Dice::setTestRolls([4, 4]); // 8 <= 9 успех
        $hero = $this->makeFighterMock(9, Status::STANDING);
        $this->assertTrue(Psychology::stupidityTest($hero));
        Dice::setTestRolls([5, 4]); // 9 > 6 провал
        $hero = $this->makeFighterMock(6, Status::STANDING);
        $this->assertFalse(Psychology::stupidityTest($hero));
    }
}
