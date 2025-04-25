<?php

use Mordheim\Characteristics;
use Mordheim\EquipmentManager;
use Mordheim\Fighter;
use Mordheim\Rule\Psychology;
use Mordheim\Strategy\AggressiveStrategy;
use PHPUnit\Framework\TestCase;

class PsychologyTest extends TestCase
{
    public function makeFighter($ws, $ld) {
        $char = new Characteristics(
    4,      // movement
    $ws,    // weaponSkill
    3,      // ballisticSkill
    3,      // strength
    3,      // toughness
    2,      // wounds
    4,      // initiative
    1,      // attacks
    $ld     // leadership
);
        return new Fighter('Test', $char, [], new EquipmentManager([]), new AggressiveStrategy());
    }

    public function testLeadershipPass() {
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

    public function testLeadershipFail() {
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

    public function testFear() {
        $a = $this->makeFighter(2, 7);
        $d = $this->makeFighter(4, 7);
        $result = Psychology::testFear($a, $d);
        $this->assertIsBool($result);
    }

    public function testTerror() {
        $f = $this->makeFighter(3, 7);
        $result = Psychology::testTerror($f);
        $this->assertIsBool($result);
    }

    public function testRout() {
        $f = $this->makeFighter(3, 7);
        $result = Psychology::testRout($f);
        $this->assertIsBool($result);
    }
}
