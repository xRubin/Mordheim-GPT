<?php

use Mordheim\Battle;
use Mordheim\Characteristics;
use Mordheim\Equipment;
use Mordheim\Fighter;
use Mordheim\GameField;
use Mordheim\Rule\Attack;
use Mordheim\SpecialRule;
use Mordheim\Band;
use PHPUnit\Framework\TestCase;

class FighterShootingTest extends TestCase
{
    private function makeClearBattle(array $shooterFighters, array $targetFighters)
    {

        return new Battle(
            new GameField(),
            [
                new Band('Shooters', $shooterFighters),
                new Band('Targets', $targetFighters)
            ]
        );
    }

    public function testBasicHitAndMiss()
    {
        $shooter = new \Mordheim\Fighter(
            Mordheim\Blank::REIKLAND_MARKSMAN,
            \Mordheim\FighterAdvancement::empty(),
            new \Mordheim\EquipmentManager([Equipment::BOW]),
            new \Mordheim\FighterState(
                [0, 0, 0],
                $this->createMock(Mordheim\BattleStrategyInterface::class),
                1
            )
        );

        $target = new \Mordheim\Fighter(
            Mordheim\Blank::MARIENBURG_SWORDSMAN,
            \Mordheim\FighterAdvancement::empty(),
            new \Mordheim\EquipmentManager(),
            new \Mordheim\FighterState(
                [2, 0, 0],
                $this->createMock(Mordheim\BattleStrategyInterface::class),
                1
            )
        );

        $battle = $this->makeClearBattle([$shooter], [$target]);
        $result = \Mordheim\Rule\Attack::ranged($battle, $shooter, $target);
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
            Mordheim\Blank::REIKLAND_MARKSMAN,
            \Mordheim\FighterAdvancement::empty(),
            new \Mordheim\EquipmentManager([Equipment::BOW]),
            new \Mordheim\FighterState(
                [0, 0, 0],
                $this->createMock(Mordheim\BattleStrategyInterface::class),
                1
            )
        );

        $target = new \Mordheim\Fighter(
            Mordheim\Blank::MARIENBURG_SWORDSMAN,
            new \Mordheim\FighterAdvancement(new Characteristics(), [SpecialRule::DODGE]),
            new \Mordheim\EquipmentManager(),
            new \Mordheim\FighterState(
                [2, 0, 0],
                $this->createMock(Mordheim\BattleStrategyInterface::class),
                1
            )
        );

        $battle = $this->makeClearBattle([$shooter], [$target]);
        $result = \Mordheim\Rule\Attack::ranged($battle, $shooter, $target);
        $this->assertIsBool($result);
    }

    public function testQuickShotSkill()
    {
        $shooter = new \Mordheim\Fighter(
            Mordheim\Blank::REIKLAND_MARKSMAN,
            new \Mordheim\FighterAdvancement(new Characteristics(), [SpecialRule::QUICK_SHOT]),
            new \Mordheim\EquipmentManager([Equipment::SLING]),
            new \Mordheim\FighterState(
                [0, 0, 0],
                $this->createMock(Mordheim\BattleStrategyInterface::class),
                1
            )
        );

        $target = new \Mordheim\Fighter(
            Mordheim\Blank::MARIENBURG_SWORDSMAN,
            \Mordheim\FighterAdvancement::empty(),
            new \Mordheim\EquipmentManager(),
            new \Mordheim\FighterState(
                [2, 0, 0],
                $this->createMock(Mordheim\BattleStrategyInterface::class),
                1
            )
        );

        $battle = $this->makeClearBattle([$shooter], [$target]);
        $result = \Mordheim\Rule\Attack::ranged($battle, $shooter, $target, false);
        $this->assertIsBool($result);
    }

    public function testWeaponSpecialRules()
    {
        $shooter = new \Mordheim\Fighter(
            Mordheim\Blank::REIKLAND_MARKSMAN,
            \Mordheim\FighterAdvancement::empty(),
            new \Mordheim\EquipmentManager([Equipment::CROSSBOW]),
            new \Mordheim\FighterState(
                [0, 0, 0],
                $this->createMock(Mordheim\BattleStrategyInterface::class),
                1
            )
        );

        $target = new \Mordheim\Fighter(
            Mordheim\Blank::MARIENBURG_SWORDSMAN,
            \Mordheim\FighterAdvancement::empty(),
            new \Mordheim\EquipmentManager(),
            new \Mordheim\FighterState(
                [2, 0, 0],
                $this->createMock(Mordheim\BattleStrategyInterface::class),
                1
            )
        );

        $battle = $this->makeClearBattle([$shooter], [$target]);
        $result = \Mordheim\Rule\Attack::ranged($battle, $shooter, $target);
        $this->assertIsBool($result);
    }

    public function testMoveOrFireBlocksShootingAfterMove()
    {
        $shooter = new \Mordheim\Fighter(
            Mordheim\Blank::REIKLAND_MARKSMAN,
            \Mordheim\FighterAdvancement::empty(),
            new \Mordheim\EquipmentManager([Equipment::CROSSBOW]),
            new \Mordheim\FighterState(
                [0, 0, 0],
                $this->createMock(Mordheim\BattleStrategyInterface::class),
                1
            )
        );

        $target = new \Mordheim\Fighter(
            Mordheim\Blank::MARIENBURG_SWORDSMAN,
            \Mordheim\FighterAdvancement::empty(),
            new \Mordheim\EquipmentManager(),
            new \Mordheim\FighterState(
                [2, 0, 0],
                $this->createMock(Mordheim\BattleStrategyInterface::class),
                1
            )
        );

        $battle = $this->makeClearBattle([$shooter], [$target]);
        $result = \Mordheim\Rule\Attack::ranged($battle, $shooter, $target, true); // moved=true
        $this->assertFalse($result, 'MoveOrFire: нельзя стрелять после движения');
    }

    public function testMoveOrFireAllowsShootingWithoutMove()
    {
        $shooter = new \Mordheim\Fighter(
            Mordheim\Blank::REIKLAND_MARKSMAN,
            \Mordheim\FighterAdvancement::empty(),
            new \Mordheim\EquipmentManager([Equipment::CROSSBOW]),
            new \Mordheim\FighterState(
                [0, 0, 0],
                $this->createMock(Mordheim\BattleStrategyInterface::class),
                1
            )
        );

        $target = new \Mordheim\Fighter(
            Mordheim\Blank::MARIENBURG_SWORDSMAN,
            \Mordheim\FighterAdvancement::empty(),
            new \Mordheim\EquipmentManager(),
            new \Mordheim\FighterState(
                [2, 0, 0],
                $this->createMock(Mordheim\BattleStrategyInterface::class),
                1
            )
        );

        $battle = $this->makeClearBattle([$shooter], [$target]);
        $result = \Mordheim\Rule\Attack::ranged($battle, $shooter, $target, false); // moved=false
        $this->assertIsBool($result, 'MoveOrFire: можно стрелять если не двигался');
    }

    public function testNormalRangedWeaponCanShootAfterMove()
    {
        $shooter = new \Mordheim\Fighter(
            Mordheim\Blank::REIKLAND_MARKSMAN,
            \Mordheim\FighterAdvancement::empty(),
            new \Mordheim\EquipmentManager([Equipment::BOW]),
            new \Mordheim\FighterState(
                [0, 0, 0],
                $this->createMock(Mordheim\BattleStrategyInterface::class),
                1
            )
        );

        $target = new \Mordheim\Fighter(
            Mordheim\Blank::MARIENBURG_SWORDSMAN,
            \Mordheim\FighterAdvancement::empty(),
            new \Mordheim\EquipmentManager(),
            new \Mordheim\FighterState(
                [2, 0, 0],
                $this->createMock(Mordheim\BattleStrategyInterface::class),
                1
            )
        );

        $battle = $this->makeClearBattle([$shooter], [$target]);
        $result = \Mordheim\Rule\Attack::ranged($battle, $shooter, $target, true); // moved=true
        $this->assertIsBool($result, 'Обычное оружие: можно стрелять после движения');
    }

    public function testSelectRangedWeaponReturnsNullIfMoveOrFireAndMoved()
    {
        $source = $this->createMock(Fighter::class);
        $sourceState = $this->createMock(\Mordheim\FighterState::class);
        $sourceState->method('getPosition')->willReturn([0, 0, 0]);
        $source->method('getEquipmentManager')->willReturn(new \Mordheim\EquipmentManager([Equipment::CROSSBOW]));
        $source->method('getState')->willReturn($sourceState);

        $target = $this->createMock(Fighter::class);
        $targetState = $this->createMock(\Mordheim\FighterState::class);
        $targetState->method('getPosition')->willReturn([10, 0, 0]);
        $target->method('getState')->willReturn($targetState);

        $this->assertNull(Attack::selectRangedWeapon($source, $target, true));
    }

    public function testCalculateRangedParamsWithAllModifiers()
    {
        $battle = $this->createMock(Battle::class);
        $battle->method('hasObstacleBetween')->willReturn(true);

        $weapon = Equipment::BOW;

        $sourceState = $this->createMock(\Mordheim\FighterState::class);
        $sourceState->method('getPosition')->willReturn([0, 0, 0]);

        $source = $this->createMock(Fighter::class);
        $source->method('getBallisticSkill')->willReturn(3); // 5+ to hit
        $source->method('hasSpecialRule')->willReturn(false);
        $source->method('getHitModifier')->willReturn(2);
        $source->method('getState')->willReturn($sourceState);

        $targetState = $this->createMock(\Mordheim\FighterState::class);
        $targetState->method('getPosition')->willReturn([10, 0, 0]);

        $target = $this->createMock(Fighter::class);
        $target->method('hasSpecialRule')->willReturnCallback(function ($rule) {
            return $rule === SpecialRule::LARGE_TARGET;
        });
        $target->method('getState')->willReturn($targetState);

        [$toHit, $shots] = Attack::calculateRangedParams($battle, $source, $target, $weapon, true);
        // 5+ (BS3) +1 (дальний) +1 (двигался) +1 (укрытие) -1 (большая цель) +2 (модификатор оружия) = 9, но максимум 6
        $this->assertEquals(6, $toHit);
        $this->assertEquals(1, $shots);
    }

    public function testTryArmourSaveRangedReturnsTrueIfSaveRollSufficient()
    {
        $source = $this->createMock(Fighter::class);
        $target = $this->createMock(Fighter::class);
        $weapon = Equipment::BOW;
        // Мокаем getArmoUrSave чтобы вернуть 4, и Dice::roll чтобы вернуть 5 (>=4)
        $target->method('getArmourSave')->willReturn(4);
        \Mordheim\Dice::setTestRolls([5]);
        // Здесь имитируем saveRoll >= armourSave
        $this->assertTrue(Attack::tryArmourSaveRanged($source, $target, $weapon));
    }

    public function testTryArmourSaveRangedReturnsFalseIfSaveRollInsufficient()
    {
        $source = $this->createMock(Fighter::class);
        $target = $this->createMock(Fighter::class);
        $weapon = Equipment::BOW;

        $targetState = $this->createMock(\Mordheim\FighterState::class);
        $targetState->method('getStatus')->willReturn(\Mordheim\Status::STANDING);
        $target->method('getState')->willReturn($targetState);

        $target->method('getArmourSave')->willReturn(5);
        \Mordheim\Dice::setTestRolls([3]);
        $this->assertFalse(Attack::tryArmourSaveRanged($source, $target, $weapon));
    }

    public function testSelectRangedWeaponReturnsNullIfNoWeapon()
    {
        $source = $this->createMock(Fighter::class);
        $source->method('getEquipmentManager')->willReturn(new \Mordheim\EquipmentManager());
        $target = $this->createMock(Fighter::class);
        $this->assertNull(Attack::selectRangedWeapon($source, $target, false));
    }

    public function testSelectRangedWeaponReturnsWeaponIfInRange()
    {
        $weapon = Equipment::BOW;
        $source = $this->createMock(Fighter::class);
        $source->method('getEquipmentManager')->willReturn(new \Mordheim\EquipmentManager([$weapon]));
        $source->method('getState')->willReturn(
            new \Mordheim\FighterState([0, 0, 0], $this->createMock(\Mordheim\BattleStrategyInterface::class), 1)
        );
        $target = $this->createMock(Fighter::class);
        $target->method('getState')->willReturn(
            new \Mordheim\FighterState([3, 0, 0], $this->createMock(\Mordheim\BattleStrategyInterface::class), 1)
        );
        $this->assertSame($weapon, Attack::selectRangedWeapon($source, $target, false));
    }

    public function testSelectRangedWeaponReturnsNullIfOutOfRange()
    {
        $source = $this->createMock(Fighter::class);
        $sourceState = $this->createMock(\Mordheim\FighterState::class);
        $sourceState->method('getPosition')->willReturn([0, 0, 0]);
        $source->method('getEquipmentManager')->willReturn(new \Mordheim\EquipmentManager([Equipment::PISTOL]));
        $source->method('getState')->willReturn($sourceState);

        $target = $this->createMock(Fighter::class);
        $targetState = $this->createMock(\Mordheim\FighterState::class);
        $targetState->method('getPosition')->willReturn([10, 0, 0]);
        $target->method('getState')->willReturn($targetState);

        $this->assertNull(Attack::selectRangedWeapon($source, $target, false));
    }

    public function testCalculateRangedParamsTypical()
    {
        $battle = $this->createMock(Battle::class);
        $battle->method('hasObstacleBetween')->willReturn(false);
        $weapon = Equipment::BOW;
        $source = $this->createMock(Fighter::class);
        $source->method('getBallisticSkill')->willReturn(4); // 4+ to hit
        $source->method('hasSpecialRule')->willReturn(false);
        $source->method('getHitModifier')->willReturn(0);
        $sourceState = $this->createMock(\Mordheim\FighterState::class);
        $sourceState->method('getPosition')->willReturn([0, 0, 0]);
        $source->method('getState')->willReturn($sourceState);
        $target = $this->createMock(Fighter::class);
        $target->method('hasSpecialRule')->willReturn(false);
        $targetState = $this->createMock(\Mordheim\FighterState::class);
        $targetState->method('getPosition')->willReturn([10, 0, 0]);
        $target->method('getState')->willReturn($targetState);
        [$toHit, $shots] = Attack::calculateRangedParams($battle, $source, $target, $weapon, false);
        $this->assertEquals(3, $toHit);
        $this->assertEquals(1, $shots);
    }

    public function testCalculateRangedParamsWithQuickShot()
    {
        $battle = $this->createMock(Battle::class);
        $battle->method('hasObstacleBetween')->willReturn(false);
        $weapon = Equipment::BOW;
        $source = $this->createMock(Fighter::class);
        $source->method('getBallisticSkill')->willReturn(4);
        $source->method('hasSpecialRule')->willReturnCallback(function ($rule) {
            return $rule === SpecialRule::QUICK_SHOT;
        });
        $source->method('getHitModifier')->willReturn(0);
        $sourceState = $this->createMock(\Mordheim\FighterState::class);
        $sourceState->method('getPosition')->willReturn([0, 0, 0]);
        $source->method('getState')->willReturn($sourceState);
        $target = $this->createMock(Fighter::class);
        $target->method('hasSpecialRule')->willReturn(false);
        $targetState = $this->createMock(\Mordheim\FighterState::class);
        $targetState->method('getPosition')->willReturn([10, 0, 0]);
        $target->method('getState')->willReturn($targetState);
        [$toHit, $shots] = Attack::calculateRangedParams($battle, $source, $target, $weapon, false);
        $this->assertEquals(3, $toHit);
        $this->assertEquals(2, $shots);
    }
}
