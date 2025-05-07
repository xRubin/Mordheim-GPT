<?php

use Mordheim\Status;
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

    private function makeSuperLeader($state = Status::STANDING)
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
                return 12;
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

    public function testRoutTestFailsWhenOOAOver25Percent()
    {
        $fighters = [
            $this->makeFighter(Status::OUT_OF_ACTION),
            $this->makeFighter(Status::OUT_OF_ACTION),
            // Лидер с Leadership=2, чтобы гарантированно провалить тест (2d6 > 2)
            $this->makeLeader(),
            $this->makeFighter(),
        ];
        $warband = new Warband('Test', $fighters);
        $result = \Mordheim\Rule\RoutTest::apply($warband);
        $this->assertFalse($result, 'Rout test должен провалиться при 50% OOA');
    }

    public function testRoutTestPassesWhenOOAUnder25Percent()
    {
        $fighters = [
            // Лидер с Leadership=12, чтобы гарантированно пройти тест (2d6 <= 12)
            $this->makeSuperLeader(),
            $this->makeFighter(),
            $this->makeFighter(),
            $this->makeFighter(Status::OUT_OF_ACTION),
        ];
        $warband = new Warband('Test', $fighters);
        $result = \Mordheim\Rule\RoutTest::apply($warband);
        $this->assertTrue($result, 'Rout test должен пройти при 25% OOA');
    }
}
