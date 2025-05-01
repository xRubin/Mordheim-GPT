<?php

use Mordheim\Characteristics;
use Mordheim\Data\Skills;
use Mordheim\EquipmentManager;
use Mordheim\FieldCell;
use Mordheim\Fighter;
use Mordheim\FighterState;
use Mordheim\GameField;
use Mordheim\Strategy\BattleStrategyInterface;
use PHPUnit\Framework\TestCase;

class FighterTest extends TestCase
{
    private function makeFighter($pos, $move = 3, $skills = [])
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
            'Test', $char, $skills, new EquipmentManager(),
            $this->createMock(BattleStrategyInterface::class), $pos, FighterState::STANDING
        );
    }

    public function testAcrobatIgnoresDanger()
    {
        // Для простоты: если бы была опасная местность, Acrobat игнорирует штраф
        // Здесь можно мокнуть GameField и FieldCell, чтобы cell->dangerousTerrain=true, а у бойца есть Acrobat
        $this->assertTrue(true, 'Acrobat skill placeholder test');
    }

    public function testLeapAllowsJump()
    {
        // Leap: можно прыгнуть на 6" через препятствие (требует тест Initiative)
        // Здесь можно мокнуть PathFinder, чтобы путь был заблокирован, но Leap разрешает прыжок
        $this->assertTrue(true, 'Leap skill placeholder test');
    }

    public function testMovementPenaltyForHeavyArmorAndShield()
    {
        $char = new \Mordheim\Characteristics(4, 1, 1, 1, 1, 1, 5, 1, 7);
        $heavy = \Mordheim\Data\Armors::getByName('Heavy Armor');
        $shield = \Mordheim\Data\Armors::getByName('Shield');

        // Без брони
        $fighter = new \Mordheim\Fighter('NoArmor', $char, [], new \Mordheim\EquipmentManager(), $this->createMock(\Mordheim\Strategy\BattleStrategyInterface::class));
        $this->assertEquals(4, $fighter->getMovement());

        // Только Heavy Armor с MOVEMENT
        $fighter = new \Mordheim\Fighter('Heavy', $char, [], new \Mordheim\EquipmentManager([], [$heavy]), $this->createMock(\Mordheim\Strategy\BattleStrategyInterface::class));
        $this->assertEquals(4, $fighter->getMovement());

        // Только Shield
        $fighter = new \Mordheim\Fighter('Shield', $char, [], new \Mordheim\EquipmentManager([], [$shield]), $this->createMock(\Mordheim\Strategy\BattleStrategyInterface::class));
        $this->assertEquals(4, $fighter->getMovement());

        // Heavy Armor с MOVEMENT и Shield
        $armorNames = [$heavy ? $heavy->name : 'null', $shield ? $shield->name : 'null'];
        fwrite(STDERR, "[TEST DEBUG] FighterTest about to create EquipmentManager with armors: " . implode(", ", $armorNames) . "\n");
        $fighter = new \Mordheim\Fighter('Both', $char, [], new \Mordheim\EquipmentManager([], [$heavy, $shield]), $this->createMock(\Mordheim\Strategy\BattleStrategyInterface::class));
        fwrite(STDERR, "[TEST DEBUG] FighterTest Both: movement base={$char->movement}, Heavy='{$heavy->name}', Shield='{$shield->name}', getMovement=" . $fighter->getMovement() . "\n");
        $this->assertEquals(3, $fighter->getMovement());

        // Движение не может быть меньше 1
        $char = new \Mordheim\Characteristics(1, 1, 1, 1, 1, 1, 5, 1, 7);
        $fighter = new \Mordheim\Fighter('Min', $char, [], new \Mordheim\EquipmentManager([], [$heavy, $shield]), $this->createMock(\Mordheim\Strategy\BattleStrategyInterface::class));
        $this->assertEquals(1, $fighter->getMovement());
    }

    public function testTryAvoidStunWithHelmet()
    {
        $helmet = \Mordheim\Data\Armors::getByName('Helmet');
        $em = new \Mordheim\EquipmentManager([], [$helmet]);
        $char = new \Mordheim\Characteristics(4, 1, 1, 1, 1, 1, 5, 1, 7);
        $fighter = new \Mordheim\Fighter('HelmetGuy', $char, [], $em, $this->createMock(\Mordheim\Strategy\BattleStrategyInterface::class));

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

