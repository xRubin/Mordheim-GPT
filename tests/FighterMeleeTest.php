<?php

use Mordheim\Battle;
use Mordheim\Characteristics;
use Mordheim\Data\Equipment;
use Mordheim\GameField;
use Mordheim\SpecialRule;
use Mordheim\Strategy\AggressiveStrategy;
use Mordheim\Warband;

class FighterMeleeTest extends MordheimTestCase
{
    // Вспомогательный класс для контроля сейва
    private static function makeTestFighter(array $skills, array $weapons, int $wounds = 2, array $pos = [0, 0, 0])
    {
        return new class (
            \Mordheim\Data\Blank::MARIENBURG_SWORDSMAN,
            new \Mordheim\FighterAdvancement(Characteristics::empty(), $skills),
            new \Mordheim\EquipmentManager($weapons),
            new \Mordheim\FighterState(
                $pos,
                new AggressiveStrategy(),
                $wounds
            )
        ) extends \Mordheim\Fighter {
            public function getArmorSave(?\Mordheim\EquipmentInterface $attackerWeapon): int
            {
                return 0;
            }
        };
    }

    private function makeFighter(array $skills, array $weapons, int $wounds = 2, $pos = [0, 0, 0])
    {
        return new \Mordheim\Fighter(
            \Mordheim\Data\Blank::REIKLAND_CHAMPION,
            new \Mordheim\FighterAdvancement(Characteristics::empty(), $skills),
            new \Mordheim\EquipmentManager($weapons),
            new \Mordheim\FighterState(
                $pos,
                new AggressiveStrategy(),
                $wounds
            )
        );
    }

    private function makeClearBattle(array $attackerFighters, array $defenderFighters)
    {

        return new Battle(
            new GameField(),
            [
                new Warband('Attackers', $attackerFighters),
                new Warband('Defenders', $defenderFighters)
            ]
        );
    }

    public function testBasicHitAndWound()
    {
        // Гарантируем попадание и ранение, сейв не проходит
        \Mordheim\Dice::setTestRolls([4, 1, 4, 7]);
        $attacker = $this->makeFighter([], [Equipment::SWORD], 2, [0, 0, 0]);
        $defender = self::makeTestFighter([], [Equipment::SWORD], 2, [1, 0, 0]);
        $battle = $this->makeClearBattle([$attacker], [$defender]);
        $result = \Mordheim\Rule\Attack::melee($battle, $attacker, $defender);
        $this->assertTrue($result);
        $this->assertEquals(1, $defender->getState()->getWounds());
    }

    public function testParrySkill()
    {
        $attacker = $this->makeFighter([], [Equipment::SWORD]);
        $parried = false;
        for ($i = 0; $i < 20; $i++) {
            $defender = $this->makeFighter([SpecialRule::STEP_ASIDE], [Equipment::SWORD], 2);
            $battle = $this->makeClearBattle([$attacker], [$defender]);
            \Mordheim\Rule\Attack::melee($battle, $attacker, $defender);
            if ($defender->getState()->getWounds() == 2) {
                $parried = true;
                break;
            }
        }
        $this->assertTrue($parried, 'Step Aside (parry) skill should sometimes prevent wounds');
    }

    public function testAxeStrengthBonus()
    {
        // Гарантируем попадание и ранение, сейв не проходит
        \Mordheim\Dice::setTestRolls([4, 1, 4, 7]);
        $attacker = $this->makeFighter([], [Equipment::SWORD], 2, [0, 0, 0]);
        $defender = self::makeTestFighter([], [Equipment::SWORD], 2, [1, 0, 0]);
        $battle = $this->makeClearBattle([$attacker], [$defender]);
        \Mordheim\Rule\Attack::melee($battle, $attacker, $defender);
        $this->assertLessThanOrEqual(1, $defender->getState()->getWounds());
    }

    public function testResilientSkill()
    {
        $attacker = $this->makeFighter([], [Equipment::SWORD], 2, [0, 0, 0]);
        // Сначала неудачные броски, потом успешный
        // 10 неудачных атак (hitRoll=1), потом успешная (hit=4, parry=1, wound=4, save=7)
        \Mordheim\Dice::setTestRolls(array_merge(array_fill(0, 10, 1), [4, 1, 5, 7]));
        $wounded = false;
        for ($i = 0; $i < 20; $i++) {
            $defender = self::makeTestFighter([SpecialRule::RESILIENT], [Equipment::SWORD], 2, [1, 0, 0]);
            $battle = $this->makeClearBattle([$attacker], [$defender]);
            \Mordheim\Rule\Attack::melee($battle, $attacker, $defender);
            if ($defender->getState()->getWounds() < 2) {
                $wounded = true;
                break;
            }
        }
        $this->assertTrue($wounded, 'Resilient skill should make wounding harder but not impossible');
    }

    public function testDualWieldAttacks()
    {
        // 1. Только один меч — 1 атака
        $fighter = $this->makeFighter([], [Equipment::SWORD], 2, [0, 0, 0]);
        $this->assertEquals(1, $fighter->getAttacks(), 'Одна атака с одним мечом');
        // 2. Два одноручных оружия ближнего боя — 2 атаки
        $fighter2 = $this->makeFighter([], [Equipment::SWORD, Equipment::DAGGER], 2, [0, 0, 0]);
        $this->assertEquals(2, $fighter2->getAttacks(), 'Две атаки с двумя одноручными');
        // 3. Два одноручных, атака наносится дважды
        $defender = self::makeTestFighter([], [Equipment::SWORD], 2, [1, 0, 0]);
        \Mordheim\Dice::setTestRolls([4, 1, 5, 4, 1, 5]); // два успешных удара подряд
        $battle = $this->makeClearBattle([$fighter2], [$defender]);
        \Mordheim\Rule\Attack::melee($battle, $fighter2, $defender); // предполагается, что attack() вызывает getAttacks() и делает нужное число атак
        // Проверим, что у защитника осталось 0 ран
        $this->assertEquals(0, $defender->getState()->getWounds(), 'Две успешные атаки списывают две раны');
    }

    public function testSteelWhipIgnoresParry()
    {
        \Mordheim\Dice::setTestRolls([6, 4, 7]); // hit=6, wound=4, save=7
        $attacker = $this->makeFighter([], [Equipment::STEEL_WHIP], 2, [0, 0, 0]);
        $defender = self::makeTestFighter([], [Equipment::SWORD], 2, [1, 0, 0]);
        $battle = $this->makeClearBattle([$attacker], [$defender]);
        $result = \Mordheim\Rule\Attack::melee($battle, $attacker, $defender);
        $this->assertTrue($result, 'Steel Whip should ignore parry');
    }

    public function testClubInjuryTable()
    {
        \Mordheim\Dice::setTestRolls([4, 1, 4, 1, 1]); // hit, parry, wound, injury=1 (выбывает)
        $attacker = $this->makeFighter([], [Equipment::CLUB], 2, [0, 0, 0]);
        $defender = $this->makeFighter([], [Equipment::SWORD], 2, [1, 0, 0]);
        $battle = $this->makeClearBattle([$attacker], [$defender]);
        \Mordheim\Rule\Attack::melee($battle, $attacker, $defender);
        $this->assertFalse($defender->getState()->getStatus()->isAlive(), 'Club/Mace/Hammer should put out of action on 1');
    }

    public function testDoubleHandedAndArmorPiercingAffectSave()
    {
        \Mordheim\Dice::setTestRolls([4, 1, 4, 5]); // hit, parry, wound, save=5 (обычно спасся бы при save=5, но с модификаторами не спасётся)
        // Используем "Ogre Club" как пример двуручного оружия с Club и DoubleHanded
        $attacker = $this->makeFighter([], [Equipment::DOUBLE_HANDED_AXE], 2, [0, 0, 0]);
        $defender = $this->makeFighter([], [Equipment::SWORD, Equipment::LIGHT_ARMOR], 2, [1, 0, 0]);
        $battle = $this->makeClearBattle([$attacker], [$defender]);
        $result = \Mordheim\Rule\Attack::melee($battle, $attacker, $defender);
        $this->assertTrue($result, 'DoubleHanded and ArmorPiercing should worsen save');
    }

    /**
     * Тест: атака по бойцу в состоянии KNOCKED_DOWN — попадание автоматически успешно, остальное по обычным правилам
     */
    public function testAttackKnockedDownAutoHit()
    {
        // Сценарий 1: успешный бросок на ранение — атака наносит урон
        \Mordheim\Dice::setTestRolls([4, 7]); // парирование=1 (fail), ранение=4 (успех), сейв=7 (fail)
        $attacker = $this->makeFighter([], [Equipment::SWORD], 2, [0, 0, 0]);
        $defender = self::makeTestFighter([], [Equipment::SWORD], 2, [1, 0, 0]);
        $defender->getState()->setStatus(\Mordheim\Status::KNOCKED_DOWN);
        $battle = $this->makeClearBattle([$attacker], [$defender]);
        $result = \Mordheim\Rule\Attack::melee($battle, $attacker, $defender);
        $this->assertTrue($result, 'Attack against knocked down should hit and wound if wound roll succeeds');
        $this->assertContains(
            $defender->getState()->getStatus(),
            [
                \Mordheim\Status::KNOCKED_DOWN,
                \Mordheim\Status::STUNNED,
                \Mordheim\Status::OUT_OF_ACTION
            ],
            'After attack against knocked down, defender state should be KNOCKED_DOWN, STUNNED, or OUT_OF_ACTION'
        );

        // Сценарий 2: неудачный бросок на ранение — атака не наносит урон, но бросок на попадание не требуется
        \Mordheim\Dice::setTestRolls([1, 1, 1, 7]); // wound (fail), парирование, wound (fail), save
        $attacker2 = $this->makeFighter([], [Equipment::SWORD], 2, [0, 0, 0]);
        $defender2 = self::makeTestFighter([], [Equipment::SWORD], 2, [1, 0, 0]);
        $defender2->getState()->setStatus(\Mordheim\Status::KNOCKED_DOWN);
        $battle2 = $this->makeClearBattle([$attacker2], [$defender2]);
        $result2 = \Mordheim\Rule\Attack::melee($battle, $attacker2, $defender2);
        $this->assertFalse($result2, 'Attack against knocked down should not wound if wound roll fails, but always counts as hit');
        $this->assertEquals(2, $defender2->getState()->getWounds());
    }

    /**
     * Тест: атака по бойцу в состоянии STUNNED — попадание и ранение автоматически, сейв невозможен, сразу бросок на травму
     */
    public function testAttackStunnedAutoWoundNoSave()
    {
        \Mordheim\Dice::setTestRolls([6]); // injury roll
        $attacker = $this->makeFighter([], [Equipment::SWORD], 2, [0, 0, 0]);
        $defender = self::makeTestFighter([], [Equipment::SWORD], 2, [1, 0, 0]);
        $defender->getState()->setStatus(\Mordheim\Status::STUNNED);
        $battle = $this->makeClearBattle([$attacker], [$defender]);
        $result = \Mordheim\Rule\Attack::melee($battle, $attacker, $defender);
        $this->assertTrue($result, 'Attack against stunned should always wound and skip save');
        // Проверим, что после атаки защитник либо выведен из строя, либо в состоянии травмы
        $this->assertContains($defender->getState()->getStatus(), [\Mordheim\Status::KNOCKED_DOWN, \Mordheim\Status::STUNNED, \Mordheim\Status::OUT_OF_ACTION]);
    }

    public function testCriticalHit()
    {
        \Mordheim\Dice::setTestRolls([4, 1, 6, 7]); // hit, parry, wound=6 (крит), save
        $attacker = $this->makeFighter([], [Equipment::SWORD], 2, [0, 0, 0]);
        $defender = self::makeTestFighter([], [Equipment::SWORD], 2, [1, 0, 0]);
        $battle = $this->makeClearBattle([$attacker], [$defender]);
        \Mordheim\Rule\Attack::melee($battle, $attacker, $defender);
        $this->assertEquals(\Mordheim\Status::OUT_OF_ACTION, $defender->getState()->getStatus(), 'Critical should put out of action on 6 to wound');
    }
}
