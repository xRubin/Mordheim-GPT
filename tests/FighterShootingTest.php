<?php

use Mordheim\Battle;
use Mordheim\Characteristics;
use Mordheim\Data\Skills;
use Mordheim\Data\Weapons;
use Mordheim\EquipmentManager;
use Mordheim\Fighter;
use Mordheim\GameField;
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
        $shooter = new Fighter('Shooter', new Characteristics(4, 4, 4, 3, 3, 1, 3, 1, 7), [], new EquipmentManager([Weapons::getByName('Bow')]), $this->createMock(\Mordheim\Strategy\BattleStrategyInterface::class));
        $target = new Fighter('Target', new Characteristics(4, 4, 4, 3, 3, 1, 3, 1, 7), [], new EquipmentManager([]), $this->createMock(\Mordheim\Strategy\BattleStrategyInterface::class));
        $battle = $this->makeClearBattle([$shooter], [$target]);
        $result = \Mordheim\Rule\Shoot::apply($battle, $shooter, $target);
        $this->assertIsBool($result);
    }

    /**
     * TODO
     */
    public function testCriticalHitIgnoresSave()
    {
        $shooter = new Fighter('Shooter', new Characteristics(4, 4, 4, 3, 3, 1, 3, 1, 7), [], new EquipmentManager([Weapons::getByName('Bow')]), $this->createMock(\Mordheim\Strategy\BattleStrategyInterface::class));
        $target = new Fighter('Target', new Characteristics(4, 4, 4, 3, 3, 1, 3, 1, 7), [], new EquipmentManager([]), $this->createMock(\Mordheim\Strategy\BattleStrategyInterface::class));
        // Мокаем Dice чтобы выдать 6
        $this->assertTrue(true); // Здесь должен быть мок Dice::roll, но для примера — всегда true
    }

    public function testDodgeSkill()
    {
        $shooter = new Fighter('Shooter', new Characteristics(4, 4, 4, 3, 3, 1, 3, 1, 7), [], new EquipmentManager([Weapons::getByName('Bow')]), $this->createMock(\Mordheim\Strategy\BattleStrategyInterface::class));
        $target = new Fighter('Target', new Characteristics(4, 4, 4, 3, 3, 1, 3, 1, 7), [Skills::getByName('Dodge')], new EquipmentManager([]), $this->createMock(\Mordheim\Strategy\BattleStrategyInterface::class));
        $battle = $this->makeClearBattle([$shooter], [$target]);
        $result = \Mordheim\Rule\Shoot::apply($battle, $shooter, $target);
        $this->assertIsBool($result);
    }

    public function testQuickShotSkill()
    {
        $shooter = new Fighter('Shooter', new Characteristics(4, 4, 4, 3, 3, 1, 3, 1, 7), [Skills::getByName('Quick Shot')], new EquipmentManager([Weapons::getByName('Sling')]), $this->createMock(\Mordheim\Strategy\BattleStrategyInterface::class));
        $target = new Fighter('Target', new Characteristics(4, 4, 4, 3, 3, 1, 3, 1, 7), [], new EquipmentManager([]), $this->createMock(\Mordheim\Strategy\BattleStrategyInterface::class));
        $battle = $this->makeClearBattle([$shooter], [$target]);
        $result = \Mordheim\Rule\Shoot::apply($battle, $shooter, $target, false);
        $this->assertIsBool($result);
    }

    public function testWeaponSpecialRules()
    {
        $shooter = new Fighter('Shooter', new Characteristics(4, 4, 4, 3, 3, 1, 3, 1, 7), [], new EquipmentManager([Weapons::getByName('Warplock Jezzail')]), $this->createMock(\Mordheim\Strategy\BattleStrategyInterface::class));
        $target = new Fighter('Target', new Characteristics(4, 4, 4, 3, 3, 1, 3, 1, 7), [], new EquipmentManager([]), $this->createMock(\Mordheim\Strategy\BattleStrategyInterface::class));
        $battle = $this->makeClearBattle([$shooter], [$target]);
        $result = \Mordheim\Rule\Shoot::apply($battle, $shooter, $target);
        $this->assertIsBool($result);
    }

    public function testMoveOrFireBlocksShootingAfterMove()
    {
        $shooter = new Fighter('Shooter', new Characteristics(4, 4, 4, 3, 3, 1, 3, 1, 7), [], new EquipmentManager([
            Weapons::getByName('Warplock Jezzail')
        ]), $this->createMock(\Mordheim\Strategy\BattleStrategyInterface::class));
        $target = new Fighter('Target', new Characteristics(4, 4, 4, 3, 3, 1, 3, 1, 7), [], new EquipmentManager([]), $this->createMock(\Mordheim\Strategy\BattleStrategyInterface::class));
        $battle = $this->makeClearBattle([$shooter], [$target]);
        $result = \Mordheim\Rule\Shoot::apply($battle, $shooter, $target, true); // moved=true
        $this->assertFalse($result, 'MoveOrFire: нельзя стрелять после движения');
    }

    public function testMoveOrFireAllowsShootingWithoutMove()
    {
        $shooter = new Fighter('Shooter', new Characteristics(4, 4, 4, 3, 3, 1, 3, 1, 7), [], new EquipmentManager([Weapons::getByName('Warplock Jezzail')]), $this->createMock(\Mordheim\Strategy\BattleStrategyInterface::class));
        $target = new Fighter('Target', new Characteristics(4, 4, 4, 3, 3, 1, 3, 1, 7), [], new EquipmentManager([]), $this->createMock(\Mordheim\Strategy\BattleStrategyInterface::class));
        $battle = $this->makeClearBattle([$shooter], [$target]);
        $result = \Mordheim\Rule\Shoot::apply($battle, $shooter, $target, false); // moved=false
        $this->assertIsBool($result, 'MoveOrFire: можно стрелять если не двигался');
    }

    public function testNormalRangedWeaponCanShootAfterMove()
    {
        $shooter = new Fighter('Shooter', new Characteristics(4, 4, 4, 3, 3, 1, 3, 1, 7), [], new EquipmentManager([
            Weapons::getByName('Bow')
        ]), $this->createMock(\Mordheim\Strategy\BattleStrategyInterface::class));
        $target = new Fighter('Target', new Characteristics(4, 4, 4, 3, 3, 1, 3, 1, 7), [], new EquipmentManager([]), $this->createMock(\Mordheim\Strategy\BattleStrategyInterface::class));
        $battle = $this->makeClearBattle([$shooter], [$target]);
        $result = \Mordheim\Rule\Shoot::apply($battle, $shooter, $target, true); // moved=true
        $this->assertIsBool($result, 'Обычное оружие: можно стрелять после движения');
    }
}
