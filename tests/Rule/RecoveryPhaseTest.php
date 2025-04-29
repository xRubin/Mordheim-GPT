<?php

use Mordheim\Characteristics;
use Mordheim\EquipmentManager;
use Mordheim\Fighter;
use Mordheim\FighterState;
use Mordheim\Strategy\AggressiveStrategy;
use Mordheim\Warband;
use PHPUnit\Framework\TestCase;

class RecoveryPhaseTest extends TestCase
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

    public function testRecoverPsychologyStateSetsPanicOnFailedRout()
    {
        $fighters = [
            $this->makeFighter('A'),
            $this->makeFighter('B'),
            $this->makeFighter('C'),
            $this->makeFighter('D', FighterState::OUT_OF_ACTION),
        ];
        $warband = new Warband('Test', $fighters);
        $fighters[0]->characteristics->leadership = 2; // лидер гарантированно провалит тест
        \Mordheim\Rule\RecoveryPhase::apply($warband, [$warband]);
        $this->assertEquals(FighterState::PANIC, $fighters[0]->state);
        $this->assertEquals(FighterState::PANIC, $fighters[1]->state);
        $this->assertEquals(FighterState::PANIC, $fighters[2]->state);
        $this->assertEquals(FighterState::OUT_OF_ACTION, $fighters[3]->state);
    }
}