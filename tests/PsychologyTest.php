<?php

use Mordheim\Characteristics;
use Mordheim\EquipmentManager;
use Mordheim\Rule\Psychology;
use PHPUnit\Framework\TestCase;

class PsychologyTest extends TestCase
{
    public function makeFighter($ws, $ld)
    {
        return new \Mordheim\Fighter(
            \Mordheim\Data\Blank::MARIENBURG_MERCENARY_CAPTAIN,
            new \Mordheim\FighterAdvancement(
                new Characteristics(0, $ws - 2, 0, 0, 0, 0, 0, 0, $ld - 6)
            ),
            new EquipmentManager(),
            new \Mordheim\FighterState(
                [0, 0, 0],
                $this->createMock(Mordheim\BattleStrategyInterface::class),
                2
            )
        );
    }

    public function testLeadershipPass()
    {
        $fighter = $this->makeFighter(3, 12); // Ld 12, всегда успех
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
        $fighter = $this->makeFighter(3, 2); // Ld 2, почти всегда провал
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
        $a = $this->makeFighter(2, 7);
        $d = $this->makeFighter(4, 7);
        $result = Psychology::testFear($a, $d);
        $this->assertIsBool($result);
    }

    public function testRout()
    {
        $f = $this->makeFighter(3, 7);
        $result = Psychology::testRout($f);
        $this->assertIsBool($result);
    }
}
