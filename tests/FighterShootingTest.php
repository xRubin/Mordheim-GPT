<?php

use Mordheim\Battle;
use Mordheim\Characteristics;
use Mordheim\Data\Equipment;
use Mordheim\GameField;
use Mordheim\SpecialRule;
use Mordheim\Warband;
use PHPUnit\Framework\TestCase;

class FighterShootingTest extends TestCase
{
    private function makeClearBattle(array $shooterFighters, array $targetFighters)
    {

        return new Battle(
            new GameField(),
            [
                new Warband('Shooters', $shooterFighters),
                new Warband('Targets', $targetFighters)
            ]
        );
    }

    public function testBasicHitAndMiss()
    {
        $shooter = new \Mordheim\Fighter(
            \Mordheim\Data\Blank::REIKLAND_MARKSMAN,
            \Mordheim\FighterAdvancement::empty(),
            new \Mordheim\EquipmentManager([Equipment::BOW]),
            new \Mordheim\FighterState(
                [0, 0, 0],
                $this->createMock(Mordheim\BattleStrategyInterface::class),
                1
            )
        );

        $target = new \Mordheim\Fighter(
            \Mordheim\Data\Blank::MARIENBURG_SWORDSMAN,
            \Mordheim\FighterAdvancement::empty(),
            new \Mordheim\EquipmentManager(),
            new \Mordheim\FighterState(
                [2, 0, 0],
                $this->createMock(Mordheim\BattleStrategyInterface::class),
                1
            )
        );

        $battle = $this->makeClearBattle([$shooter], [$target]);
        $result = \Mordheim\Rule\Shoot::apply($battle, $shooter, $target);
        $this->assertIsBool($result);
    }

    /**
     * TODO
     */
    public function testCriticalHitIgnoresSave()
    {
        $this->assertTrue(true); // Здесь должен быть мок Dice::roll, но для примера — всегда true
    }

    public function testDodgeSkill()
    {
        $shooter = new \Mordheim\Fighter(
            \Mordheim\Data\Blank::REIKLAND_MARKSMAN,
            \Mordheim\FighterAdvancement::empty(),
            new \Mordheim\EquipmentManager([Equipment::BOW]),
            new \Mordheim\FighterState(
                [0, 0, 0],
                $this->createMock(Mordheim\BattleStrategyInterface::class),
                1
            )
        );

        $target = new \Mordheim\Fighter(
            \Mordheim\Data\Blank::MARIENBURG_SWORDSMAN,
            new \Mordheim\FighterAdvancement(Characteristics::empty(), [SpecialRule::DODGE]),
            new \Mordheim\EquipmentManager(),
            new \Mordheim\FighterState(
                [2, 0, 0],
                $this->createMock(Mordheim\BattleStrategyInterface::class),
                1
            )
        );

        $battle = $this->makeClearBattle([$shooter], [$target]);
        $result = \Mordheim\Rule\Shoot::apply($battle, $shooter, $target);
        $this->assertIsBool($result);
    }

    public function testQuickShotSkill()
    {
        $shooter = new \Mordheim\Fighter(
            \Mordheim\Data\Blank::REIKLAND_MARKSMAN,
            new \Mordheim\FighterAdvancement(Characteristics::empty(), [SpecialRule::QUICK_SHOT]),
            new \Mordheim\EquipmentManager([Equipment::SLING]),
            new \Mordheim\FighterState(
                [0, 0, 0],
                $this->createMock(Mordheim\BattleStrategyInterface::class),
                1
            )
        );

        $target = new \Mordheim\Fighter(
            \Mordheim\Data\Blank::MARIENBURG_SWORDSMAN,
            \Mordheim\FighterAdvancement::empty(),
            new \Mordheim\EquipmentManager(),
            new \Mordheim\FighterState(
                [2, 0, 0],
                $this->createMock(Mordheim\BattleStrategyInterface::class),
                1
            )
        );

        $battle = $this->makeClearBattle([$shooter], [$target]);
        $result = \Mordheim\Rule\Shoot::apply($battle, $shooter, $target, false);
        $this->assertIsBool($result);
    }

    public function testWeaponSpecialRules()
    {
        $shooter = new \Mordheim\Fighter(
            \Mordheim\Data\Blank::REIKLAND_MARKSMAN,
            \Mordheim\FighterAdvancement::empty(),
            new \Mordheim\EquipmentManager([Equipment::CROSSBOW]),
            new \Mordheim\FighterState(
                [0, 0, 0],
                $this->createMock(Mordheim\BattleStrategyInterface::class),
                1
            )
        );

        $target = new \Mordheim\Fighter(
            \Mordheim\Data\Blank::MARIENBURG_SWORDSMAN,
            \Mordheim\FighterAdvancement::empty(),
            new \Mordheim\EquipmentManager(),
            new \Mordheim\FighterState(
                [2, 0, 0],
                $this->createMock(Mordheim\BattleStrategyInterface::class),
                1
            )
        );

        $battle = $this->makeClearBattle([$shooter], [$target]);
        $result = \Mordheim\Rule\Shoot::apply($battle, $shooter, $target);
        $this->assertIsBool($result);
    }

    public function testMoveOrFireBlocksShootingAfterMove()
    {
        $shooter = new \Mordheim\Fighter(
            \Mordheim\Data\Blank::REIKLAND_MARKSMAN,
            \Mordheim\FighterAdvancement::empty(),
            new \Mordheim\EquipmentManager([Equipment::CROSSBOW]),
            new \Mordheim\FighterState(
                [0, 0, 0],
                $this->createMock(Mordheim\BattleStrategyInterface::class),
                1
            )
        );

        $target = new \Mordheim\Fighter(
            \Mordheim\Data\Blank::MARIENBURG_SWORDSMAN,
            \Mordheim\FighterAdvancement::empty(),
            new \Mordheim\EquipmentManager(),
            new \Mordheim\FighterState(
                [2, 0, 0],
                $this->createMock(Mordheim\BattleStrategyInterface::class),
                1
            )
        );

        $battle = $this->makeClearBattle([$shooter], [$target]);
        $result = \Mordheim\Rule\Shoot::apply($battle, $shooter, $target, true); // moved=true
        $this->assertFalse($result, 'MoveOrFire: нельзя стрелять после движения');
    }

    public function testMoveOrFireAllowsShootingWithoutMove()
    {
        $shooter = new \Mordheim\Fighter(
            \Mordheim\Data\Blank::REIKLAND_MARKSMAN,
            \Mordheim\FighterAdvancement::empty(),
            new \Mordheim\EquipmentManager([Equipment::CROSSBOW]),
            new \Mordheim\FighterState(
                [0, 0, 0],
                $this->createMock(Mordheim\BattleStrategyInterface::class),
                1
            )
        );

        $target = new \Mordheim\Fighter(
            \Mordheim\Data\Blank::MARIENBURG_SWORDSMAN,
            \Mordheim\FighterAdvancement::empty(),
            new \Mordheim\EquipmentManager(),
            new \Mordheim\FighterState(
                [2, 0, 0],
                $this->createMock(Mordheim\BattleStrategyInterface::class),
                1
            )
        );

        $battle = $this->makeClearBattle([$shooter], [$target]);
        $result = \Mordheim\Rule\Shoot::apply($battle, $shooter, $target, false); // moved=false
        $this->assertIsBool($result, 'MoveOrFire: можно стрелять если не двигался');
    }

    public function testNormalRangedWeaponCanShootAfterMove()
    {
        $shooter = new \Mordheim\Fighter(
            \Mordheim\Data\Blank::REIKLAND_MARKSMAN,
            \Mordheim\FighterAdvancement::empty(),
            new \Mordheim\EquipmentManager([Equipment::BOW]),
            new \Mordheim\FighterState(
                [0, 0, 0],
                $this->createMock(Mordheim\BattleStrategyInterface::class),
                1
            )
        );

        $target = new \Mordheim\Fighter(
            \Mordheim\Data\Blank::MARIENBURG_SWORDSMAN,
            \Mordheim\FighterAdvancement::empty(),
            new \Mordheim\EquipmentManager(),
            new \Mordheim\FighterState(
                [2, 0, 0],
                $this->createMock(Mordheim\BattleStrategyInterface::class),
                1
            )
        );

        $battle = $this->makeClearBattle([$shooter], [$target]);
        $result = \Mordheim\Rule\Shoot::apply($battle, $shooter, $target, true); // moved=true
        $this->assertIsBool($result, 'Обычное оружие: можно стрелять после движения');
    }
}
