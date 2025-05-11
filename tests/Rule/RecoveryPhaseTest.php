<?php

use Mordheim\Status;
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

    private function makeLeader($state = Status::STANDING)
    {
        return new class (
            \Mordheim\Data\Blank::REIKLAND_MERCENARY_CAPTAIN,
            \Mordheim\FighterAdvancement::empty(),
            new \Mordheim\EquipmentManager(),
            new \Mordheim\FighterState(
                [0, 0, 0],
                new AggressiveStrategy(),
                1,
                $state
            )
        ) extends \Mordheim\Fighter {
            public function getLeadership(): int
            {
                return 2;
            }
        };
    }

    private function makeFighter($state = Status::STANDING)
    {
        return new \Mordheim\Fighter(
            \Mordheim\Data\Blank::REIKLAND_CHAMPION,
            \Mordheim\FighterAdvancement::empty(),
            new \Mordheim\EquipmentManager(),
            new \Mordheim\FighterState(
                [0, 0, 0],
                new AggressiveStrategy(),
                1,
                $state
            )
        );
    }

    public function testRecoverPsychologyStateSetsPanicOnFailedRout()
    {
        $fighters = [
            $this->makeLeader(), // лидер гарантированно провалит тест
            $this->makeFighter(),
            $this->makeFighter(),
            $this->makeFighter(Status::OUT_OF_ACTION),
        ];
        $warband = new Warband('Test', $fighters);
        $result = \Mordheim\Rule\RecoveryPhase::applyRoutTest($warband, [$warband]);
        $this->assertFalse($result);
        $this->assertEquals(Status::PANIC, $fighters[0]->getState()->getStatus());
        $this->assertEquals(Status::PANIC, $fighters[1]->getState()->getStatus());
        $this->assertEquals(Status::PANIC, $fighters[2]->getState()->getStatus());
        $this->assertEquals(Status::OUT_OF_ACTION, $fighters[3]->getState()->getStatus());
    }

    public function testApplyPsychologyPanicRecovery()
    {
        $fighter = $this->makeFighter(Status::PANIC);
        $warband = new Warband('Test', [$fighter]);
        // эмулируем успешный тест лидерства
        \Mordheim\Dice::setTestRolls([1, 1]);
        $result = \Mordheim\Rule\RecoveryPhase::applyPsychology($fighter, $warband, [$warband]);
        $this->assertTrue($result);
        $this->assertEquals(Status::STANDING, $fighter->getState()->getStatus());
    }

    public function testApplyPsychologyPanicFail()
    {
        $fighter = $this->makeFighter(Status::PANIC);
        $warband = new Warband('Test', [$fighter]);
        // эмулируем провал теста лидерства
        \Mordheim\Dice::setTestRolls([6, 6]);
        $result = \Mordheim\Rule\RecoveryPhase::applyPsychology($fighter, $warband, [$warband]);
        $this->assertFalse($result);
        $this->assertEquals(Status::PANIC, $fighter->getState()->getStatus());
    }
}