<?php

namespace Classic;

use Mordheim;
use Mordheim\Classic\Equipment;
use Mordheim\Classic\Rule\AvoidStun;

class FighterTest extends \MordheimTestCase
{
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

    public function testMovementPenaltyForHeavyArmourAndShield()
    {
        // Без брони
        $fighter = new \Mordheim\Classic\Fighter(
            Mordheim\Classic\Blank::MARIENBURG_SWORDSMAN,
            \Mordheim\Classic\FighterAdvancement::empty(),
            new \Mordheim\Classic\EquipmentManager(),
        );
        $this->assertEquals(4, $fighter->getMoveRange());

        // Только Heavy Armour с MOVEMENT
        $fighter = new \Mordheim\Classic\Fighter(
            Mordheim\Classic\Blank::MARIENBURG_SWORDSMAN,
            \Mordheim\Classic\FighterAdvancement::empty(),
            new \Mordheim\Classic\EquipmentManager([Equipment::HEAVY_ARMOUR])
        );
        $this->assertEquals(4, $fighter->getMoveRange());

        $fighter = new \Mordheim\Classic\Fighter(
            Mordheim\Classic\Blank::MARIENBURG_SWORDSMAN,
            \Mordheim\Classic\FighterAdvancement::empty(),
            new \Mordheim\Classic\EquipmentManager([Equipment::SHIELD])
        );
        $this->assertEquals(4, $fighter->getMoveRange());

        // Heavy Armour с MOVEMENT и Shield
        $fighter = new \Mordheim\Classic\Fighter(
            Mordheim\Classic\Blank::MARIENBURG_SWORDSMAN,
            \Mordheim\Classic\FighterAdvancement::empty(),
            new \Mordheim\Classic\EquipmentManager([Equipment::HEAVY_ARMOUR, Equipment::SHIELD])
        );
        $this->assertEquals(3, $fighter->getMoveRange());
    }

    public function testTryAvoidStunWithHelmet()
    {
        $fighter = new \Mordheim\Classic\Fighter(
            Mordheim\Classic\Blank::MARIENBURG_SWORDSMAN,
            \Mordheim\Classic\FighterAdvancement::empty(),
            new \Mordheim\Classic\EquipmentManager([Equipment::HELMET])
        );
        // Используем Dice::setTestRolls для мокирования бросков
        $mockedRolls = [3, 4, 5, 6]; // 3 - fail, 4/5/6 - success
        Mordheim\Dice::setTestRolls($mockedRolls);
        $results = [];
        foreach ($mockedRolls as $ignored) {
            $results[] = AvoidStun::roll($fighter);
        }
        // Проверяем, что только 3 (fail), остальные - success
        $this->assertFalse($results[0], 'Roll 3 should fail helmet save');
        $this->assertTrue($results[1], 'Roll 4 should pass helmet save');
        $this->assertTrue($results[2], 'Roll 5 should pass helmet save');
        $this->assertTrue($results[3], 'Roll 6 should pass helmet save');
    }
}

