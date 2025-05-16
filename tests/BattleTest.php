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
            Mordheim\Blank::REIKLAND_CHAMPION,
            \Mordheim\FighterAdvancement::empty(),
            new \Mordheim\EquipmentManager(),
            new \Mordheim\FighterState(
                [0, 0, 0],
                $this->createMock(Mordheim\BattleStrategyInterface::class),
                1
            )
        );
        $f2 = new \Mordheim\Fighter(
            Mordheim\Blank::MARIENBURG_CHAMPION,
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

    public function testRunAwayInPanic_CenterGoesToNearestEdge()
    {
        $field = new \Mordheim\GameField(5, 5, 1);
        $warband = new \Mordheim\Warband('WB');
        $fighter = new \Mordheim\Fighter(
            Mordheim\Blank::REIKLAND_CHAMPION,
            \Mordheim\FighterAdvancement::empty(),
            new \Mordheim\EquipmentManager(),
            new \Mordheim\FighterState([2, 2, 0], $this->createMock(\Mordheim\BattleStrategyInterface::class), 1, \Mordheim\Status::PANIC)
        );
        $warband->fighters[] = $fighter;
        $battle = new \Mordheim\Battle($field, [$warband]);
        $battle->runAwayInPanic($fighter);
        $pos = $fighter->getState()->getPosition();
        // Должен быть ближе к одному из краёв (x=0, x=4, y=0, y=4)
        $this->assertTrue($pos[0] === 0 || $pos[0] === 4 || $pos[1] === 0 || $pos[1] === 4, 'Должен добежать до края');
    }

    public function testRunAwayInPanic_LeftEdgeStaysOrMovesAlongEdge()
    {
        $field = new \Mordheim\GameField(5, 5, 1);
        $warband = new \Mordheim\Warband('WB');
        $fighter = new \Mordheim\Fighter(
            Mordheim\Blank::REIKLAND_CHAMPION,
            \Mordheim\FighterAdvancement::empty(),
            new \Mordheim\EquipmentManager(),
            new \Mordheim\FighterState([0, 2, 0], $this->createMock(\Mordheim\BattleStrategyInterface::class), 1, \Mordheim\Status::PANIC)
        );
        $warband->fighters[] = $fighter;
        $battle = new \Mordheim\Battle($field, [$warband]);
        $battle->runAwayInPanic($fighter);
        $pos = $fighter->getState()->getPosition();
        $this->assertEquals(0, $pos[0], 'Должен остаться на левом краю по X');
    }

    public function testRunAwayInPanic_TopEdgeStaysOrMovesAlongEdge()
    {
        $field = new \Mordheim\GameField(5, 5, 1);
        $warband = new \Mordheim\Warband('WB');
        $fighter = new \Mordheim\Fighter(
            Mordheim\Blank::REIKLAND_CHAMPION,
            \Mordheim\FighterAdvancement::empty(),
            new \Mordheim\EquipmentManager(),
            new \Mordheim\FighterState([2, 0, 0], $this->createMock(\Mordheim\BattleStrategyInterface::class), 1, \Mordheim\Status::PANIC)
        );
        $warband->fighters[] = $fighter;
        $battle = new \Mordheim\Battle($field, [$warband]);
        $battle->runAwayInPanic($fighter);
        $pos = $fighter->getState()->getPosition();
        $this->assertEquals(0, $pos[1], 'Должен остаться на верхнем краю по Y');
    }

    public function testRunAwayInPanic_RightEdgeStaysOrMovesAlongEdge()
    {
        $field = new \Mordheim\GameField(5, 5, 1);
        $warband = new \Mordheim\Warband('WB');
        $fighter = new \Mordheim\Fighter(
            Mordheim\Blank::REIKLAND_CHAMPION,
            \Mordheim\FighterAdvancement::empty(),
            new \Mordheim\EquipmentManager(),
            new \Mordheim\FighterState([4, 2, 0], $this->createMock(\Mordheim\BattleStrategyInterface::class), 1, \Mordheim\Status::PANIC)
        );
        $warband->fighters[] = $fighter;
        $battle = new \Mordheim\Battle($field, [$warband]);
        $battle->runAwayInPanic($fighter);
        $pos = $fighter->getState()->getPosition();
        $this->assertEquals(4, $pos[0], 'Должен остаться на правом краю по X');
    }
}
