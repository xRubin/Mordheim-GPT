<?php

use Mordheim\Battle;
use Mordheim\CloseCombat;
use Mordheim\GameField;
use Mordheim\Warband;
use PHPUnit\Framework\TestCase;

class BattleTest extends TestCase
{
    private function makeBattle(): Battle
    {
        // Простое поле 3x3x1
        $field = new GameField(3, 3, 1);
        $warband1 = new Warband('WB1');
        $warband2 = new Warband('WB2');
        $f1 = new \Mordheim\Fighter(
            \Mordheim\Data\Blank::REIKLAND_CHAMPION,
            \Mordheim\FighterAdvancement::empty(),
            new \Mordheim\EquipmentManager(),
            new \Mordheim\FighterState(
                [0, 0, 0],
                $this->createMock(Mordheim\BattleStrategyInterface::class),
                1
            )
        );
        $f2 = new \Mordheim\Fighter(
            \Mordheim\Data\Blank::MARIENBURG_CHAMPION,
            \Mordheim\FighterAdvancement::empty(),
            new \Mordheim\EquipmentManager(),
            new \Mordheim\FighterState(
                [2, 2, 2],
                $this->createMock(Mordheim\BattleStrategyInterface::class),
                1
            )
        );
        $warband1->fighters[] = $f1;
        $warband2->fighters[] = $f2;
        return new Battle($field, [$warband1, $warband2]);
    }

    public function testTurnOrderAndPhases()
    {
        $battle = $this->makeBattle();
        $this->assertEquals(1, $battle->getTurn());
        $battle->playTurn();
        $this->assertEquals(2, $battle->getTurn());
    }

    public function testAddAndRemoveCombat()
    {
        $battle = $this->makeBattle();
        $combat = new CloseCombat($battle->getFighters()[0], $battle->getFighters()[1]);
        $battle->getActiveCombats()->add($combat);
        $this->assertCount(1, $battle->getActiveCombats()->getAll());
        $battle->getActiveCombats()->remove($combat);
        $this->assertCount(0, $battle->getActiveCombats()->getAll());
    }
}
