<?php

use Mordheim\Battle;
use Mordheim\CloseCombat;
use Mordheim\Fighter;
use Mordheim\GameField;
use Mordheim\Warband;
use PHPUnit\Framework\TestCase;

class BattleTest extends TestCase
{
    public function setUp(): void
    {
        // Простое поле 3x3x1
        $this->field = new GameField(3, 3, 1);
        $this->warband1 = new Warband('WB1');
        $this->warband2 = new Warband('WB2');
        $char = new \Mordheim\Characteristics(4, 4, 4, 3, 3, 1, 3, 1, 7);
        $this->f1 = new Fighter('A', $char, [], new \Mordheim\EquipmentManager(), $this->createMock(\Mordheim\Strategy\BattleStrategy::class), [0,0,0]);
        $this->f2 = new Fighter('B', $char, [], new \Mordheim\EquipmentManager(), $this->createMock(\Mordheim\Strategy\BattleStrategy::class), [2,2,0]);
        $this->warband1->fighters[] = $this->f1;
        $this->warband2->fighters[] = $this->f2;
        $this->battle = new Battle($this->field, [$this->warband1, $this->warband2]);
    }

    public function testTurnOrderAndPhases()
    {
        $this->assertEquals(1, $this->battle->getTurn());
        $this->battle->playTurn();
        $this->assertEquals(2, $this->battle->getTurn());
    }

    public function testAddAndRemoveCombat()
    {
        $combat = new CloseCombat($this->f1, $this->f2);
        $this->battle->addCombat($combat);
        $this->assertCount(1, $this->battle->getActiveCombats());
        $this->battle->removeCombat($combat);
        $this->assertCount(0, $this->battle->getActiveCombats());
    }
}
