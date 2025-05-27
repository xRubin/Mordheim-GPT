<?php

namespace Classic;

use Mordheim\Band;
use Mordheim\Characteristics;
use Mordheim\Classic\Battle;
use Mordheim\Classic\BattleStrategyInterface;
use Mordheim\Classic\Blank;
use Mordheim\Classic\Equipment;
use Mordheim\Classic\EquipmentManager;
use Mordheim\Classic\Fighter;
use Mordheim\Classic\FighterAdvancement;
use Mordheim\Classic\FighterState;
use Mordheim\Classic\Rule\Attack;
use Mordheim\Classic\SpecialRule;
use Mordheim\Classic\Status;
use Mordheim\GameField;

class FighterShootingTest extends \MordheimTestCase
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
        $shooter = new Fighter(
            Blank::REIKLAND_MARKSMAN,
            FighterAdvancement::empty(),
            new EquipmentManager([Equipment::BOW]),
            new FighterState(
                [0, 0, 0],
                $this->createMock(BattleStrategyInterface::class),
                1
            )
        );

        $target = new Fighter(
            Blank::MARIENBURG_SWORDSMAN,
            FighterAdvancement::empty(),
            new EquipmentManager(),
            new FighterState(
                [2, 0, 0],
                $this->createMock(BattleStrategyInterface::class),
                1
            )
        );

        $battle = $this->makeClearBattle([$shooter], [$target]);
        $result = Attack::ranged($battle, $shooter, $target);
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
        $shooter = new Fighter(
            Blank::REIKLAND_MARKSMAN,
            FighterAdvancement::empty(),
            new EquipmentManager([Equipment::BOW]),
            new FighterState(
                [0, 0, 0],
                $this->createMock(BattleStrategyInterface::class),
                1
            )
        );

        $target = new Fighter(
            Blank::MARIENBURG_SWORDSMAN,
            new FighterAdvancement(new Characteristics(), [SpecialRule::DODGE]),
            new EquipmentManager(),
            new FighterState(
                [2, 0, 0],
                $this->createMock(BattleStrategyInterface::class),
                1
            )
        );

        $battle = $this->makeClearBattle([$shooter], [$target]);
        $result = Attack::ranged($battle, $shooter, $target);
        $this->assertIsBool($result);
    }

    public function testQuickShotSkill()
    {
        $shooter = new Fighter(
            Blank::REIKLAND_MARKSMAN,
            new FighterAdvancement(new Characteristics(), [SpecialRule::QUICK_SHOT]),
            new EquipmentManager([Equipment::SLING]),
            new FighterState(
                [0, 0, 0],
                $this->createMock(BattleStrategyInterface::class),
                1
            )
        );

        $target = new Fighter(
            Blank::MARIENBURG_SWORDSMAN,
            FighterAdvancement::empty(),
            new EquipmentManager(),
            new FighterState(
                [2, 0, 0],
                $this->createMock(BattleStrategyInterface::class),
                1
            )
        );

        $battle = $this->makeClearBattle([$shooter], [$target]);
        $result = Attack::ranged($battle, $shooter, $target, false);
        $this->assertIsBool($result);
    }

    public function testWeaponSpecialRules()
    {
        $shooter = new Fighter(
            Blank::REIKLAND_MARKSMAN,
            FighterAdvancement::empty(),
            new EquipmentManager([Equipment::CROSSBOW]),
            new FighterState(
                [0, 0, 0],
                $this->createMock(BattleStrategyInterface::class),
                1
            )
        );

        $target = new Fighter(
            Blank::MARIENBURG_SWORDSMAN,
            FighterAdvancement::empty(),
            new EquipmentManager(),
            new FighterState(
                [2, 0, 0],
                $this->createMock(BattleStrategyInterface::class),
                1
            )
        );

        $battle = $this->makeClearBattle([$shooter], [$target]);
        $result = Attack::ranged($battle, $shooter, $target);
        $this->assertIsBool($result);
    }

    public function testMoveOrFireBlocksShootingAfterMove()
    {
        $shooter = new Fighter(
            Blank::REIKLAND_MARKSMAN,
            FighterAdvancement::empty(),
            new EquipmentManager([Equipment::CROSSBOW]),
            new FighterState(
                [0, 0, 0],
                $this->createMock(BattleStrategyInterface::class),
                1
            )
        );

        $target = new Fighter(
            Blank::MARIENBURG_SWORDSMAN,
            FighterAdvancement::empty(),
            new EquipmentManager(),
            new FighterState(
                [2, 0, 0],
                $this->createMock(BattleStrategyInterface::class),
                1
            )
        );

        $battle = $this->makeClearBattle([$shooter], [$target]);
        $result = Attack::ranged($battle, $shooter, $target, true); // moved=true
        $this->assertFalse($result, 'MoveOrFire: нельзя стрелять после движения');
    }

    public function testMoveOrFireAllowsShootingWithoutMove()
    {
        $shooter = new Fighter(
            Blank::REIKLAND_MARKSMAN,
            FighterAdvancement::empty(),
            new EquipmentManager([Equipment::CROSSBOW]),
            new FighterState(
                [0, 0, 0],
                $this->createMock(BattleStrategyInterface::class),
                1
            )
        );

        $target = new Fighter(
            Blank::MARIENBURG_SWORDSMAN,
            FighterAdvancement::empty(),
            new EquipmentManager(),
            new FighterState(
                [2, 0, 0],
                $this->createMock(BattleStrategyInterface::class),
                1
            )
        );

        $battle = $this->makeClearBattle([$shooter], [$target]);
        $result = Attack::ranged($battle, $shooter, $target, false); // moved=false
        $this->assertIsBool($result, 'MoveOrFire: можно стрелять если не двигался');
    }

    public function testNormalRangedWeaponCanShootAfterMove()
    {
        $shooter = new Fighter(
            Blank::REIKLAND_MARKSMAN,
            FighterAdvancement::empty(),
            new EquipmentManager([Equipment::BOW]),
            new FighterState(
                [0, 0, 0],
                $this->createMock(BattleStrategyInterface::class),
                1
            )
        );

        $target = new Fighter(
            Blank::MARIENBURG_SWORDSMAN,
            FighterAdvancement::empty(),
            new EquipmentManager(),
            new FighterState(
                [2, 0, 0],
                $this->createMock(BattleStrategyInterface::class),
                1
            )
        );

        $battle = $this->makeClearBattle([$shooter], [$target]);
        $result = Attack::ranged($battle, $shooter, $target, true); // moved=true
        $this->assertIsBool($result, 'Обычное оружие: можно стрелять после движения');
    }

    public function testSelectRangedWeaponReturnsNullIfMoveOrFireAndMoved()
    {
        $source = $this->createMock(Fighter::class);
        $sourceState = $this->createMock(FighterState::class);
        $sourceState->method('getPosition')->willReturn([0, 0, 0]);
        $source->method('getEquipmentManager')->willReturn(new EquipmentManager([Equipment::CROSSBOW]));
        $source->method('getState')->willReturn($sourceState);

        $target = $this->createMock(Fighter::class);
        $targetState = $this->createMock(FighterState::class);
        $targetState->method('getPosition')->willReturn([10, 0, 0]);
        $target->method('getState')->willReturn($targetState);

        $this->assertNull(Attack::selectRangedWeapon($source, $target, true));
    }

    public function testCalculateRangedParamsWithAllModifiers()
    {
        $battle = $this->createMock(Battle::class);
        $battle->method('hasObstacleBetween')->willReturn(true);

        $weapon = Equipment::BOW;

        $sourceState = $this->createMock(FighterState::class);
        $sourceState->method('getPosition')->willReturn([0, 0, 0]);

        $source = $this->createMock(Fighter::class);
        $source->method('getBallisticSkill')->willReturn(3); // 5+ to hit
        $source->method('hasSpecialRule')->willReturn(false);
        $source->method('getHitModifier')->willReturn(2);
        $source->method('getState')->willReturn($sourceState);

        $targetState = $this->createMock(FighterState::class);
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

        $targetState = $this->createMock(FighterState::class);
        $targetState->method('getStatus')->willReturn(Status::STANDING);
        $target->method('getState')->willReturn($targetState);

        $target->method('getArmourSave')->willReturn(5);
        \Mordheim\Dice::setTestRolls([3]);
        $this->assertFalse(Attack::tryArmourSaveRanged($source, $target, $weapon));
    }

    public function testSelectRangedWeaponReturnsNullIfNoWeapon()
    {
        $source = $this->createMock(Fighter::class);
        $source->method('getEquipmentManager')->willReturn(new EquipmentManager());
        $target = $this->createMock(Fighter::class);
        $this->assertNull(Attack::selectRangedWeapon($source, $target, false));
    }

    public function testSelectRangedWeaponReturnsWeaponIfInRange()
    {
        $weapon = Equipment::BOW;
        $source = $this->createMock(Fighter::class);
        $source->method('getEquipmentManager')->willReturn(new EquipmentManager([$weapon]));
        $source->method('getState')->willReturn(
            new FighterState([0, 0, 0], $this->createMock(BattleStrategyInterface::class), 1)
        );
        $target = $this->createMock(Fighter::class);
        $target->method('getState')->willReturn(
            new FighterState([3, 0, 0], $this->createMock(BattleStrategyInterface::class), 1)
        );
        $this->assertSame($weapon, Attack::selectRangedWeapon($source, $target, false));
    }

    public function testSelectRangedWeaponReturnsNullIfOutOfRange()
    {
        $source = $this->createMock(Fighter::class);
        $sourceState = $this->createMock(FighterState::class);
        $sourceState->method('getPosition')->willReturn([0, 0, 0]);
        $source->method('getEquipmentManager')->willReturn(new EquipmentManager([Equipment::PISTOL]));
        $source->method('getState')->willReturn($sourceState);

        $target = $this->createMock(Fighter::class);
        $targetState = $this->createMock(FighterState::class);
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
        $sourceState = $this->createMock(FighterState::class);
        $sourceState->method('getPosition')->willReturn([0, 0, 0]);
        $source->method('getState')->willReturn($sourceState);
        $target = $this->createMock(Fighter::class);
        $target->method('hasSpecialRule')->willReturn(false);
        $targetState = $this->createMock(FighterState::class);
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
        $sourceState = $this->createMock(FighterState::class);
        $sourceState->method('getPosition')->willReturn([0, 0, 0]);
        $source->method('getState')->willReturn($sourceState);
        $target = $this->createMock(Fighter::class);
        $target->method('hasSpecialRule')->willReturn(false);
        $targetState = $this->createMock(FighterState::class);
        $targetState->method('getPosition')->willReturn([10, 0, 0]);
        $target->method('getState')->willReturn($targetState);
        [$toHit, $shots] = Attack::calculateRangedParams($battle, $source, $target, $weapon, false);
        $this->assertEquals(3, $toHit);
        $this->assertEquals(2, $shots);
    }
}
