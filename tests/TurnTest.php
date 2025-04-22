<?php
// Заглушка для printLog, чтобы не было ошибки в тестах
function printLog($msg) {}

use PHPUnit\Framework\TestCase;
use Mordheim\Warband;
use Mordheim\Fighter;
use Mordheim\Characteristics;
use Mordheim\EquipmentManager;
use Mordheim\Strategy\AggressiveStrategy;
use Mordheim\FighterState;
use Mordheim\Turn;

function dummyFighter($name, $state = FighterState::STANDING) {
    $char = new Characteristics(4, 3, 3, 3, 3, 1, 3, 1, 7);
    $eq = new EquipmentManager([], []);
    $fighter = new Fighter($name, $char, [], $eq, new AggressiveStrategy(), [0,0,0], $state);
    $fighter->alive = ($state !== FighterState::OUT_OF_ACTION);
    return $fighter;
}

class TurnTest extends TestCase {
    public function testRoutTestFailsWhenOOAOver25Percent() {
        $fighters = [
            dummyFighter('A', FighterState::OUT_OF_ACTION),
            dummyFighter('B', FighterState::OUT_OF_ACTION),
            dummyFighter('C'),
            dummyFighter('D'),
        ];
        $warband = new Warband('Test', $fighters);
        // Лидер с Leadership=2, чтобы гарантированно провалить тест (2d6 > 2)
        $fighters[2]->characteristics->leadership = 2;
        $result = Turn::routTest($warband);
        $this->assertFalse($result, 'Rout test должен провалиться при 50% OOA');
    }

    public function testRoutTestPassesWhenOOAUnder25Percent() {
        $fighters = [
            dummyFighter('A'),
            dummyFighter('B'),
            dummyFighter('C'),
            dummyFighter('D', FighterState::OUT_OF_ACTION),
        ];
        $warband = new Warband('Test', $fighters);
        // Лидер с Leadership=12, чтобы гарантированно пройти тест (2d6 <= 12)
        $fighters[0]->characteristics->leadership = 12;
        $result = Turn::routTest($warband);
        $this->assertTrue($result, 'Rout test должен пройти при 25% OOA');
    }

    public function testRecoverPsychologyStateSetsPanicOnFailedRout() {
        $fighters = [
            dummyFighter('A'),
            dummyFighter('B'),
            dummyFighter('C'),
            dummyFighter('D', FighterState::OUT_OF_ACTION),
        ];
        $warband = new Warband('Test', $fighters);
        $fighters[0]->characteristics->leadership = 2; // лидер гарантированно провалит тест
        Turn::recoverPsychologyState($warband, [$warband]);
        $this->assertEquals(FighterState::PANIC, $fighters[0]->state);
        $this->assertEquals(FighterState::PANIC, $fighters[1]->state);
        $this->assertEquals(FighterState::PANIC, $fighters[2]->state);
        $this->assertEquals(FighterState::OUT_OF_ACTION, $fighters[3]->state);
    }
}
