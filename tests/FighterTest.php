<?php

use Mordheim\Characteristics;
use Mordheim\EquipmentManager;
use Mordheim\FieldCell;
use Mordheim\Fighter;
use Mordheim\FighterState;
use Mordheim\GameField;
use Mordheim\Strategy\BattleStrategy;
use PHPUnit\Framework\TestCase;

class FighterTest extends TestCase
{
    private function makeFighter($pos, $move = 3)
    {
        $char = new Characteristics(
            $move, // movement
            1, // weaponSkill
            1, // ballisticSkill
            1, // strength
            1, // toughness
            1, // wounds
            5, // initiative
            1, // attacks
            7  // leadership
        );
        return new Fighter(
            'Test', $char, [], new EquipmentManager(),
            $this->createMock(BattleStrategy::class), $pos, FighterState::STANDING
        );
    }

    public function testMoveAdvancedTowardsReachGoal()
    {
        $field = new GameField();
        $fighter = $this->makeFighter([0, 0, 0], 3);
        $target = [3, 0, 0];
        $log = $fighter->moveAdvancedTowards($target, $field, [], false);
        $this->assertEquals($target, $fighter->position);
        $this->assertEmpty(array_filter($log, fn($l) => str_contains($l, 'недостижима')));
    }

    public function testMoveAdvancedTowardsPartialMove()
    {
        $field = new GameField();
        $fighter = $this->makeFighter([0, 0, 0], 2);
        $target = [4, 0, 0];
        $log = $fighter->moveAdvancedTowards($target, $field, [], true);
        $this->assertEquals([2, 0, 0], $fighter->position);
        $found = false;
        foreach ($log as $l) {
            try {
                $this->assertStringContainsString('недостижима', $l);
                $found = true;
                break;
            } catch (\PHPUnit\Framework\ExpectationFailedException $e) {
            }
        }
        $this->assertTrue($found, 'Ожидалось сообщение о недостижимости');
    }

    public function testMoveAdvancedTowardsNoPath()
    {
        $field = new GameField();
        $cell = new FieldCell();
        $cell->obstacle = true;
        $field->setCell(1, 0, 0, $cell);
        $fighter = $this->makeFighter([0, 0, 0], 3);
        $target = [2, 0, 0];
        $log = $fighter->moveAdvancedTowards($target, $field, [], true);
        // Ожидаем, что дойдет по диагонали
        $this->assertEquals([2, 0, 0], $fighter->position);
        foreach ($log as $l) {
            $this->assertStringNotContainsString('Нет даже частичного пути', $l);
        }
    }

    public function testMoveAdvancedTowardsDiagonal()
    {
        $field = new GameField();
        $fighter = $this->makeFighter([0, 0, 0], 2); // move > 1.4
        $target = [1, 1, 0];
        $log = $fighter->moveAdvancedTowards($target, $field, [], false);
        $this->assertEquals($target, $fighter->position);
        $this->assertEmpty(array_filter($log, fn($l) => str_contains($l, 'недостижима')));
    }

    public function testMovementPenaltyForHeavyArmorAndShield()
    {
        $char = new \Mordheim\Characteristics(4, 1, 1, 1, 1, 1, 5, 1, 7);
        $heavy = \Mordheim\Data\Armors::getByName('Heavy Armor');
        $shield = \Mordheim\Data\Armors::getByName('Shield');

        // Без брони
        $fighter = new \Mordheim\Fighter('NoArmor', $char, [], new \Mordheim\EquipmentManager(), $this->createMock(\Mordheim\Strategy\BattleStrategy::class));
        $this->assertEquals(4, $fighter->getMovement());

        // Только Heavy Armor с MOVEMENT
        $fighter = new \Mordheim\Fighter('Heavy', $char, [], new \Mordheim\EquipmentManager([], [$heavy]), $this->createMock(\Mordheim\Strategy\BattleStrategy::class));
        $this->assertEquals(4, $fighter->getMovement());

        // Только Shield
        $fighter = new \Mordheim\Fighter('Shield', $char, [], new \Mordheim\EquipmentManager([], [$shield]), $this->createMock(\Mordheim\Strategy\BattleStrategy::class));
        $this->assertEquals(4, $fighter->getMovement());

        // Heavy Armor с MOVEMENT и Shield
        $armorNames = [$heavy ? $heavy->name : 'null', $shield ? $shield->name : 'null'];
        fwrite(STDERR, "[TEST DEBUG] FighterTest about to create EquipmentManager with armors: " . implode(", ", $armorNames) . "\n");
        $fighter = new \Mordheim\Fighter('Both', $char, [], new \Mordheim\EquipmentManager([], [$heavy, $shield]), $this->createMock(\Mordheim\Strategy\BattleStrategy::class));
        fwrite(STDERR, "[TEST DEBUG] FighterTest Both: movement base={$char->movement}, Heavy='{$heavy->name}', Shield='{$shield->name}', getMovement=" . $fighter->getMovement() . "\n");
        $this->assertEquals(3, $fighter->getMovement());

        // Движение не может быть меньше 1
        $char = new \Mordheim\Characteristics(1, 1, 1, 1, 1, 1, 5, 1, 7);
        $fighter = new \Mordheim\Fighter('Min', $char, [], new \Mordheim\EquipmentManager([], [$heavy, $shield]), $this->createMock(\Mordheim\Strategy\BattleStrategy::class));
        $this->assertEquals(1, $fighter->getMovement());
    }

    public function testTryAvoidStunWithHelmet()
    {
        $helmet = \Mordheim\Data\Armors::getByName('Helmet');
        $em = new \Mordheim\EquipmentManager([], [$helmet]);
        $char = new \Mordheim\Characteristics(4, 1, 1, 1, 1, 1, 5, 1, 7);
        $fighter = new \Mordheim\Fighter('HelmetGuy', $char, [], $em, $this->createMock(\Mordheim\Strategy\BattleStrategy::class));

        // Используем Dice::setTestRolls для мокирования бросков
        $mockedRolls = [3, 4, 5, 6]; // 3 - fail, 4/5/6 - success
        \Mordheim\Dice::setTestRolls($mockedRolls);
        $results = [];
        foreach ($mockedRolls as $ignored) {
            $results[] = $fighter->tryAvoidStun();
        }
        // Проверяем, что только 3 (fail), остальные - success
        $this->assertFalse($results[0], 'Roll 3 should fail helmet save');
        $this->assertTrue($results[1], 'Roll 4 should pass helmet save');
        $this->assertTrue($results[2], 'Roll 5 should pass helmet save');
        $this->assertTrue($results[3], 'Roll 6 should pass helmet save');
        // Очистить тестовые броски
        \Mordheim\Dice::setTestRolls([]);
    }
}

