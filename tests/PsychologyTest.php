<?php

use Mordheim\EquipmentManager;
use Mordheim\Rule\Psychology;

class PsychologyTest extends MordheimTestCase
{
    private function makeFighterMock($ws, $ld)
    {
        $fighter = $this->createMock(\Mordheim\FighterInterface::class);
        $fighter->method('getWeaponSkill')->willReturn($ws);
        $fighter->method('getLeadership')->willReturn($ld);
        $fighter->method('getEquipmentManager')->willReturn(new EquipmentManager());
        $fighter->method('getState')->willReturn(
            new \Mordheim\FighterState([0, 0, 0], $this->createMock(\Mordheim\BattleStrategyInterface::class), 1)
        );
        return $fighter;
    }

    public function testLeadershipPass()
    {
        $fighter = $this->makeFighterMock(3, 12); // Ld 12, всегда успех
        $success = false;
        for ($i = 0; $i < 10; $i++) {
            if (Psychology::leadershipTest($fighter)) {
                $success = true;
                break;
            }
        }
        $this->assertTrue($success);
    }

    public function testLeadershipFail()
    {
        $fighter = $this->makeFighterMock(3, 2); // Ld 2, почти всегда провал
        $fail = false;
        for ($i = 0; $i < 10; $i++) {
            if (!Psychology::leadershipTest($fighter)) {
                $fail = true;
                break;
            }
        }
        $this->assertTrue($fail);
    }

    public function testFear()
    {
        $a = $this->makeFighterMock(2, 7);
        $d = $this->makeFighterMock(4, 7);
        $result = Psychology::testFear($a, $d);
        $this->assertIsBool($result);
    }

    public function testRout()
    {
        $f = $this->makeFighterMock(3, 7);
        $result = Psychology::testRout($f);
        $this->assertIsBool($result);
    }
}
