<?php

use Mordheim\CloseCombat;
use Mordheim\CloseCombatCollection;
use Mordheim\Exceptions\CloseCombatCollectionOutOfBoundsException;
use Mordheim\Fighter;
use PHPUnit\Framework\TestCase;

/**
 * Тесты для класса CloseCombatCollection
 */
class CloseCombatCollectionTest extends TestCase
{
    private CloseCombatCollection $collection;
    private Fighter $fighter1;
    private Fighter $fighter2;
    private Fighter $fighter3;

    protected function setUp(): void
    {
        parent::setUp();
        $this->collection = new CloseCombatCollection();

        $this->fighter1 = (new Fighter(
            Mordheim\Blank::REIKLAND_CHAMPION,
            \Mordheim\FighterAdvancement::empty(),
            new \Mordheim\EquipmentManager(),
            new \Mordheim\FighterState(
                [0, 0, 0],
                $this->createMock(Mordheim\BattleStrategyInterface::class),
                1
            )
        ))->setName('Fighter1');

        $this->fighter2 = (new Fighter(
            Mordheim\Blank::MIDDENHEIM_CHAMPION,
            \Mordheim\FighterAdvancement::empty(),
            new \Mordheim\EquipmentManager(),
            new \Mordheim\FighterState(
                [1, 0, 0],
                $this->createMock(Mordheim\BattleStrategyInterface::class),
                1
            )
        ))->setName('Fighter2');

        $this->fighter3 = (new Fighter(
            Mordheim\Blank::MARIENBURG_CHAMPION,
            \Mordheim\FighterAdvancement::empty(),
            new \Mordheim\EquipmentManager(),
            new \Mordheim\FighterState(
                [0, 0, 0],
                $this->createMock(Mordheim\BattleStrategyInterface::class),
                1
            )
        ))->setName('Fighter3');
    }

    public function testAddAndRemove(): void
    {
        $combat1 = new CloseCombat($this->fighter1, $this->fighter2);
        $combat2 = new CloseCombat($this->fighter2, $this->fighter3);

        // Добавление схваток
        $this->collection->add($combat1);
        $this->collection->add($combat2);

        // Проверяем наличие схваток
        $this->assertCount(2, $this->collection->getAll());
        $this->assertTrue($this->collection->isFighterInCombat($this->fighter1));
        $this->assertTrue($this->collection->isFighterInCombat($this->fighter2));
        $this->assertTrue($this->collection->isFighterInCombat($this->fighter3));

        // Удаление схватки
        $this->collection->remove($combat1);
        $this->assertCount(1, $this->collection->getAll());
        $this->assertFalse($this->collection->isFighterInCombat($this->fighter1));
        $this->assertTrue($this->collection->isFighterInCombat($this->fighter2));
        $this->assertTrue($this->collection->isFighterInCombat($this->fighter3));

        // Очистка коллекции
        $this->collection->clear();
        $this->assertCount(0, $this->collection->getAll());
        $this->assertFalse($this->collection->isFighterInCombat($this->fighter2));
    }

    public function testGetByFighter(): void
    {
        $combat1 = new CloseCombat($this->fighter1, $this->fighter2);
        $combat2 = new CloseCombat($this->fighter2, $this->fighter3);
        $combat3 = new CloseCombat($this->fighter1, $this->fighter3);

        // Добавляем все схватки
        $this->collection->add($combat1);
        $this->collection->add($combat2);
        $this->collection->add($combat3);

        // Проверяем получение схваток по бойцу
        $combatsForFighter1 = $this->collection->getByFighter($this->fighter1);
        $this->assertCount(2, $combatsForFighter1);
        $this->assertContains($combat1, $combatsForFighter1);
        $this->assertContains($combat3, $combatsForFighter1);

        $combatsForFighter2 = $this->collection->getByFighter($this->fighter2);
        $this->assertCount(2, $combatsForFighter2);
        $this->assertContains($combat1, $combatsForFighter2);
        $this->assertContains($combat2, $combatsForFighter2);
    }

    public function testRemoveNonExistingCombat(): void
    {
        $combat = new CloseCombat($this->fighter1, $this->fighter2);
        $this->expectException(CloseCombatCollectionOutOfBoundsException::class);
        $this->collection->remove($combat);
    }

    public function testClear(): void
    {
        $combat1 = new CloseCombat($this->fighter1, $this->fighter2);
        $combat2 = new CloseCombat($this->fighter2, $this->fighter3);

        // Добавляем схватки
        $this->collection->add($combat1);
        $this->collection->add($combat2);

        // Очищаем коллекцию
        $this->collection->clear();

        // Проверяем результат
        $this->assertCount(0, $this->collection->getAll());
        $this->assertFalse($this->collection->isFighterInCombat($this->fighter1));
        $this->assertFalse($this->collection->isFighterInCombat($this->fighter2));
        $this->assertFalse($this->collection->isFighterInCombat($this->fighter3));
    }
}
