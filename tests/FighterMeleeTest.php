<?php

use Mordheim\Battle;
use Mordheim\Characteristics;
use Mordheim\Data\Armors;
use Mordheim\Data\Skills;
use Mordheim\Data\Weapons;
use Mordheim\EquipmentManager;
use Mordheim\Fighter;
use Mordheim\GameField;
use Mordheim\Strategy\AggressiveStrategy;
use Mordheim\Warband;
use PHPUnit\Framework\TestCase;

class FighterMeleeTest extends TestCase
{
    public function setUp(): void
    {
        \Mordheim\Dice::setTestRolls([]);
        \Mordheim\BattleLogger::clear();
        \Mordheim\BattleLogger::add("### Test: {$this->name()}");
    }

    public function tearDown(): void
    {
        \Mordheim\Dice::setTestRolls([]);
        \Mordheim\BattleLogger::print();
    }

    // Вспомогательный класс для контроля сейва
    private static function makeTestFighter($ws, $s, $skills, $weapons, $wounds = 2, $pos = [0, 0, 0])
    {
        $char = new Characteristics(
            4, $ws, 3, $s, 3, $wounds, 4, 1, 7
        );
        $equipment = new EquipmentManager($weapons);
        return new class(uniqid() . 'Guy', $char, $skills, $equipment, new AggressiveStrategy(), $pos) extends \Mordheim\Fighter {
            public function getArmorSave(?\Mordheim\Weapon $attackerWeapon): int
            {
                return 0;
            }
        };
    }

    private function makeFighter($ws, $s, $skills, $weapons, $wounds = 2, $pos = [0, 0, 0])
    {
        $char = new Characteristics(
            4,        // movement
            $ws,      // weaponSkill
            3,        // ballisticSkill
            $s,       // strength
            3,        // toughness
            $wounds,  // wounds
            4,        // initiative
            1,        // attacks
            7         // leadership
        );
        return new Fighter(
            uniqid() . 'Guy',
            $char,
            $skills,
            new EquipmentManager($weapons),
            new AggressiveStrategy(),
            $pos
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
        $attacker = $this->makeFighter(4, 3, [], [Weapons::getByName('Sword')], 2, [0, 0, 0]);
        $defender = self::makeTestFighter(3, 3, [], [Weapons::getByName('Sword')], 2, [1, 0, 0]);
        $battle = $this->makeClearBattle([$attacker], [$defender]);
        $result = \Mordheim\Rule\Attack::apply($battle, $attacker, $defender);
        $this->assertTrue($result);
        $this->assertEquals(1, $defender->characteristics->wounds);
    }

    public function testParrySkill()
    {
        $attacker = $this->makeFighter(4, 3, [], [Weapons::getByName('Sword')]);
        $defender = $this->makeFighter(3, 3, [Skills::getByName('Step Aside')], [Weapons::getByName('Sword')], 2);
        $parried = false;
        $battle = $this->makeClearBattle([$attacker], [$defender]);
        for ($i = 0; $i < 20; $i++) {
            $defender->characteristics->wounds = 2;
            \Mordheim\Rule\Attack::apply($battle, $attacker, $defender);
            if ($defender->characteristics->wounds == 2) {
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
        $attacker = $this->makeFighter(4, 3, [], [Weapons::getByName('Axe')], 2, [0, 0, 0]);
        $defender = self::makeTestFighter(3, 3, [], [Weapons::getByName('Sword')], 2, [1, 0, 0]);
        $battle = $this->makeClearBattle([$attacker], [$defender]);
        \Mordheim\Rule\Attack::apply($battle, $attacker, $defender);
        $this->assertLessThanOrEqual(1, $defender->characteristics->wounds);
    }

    public function testResilientSkill()
    {
        $attacker = $this->makeFighter(4, 3, [], [Weapons::getByName('Sword')], 2, [0, 0, 0]);
        $defender = self::makeTestFighter(3, 3, [Skills::getByName('Resilient')], [Weapons::getByName('Sword')], 2, [1, 0, 0]);
        // Сначала неудачные броски, потом успешный
        // 10 неудачных атак (hitRoll=1), потом успешная (hit=4, parry=1, wound=4, save=7)
        \Mordheim\Dice::setTestRolls(array_merge(array_fill(0, 10, 1), [4, 1, 5, 7]));
        $wounded = false;
        $battle = $this->makeClearBattle([$attacker], [$defender]);
        for ($i = 0; $i < 20; $i++) {
            $defender->characteristics->wounds = 2;
            \Mordheim\Rule\Attack::apply($battle, $attacker, $defender);
            if ($defender->characteristics->wounds < 2) {
                $wounded = true;
                break;
            }
        }
        $this->assertTrue($wounded, 'Resilient skill should make wounding harder but not impossible');
    }

    public function testDualWieldAttacks()
    {
        // 1. Только один меч — 1 атака
        $fighter = $this->makeFighter(4, 3, [], [Weapons::getByName('Sword')], 2, [0, 0, 0]);
        $this->assertEquals(1, $fighter->getAttacks(), 'Одна атака с одним мечом');
        // 2. Два одноручных оружия ближнего боя — 2 атаки
        $fighter2 = $this->makeFighter(4, 3, [], [Weapons::getByName('Sword'), Weapons::getByName('Dagger')], 2, [0, 0, 0]);
        $this->assertEquals(2, $fighter2->getAttacks(), 'Две атаки с двумя одноручными');
        // 3. Два одноручных, атака наносится дважды
        $defender = self::makeTestFighter(3, 3, [], [Weapons::getByName('Sword')], 2, [1, 0, 0]);
        \Mordheim\Dice::setTestRolls([4, 1, 5, 4, 1, 5]); // два успешных удара подряд
        $battle = $this->makeClearBattle([$fighter2], [$defender]);
        \Mordheim\Rule\Attack::apply($battle, $fighter2, $defender); // предполагается, что attack() вызывает getAttacks() и делает нужное число атак
        // Проверим, что у защитника осталось 0 ран
        $this->assertEquals(0, $defender->characteristics->wounds, 'Две успешные атаки списывают две раны');
    }

    public function testFlailIgnoresParry()
    {
        \Mordheim\Dice::setTestRolls([6, 4, 7]); // hit=6, wound=4, save=7
        $attacker = $this->makeFighter(4, 3, [], [Weapons::getByName('Flail')], 2, [0, 0, 0]);
        $defender = self::makeTestFighter(3, 3, [], [Weapons::getByName('Sword')], 2, [1, 0, 0]);
        $battle = $this->makeClearBattle([$attacker], [$defender]);
        $result = \Mordheim\Rule\Attack::apply($battle, $attacker, $defender);
        $this->assertTrue($result, 'Flail should ignore parry');
    }

    public function testClubInjuryTable()
    {
        \Mordheim\Dice::setTestRolls([4, 1, 4, 1, 1]); // hit, parry, wound, injury=1 (выбывает)
        $attacker = $this->makeFighter(4, 3, [], [Weapons::getByName('Club')], 2, [0, 0, 0], [\Mordheim\SpecialRule::CLUB]);
        $defender = $this->makeFighter(3, 3, [], [Weapons::getByName('Sword')], 2, [1, 0, 0]);
        $battle = $this->makeClearBattle([$attacker], [$defender]);
        \Mordheim\Rule\Attack::apply($battle, $attacker, $defender);
        $this->assertFalse($defender->alive, 'Club/Mace/Hammer should put out of action on 1');
    }

    public function testDoubleHandedAndArmorPiercingAffectSave()
    {
        \Mordheim\Dice::setTestRolls([4, 1, 4, 5]); // hit, parry, wound, save=5 (обычно спасся бы при save=5, но с модификаторами не спасётся)
        // Используем "Ogre Club" как пример двуручного оружия с Club и DoubleHanded
        $attacker = $this->makeFighter(4, 3, [], [Weapons::getByName('Ogre Club')], 2, [0, 0, 0]);
        $defender = $this->makeFighter(3, 3, [], [], 2, [1, 0, 0]);
        $defender->equipmentManager = new \Mordheim\EquipmentManager(
            [Weapons::getByName('Sword')],
            [Armors::getByName('Light Armor')]
        );
        $battle = $this->makeClearBattle([$attacker], [$defender]);
        $result = \Mordheim\Rule\Attack::apply($battle, $attacker, $defender);
        $this->assertTrue($result, 'DoubleHanded and ArmorPiercing should worsen save');
    }

    /**
     * Тест: атака по бойцу в состоянии KNOCKED_DOWN — попадание автоматически успешно, остальное по обычным правилам
     */
    public function testAttackKnockedDownAutoHit()
    {
        // Сценарий 1: успешный бросок на ранение — атака наносит урон
        \Mordheim\Dice::setTestRolls([4, 7]); // парирование=1 (fail), ранение=4 (успех), сейв=7 (fail)
        $attacker = $this->makeFighter(4, 3, [], [Weapons::getByName('Sword')], 2, [0, 0, 0]);
        $defender = self::makeTestFighter(3, 3, [], [Weapons::getByName('Sword')], 2, [1, 0, 0]);
        $defender->state = \Mordheim\FighterState::KNOCKED_DOWN;
        $battle = $this->makeClearBattle([$attacker], [$defender]);
        $result = \Mordheim\Rule\Attack::apply($battle, $attacker, $defender);
        $this->assertTrue($result, 'Attack against knocked down should hit and wound if wound roll succeeds');
        $this->assertContains(
            $defender->state,
            [
                \Mordheim\FighterState::KNOCKED_DOWN,
                \Mordheim\FighterState::STUNNED,
                \Mordheim\FighterState::OUT_OF_ACTION
            ],
            'After attack against knocked down, defender state should be KNOCKED_DOWN, STUNNED, or OUT_OF_ACTION'
        );

        // Сценарий 2: неудачный бросок на ранение — атака не наносит урон, но бросок на попадание не требуется
        \Mordheim\Dice::setTestRolls([1, 1, 1, 7]); // wound (fail), парирование, wound (fail), save
        $attacker2 = $this->makeFighter(4, 3, [], [Weapons::getByName('Sword')], 2, [0, 0, 0]);
        $defender2 = self::makeTestFighter(3, 3, [], [Weapons::getByName('Sword')], 2, [1, 0, 0]);
        $defender2->state = \Mordheim\FighterState::KNOCKED_DOWN;
        $battle2 = $this->makeClearBattle([$attacker2], [$defender2]);
        $result2 = \Mordheim\Rule\Attack::apply($battle, $attacker2, $defender2);
        $this->assertFalse($result2, 'Attack against knocked down should not wound if wound roll fails, but always counts as hit');
        $this->assertEquals(2, $defender2->characteristics->wounds);
    }

    /**
     * Тест: атака по бойцу в состоянии STUNNED — попадание и ранение автоматически, сейв невозможен, сразу бросок на травму
     */
    public function testAttackStunnedAutoWoundNoSave()
    {
        \Mordheim\Dice::setTestRolls([6]); // injury roll
        $attacker = $this->makeFighter(4, 3, [], [Weapons::getByName('Sword')], 2, [0, 0, 0]);
        $defender = self::makeTestFighter(3, 3, [], [Weapons::getByName('Sword')], 2, [1, 0, 0]);
        $defender->state = \Mordheim\FighterState::STUNNED;
        $battle = $this->makeClearBattle([$attacker], [$defender]);
        $result = \Mordheim\Rule\Attack::apply($battle, $attacker, $defender);
        $this->assertTrue($result, 'Attack against stunned should always wound and skip save');
        // Проверим, что после атаки защитник либо выведен из строя, либо в состоянии травмы
        $this->assertContains($defender->state, [\Mordheim\FighterState::KNOCKED_DOWN, \Mordheim\FighterState::STUNNED, \Mordheim\FighterState::OUT_OF_ACTION]);
    }

    public function testCriticalHit()
    {
        \Mordheim\Dice::setTestRolls([4, 1, 6, 7]); // hit, parry, wound=6 (крит), save
        $attacker = $this->makeFighter(4, 3, [], [Weapons::getByName('Sword')], 2, [0, 0, 0], [\Mordheim\SpecialRule::CRITICAL]);
        $defender = self::makeTestFighter(3, 3, [], [Weapons::getByName('Sword')], 2, [1, 0, 0]);
        $battle = $this->makeClearBattle([$attacker], [$defender]);
        \Mordheim\Rule\Attack::apply($battle, $attacker, $defender);
        $this->assertEquals(\Mordheim\FighterState::OUT_OF_ACTION, $defender->state, 'Critical should put out of action on 6 to wound');
    }
}
