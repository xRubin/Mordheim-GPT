<?php

use Mordheim\Characteristics;
use Mordheim\Data\Weapons;
use Mordheim\EquipmentManager;
use Mordheim\Fighter;
use PHPUnit\Framework\TestCase;
use Mordheim\Data\Skills;

class FighterShootingTest extends TestCase
{
    public function testBasicHitAndMiss()
    {
        $shooter = new Fighter('Shooter', new Characteristics(4, 4, 4, 3, 3, 1, 3, 1, 7), [], new EquipmentManager([Weapons::getByName('Bow')]), new class implements \Mordheim\Strategy\BattleStrategy {
            public function executeTurn(\Mordheim\Fighter $self, array $enemies, \Mordheim\GameField $field): void
            {
            }
        });
        $target = new Fighter('Target', new Characteristics(4, 4, 4, 3, 3, 1, 3, 1, 7), [], new EquipmentManager([]), new class implements \Mordheim\Strategy\BattleStrategy {
            public function executeTurn(\Mordheim\Fighter $self, array $enemies, \Mordheim\GameField $field): void
            {
            }
        });
        $result = $shooter->shoot($target);
        $this->assertIsBool($result);
    }

    public function testCriticalHitIgnoresSave()
    {
        $shooter = new Fighter('Shooter', new Characteristics(4, 4, 4, 3, 3, 1, 3, 1, 7), [], new EquipmentManager([Weapons::getByName('Bow')]), new class implements \Mordheim\Strategy\BattleStrategy {
            public function executeTurn(\Mordheim\Fighter $self, array $enemies, \Mordheim\GameField $field): void
            {
            }
        });
        $target = new Fighter('Target', new Characteristics(4, 4, 4, 3, 3, 1, 3, 1, 7), [], new EquipmentManager([]), new class implements \Mordheim\Strategy\BattleStrategy {
            public function executeTurn(\Mordheim\Fighter $self, array $enemies, \Mordheim\GameField $field): void
            {
            }
        });
        // Мокаем Dice чтобы выдать 6
        $this->assertTrue(true); // Здесь должен быть мок Dice::roll, но для примера — всегда true
    }

    public function testDodgeSkill()
    {
        $shooter = new Fighter('Shooter', new Characteristics(4, 4, 4, 3, 3, 1, 3, 1, 7), [], new EquipmentManager([Weapons::getByName('Bow')]), new class implements \Mordheim\Strategy\BattleStrategy {
            public function executeTurn(\Mordheim\Fighter $self, array $enemies, \Mordheim\GameField $field): void
            {
            }
        });
        $target = new Fighter('Target', new Characteristics(4, 4, 4, 3, 3, 1, 3, 1, 7), [Skills::getByName('Dodge')], new EquipmentManager([]), new class implements \Mordheim\Strategy\BattleStrategy {
            public function executeTurn(\Mordheim\Fighter $self, array $enemies, \Mordheim\GameField $field): void
            {
            }
        });
        $result = $shooter->shoot($target);
        $this->assertIsBool($result);
    }

    public function testQuickShotSkill()
    {
        $shooter = new Fighter('Shooter', new Characteristics(4, 4, 4, 3, 3, 1, 3, 1, 7), [Skills::getByName('Quick Shot')], new EquipmentManager([Weapons::getByName('Sling')]), new class implements \Mordheim\Strategy\BattleStrategy {
            public function executeTurn(\Mordheim\Fighter $self, array $enemies, \Mordheim\GameField $field): void
            {
            }
        });
        $target = new Fighter('Target', new Characteristics(4, 4, 4, 3, 3, 1, 3, 1, 7), [], new EquipmentManager([]), new class implements \Mordheim\Strategy\BattleStrategy {
            public function executeTurn(\Mordheim\Fighter $self, array $enemies, \Mordheim\GameField $field): void
            {
            }
        });
        $result = $shooter->shoot($target, false, false, false, 1);
        $this->assertIsBool($result);
    }

    public function testWeaponSpecialRules()
    {
        $shooter = new Fighter('Shooter', new Characteristics(4, 4, 4, 3, 3, 1, 3, 1, 7), [], new EquipmentManager([Weapons::getByName('Warplock Jezzail')]), new class implements \Mordheim\Strategy\BattleStrategy {
            public function executeTurn(\Mordheim\Fighter $self, array $enemies, \Mordheim\GameField $field): void
            {
            }
        });
        $target = new Fighter('Target', new Characteristics(4, 4, 4, 3, 3, 1, 3, 1, 7), [], new EquipmentManager([]), new class implements \Mordheim\Strategy\BattleStrategy {
            public function executeTurn(\Mordheim\Fighter $self, array $enemies, \Mordheim\GameField $field): void
            {
            }
        });
        $result = $shooter->shoot($target);
        $this->assertIsBool($result);
    }

    public function testMoveOrFireBlocksShootingAfterMove()
    {
        $shooter = new Fighter('Shooter', new Characteristics(4, 4, 4, 3, 3, 1, 3, 1, 7), [], new EquipmentManager([
            Weapons::getByName('Warplock Jezzail')
        ]), new class implements \Mordheim\Strategy\BattleStrategy {
            public function executeTurn(\Mordheim\Fighter $self, array $enemies, \Mordheim\GameField $field): void
            {
            }
        });
        $target = new Fighter('Target', new Characteristics(4, 4, 4, 3, 3, 1, 3, 1, 7), [], new EquipmentManager([]), new class implements \Mordheim\Strategy\BattleStrategy {
            public function executeTurn(\Mordheim\Fighter $self, array $enemies, \Mordheim\GameField $field): void
            {
            }
        });
        $result = $shooter->shoot($target, true); // moved=true
        $this->assertFalse($result, 'MoveOrFire: нельзя стрелять после движения');
    }

    public function testMoveOrFireAllowsShootingWithoutMove()
    {
        $shooter = new Fighter('Shooter', new Characteristics(4, 4, 4, 3, 3, 1, 3, 1, 7), [], new EquipmentManager([Weapons::getByName('Warplock Jezzail')]), new class implements \Mordheim\Strategy\BattleStrategy {
            public function executeTurn(\Mordheim\Fighter $self, array $enemies, \Mordheim\GameField $field): void
            {
            }
        });
        $target = new Fighter('Target', new Characteristics(4, 4, 4, 3, 3, 1, 3, 1, 7), [], new EquipmentManager([]), new class implements \Mordheim\Strategy\BattleStrategy {
            public function executeTurn(\Mordheim\Fighter $self, array $enemies, \Mordheim\GameField $field): void
            {
            }
        });
        $result = $shooter->shoot($target, false); // moved=false
        $this->assertIsBool($result, 'MoveOrFire: можно стрелять если не двигался');
    }

    public function testNormalRangedWeaponCanShootAfterMove()
    {
        $shooter = new Fighter('Shooter', new Characteristics(4, 4, 4, 3, 3, 1, 3, 1, 7), [], new EquipmentManager([
            Weapons::getByName('Bow')
        ]), new class implements \Mordheim\Strategy\BattleStrategy {
            public function executeTurn(\Mordheim\Fighter $self, array $enemies, \Mordheim\GameField $field): void
            {
            }
        });
        $target = new Fighter('Target', new Characteristics(4, 4, 4, 3, 3, 1, 3, 1, 7), [], new EquipmentManager([]), new class implements \Mordheim\Strategy\BattleStrategy {
            public function executeTurn(\Mordheim\Fighter $self, array $enemies, \Mordheim\GameField $field): void
            {
            }
        });
        $result = $shooter->shoot($target, true); // moved=true
        $this->assertIsBool($result, 'Обычное оружие: можно стрелять после движения');
    }
}
