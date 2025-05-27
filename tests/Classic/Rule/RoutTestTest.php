<?php

namespace Classic\Rule;

use Mordheim\Band;
use Mordheim\Classic\Status;
use Mordheim\Classic\Strategy\AggressiveStrategy;

class RoutTestTest extends \MordheimTestCase
{
    private function makeLeader($state = Status::STANDING)
    {
        return new class (
            \Mordheim\Classic\Blank::REIKLAND_MERCENARY_CAPTAIN,
            \Mordheim\Classic\FighterAdvancement::empty(),
            new \Mordheim\Classic\EquipmentManager(),
            new \Mordheim\Classic\FighterState(
                [0, 0, 0],
                new AggressiveStrategy(),
                1,
                $state
            )
        ) extends \Mordheim\Classic\Fighter {
            public function getLeadership(bool $withBonus = true): int
            {
                return 2;
            }
        };
    }

    private function makeSuperLeader($state = Status::STANDING)
    {
        return new class (
            \Mordheim\Classic\Blank::REIKLAND_MERCENARY_CAPTAIN,
            \Mordheim\Classic\FighterAdvancement::empty(),
            new \Mordheim\Classic\EquipmentManager(),
            new \Mordheim\Classic\FighterState(
                [0, 0, 0],
                new AggressiveStrategy(),
                1,
                $state
            )
        ) extends \Mordheim\Classic\Fighter {
            public function getLeadership(bool $withBonus = true): int
            {
                return 12;
            }
        };
    }

    private function makeFighter($state = Status::STANDING)
    {
        return new \Mordheim\Classic\Fighter(
            \Mordheim\Classic\Blank::REIKLAND_CHAMPION,
            \Mordheim\Classic\FighterAdvancement::empty(),
            new \Mordheim\Classic\EquipmentManager(),
            new \Mordheim\Classic\FighterState(
                [0, 0, 0],
                new AggressiveStrategy(),
                1,
                $state
            )
        );
    }

    public function testRoutTestFailsWhenOOAOver25Percent()
    {
        \Mordheim\Dice::setTestRolls([3, 4]); // 3+4=7 > 2, тест должен провалиться
        $fighters = [
            $this->makeFighter(Status::OUT_OF_ACTION),
            $this->makeFighter(Status::OUT_OF_ACTION),
            // Лидер с Leadership=2, чтобы гарантированно провалить тест (2d6 > 2)
            $this->makeLeader(),
            $this->makeFighter(),
        ];
        $warband = new Band('Test', $fighters);
        $result = \Mordheim\Classic\Rule\RoutTest::apply($warband);
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
        $warband = new Band('Test', $fighters);
        $result = \Mordheim\Classic\Rule\RoutTest::apply($warband);
        $this->assertTrue($result, 'Rout test должен пройти при 25% OOA');
    }
}
