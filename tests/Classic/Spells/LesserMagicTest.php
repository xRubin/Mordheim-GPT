<?php

namespace Classic\Spells;

use Mordheim\Band;
use Mordheim\Characteristics;
use Mordheim\Classic\Battle;
use Mordheim\Classic\Blank;
use Mordheim\Classic\Equipment;
use Mordheim\Classic\EquipmentManager;
use Mordheim\Classic\Fighter;
use Mordheim\Classic\FighterAdvancement;
use Mordheim\Classic\FighterState;
use Mordheim\Classic\Spell;
use Mordheim\Classic\Strategy\AggressiveStrategy;
use Mordheim\Dice;
use Mordheim\GameField;

class LesserMagicTest extends \MordheimTestCase
{
    private function makeFighterWithSpell(Spell $spell, $pos = [0, 0, 0], $wounds = 2): Fighter
    {
        $adv = new FighterAdvancement(new Characteristics(), [], [$spell]);
        return new Fighter(
            Blank::WARLOCK,
            $adv,
            new EquipmentManager([Equipment::STAFF]),
            new FighterState($pos, new AggressiveStrategy(), $wounds)
        );
    }

    private function makeEnemy($pos = [2, 0, 0], $wounds = 2): Fighter
    {
        return new Fighter(
            Blank::UNDEAD_ZOMBIE,
            FighterAdvancement::empty(),
            new EquipmentManager([]),
            new FighterState($pos, new AggressiveStrategy(), $wounds)
        );
    }

    public function testFiresOfUzhul()
    {
        $caster = $this->makeFighterWithSpell(Spell::FIRES_OF_UZHUL, [0, 0, 0]);
        $enemy = $this->makeEnemy([1, 0, 0], 2);
        $battle = new Battle(new GameField(), [
            new Band('Wizards', [$caster]),
            new Band('Undead', [$enemy])
        ]);
        Dice::setTestRolls([6, 6, 6]);
        $result = Spell::FIRES_OF_UZHUL->getProcessor()->onPhaseMagic($battle, $caster);
        $this->assertTrue($result);
        $this->assertLessThan(2, $enemy->getState()->getWounds());
    }

    public function testFlightOfZimmeran()
    {
        $caster = $this->makeFighterWithSpell(Spell::FLIGHT_OF_ZIMMERAN, [0, 0, 0]);
        $enemy = $this->makeEnemy([5, 0, 0], 2);
        $battle = new Battle(new GameField(), [
            new Band('Wizards', [$caster]),
            new Band('Undead', [$enemy])
        ]);
        Dice::setTestRolls([6, 6]);
        $result = Spell::FLIGHT_OF_ZIMMERAN->getProcessor()->onPhaseMagic($battle, $caster);
        $this->assertTrue($result);
        $this->assertNotEquals([0, 0, 0], $caster->getState()->getPosition());
    }

    public function testDreadOfAramar()
    {
        $caster = $this->makeFighterWithSpell(Spell::DREAD_OF_ARAMAR, [0, 0, 0]);
        $enemy = new Fighter(
            Blank::REIKLAND_YOUNGBLOOD,
            FighterAdvancement::empty(),
            new EquipmentManager([]),
            new FighterState([3, 3, 0], new AggressiveStrategy(), 2)
        );
        $battle = new Battle(new GameField(), [
            new Band('Wizards', [$caster]),
            new Band('Reikland', [$enemy])
        ]);
        Dice::setTestRolls([6, 6, 6, 6]); // Провал лидерства
        $result = Spell::DREAD_OF_ARAMAR->getProcessor()->onPhaseMagic($battle, $caster);
        $this->assertTrue($result);
        $this->assertNotEquals([3, 3, 0], $enemy->getState()->getPosition());
    }

    public function testSilverArrowsOfArha()
    {
        $caster = $this->makeFighterWithSpell(Spell::SILVER_ARROWS_OF_ARHA, [0, 0, 0]);
        $enemy = $this->makeEnemy([10, 0, 0], 3);
        $battle = new Battle(new GameField(), [
            new Band('Wizards', [$caster]),
            new Band('Undead', [$enemy])
        ]);
        Dice::setTestRolls([6, 6, 6, 6, 6, 6, 6, 6, 6]); // D6+2 попаданий, все успешные
        $result = Spell::SILVER_ARROWS_OF_ARHA->getProcessor()->onPhaseMagic($battle, $caster);
        $this->assertTrue($result);
        $this->assertLessThan(3, $enemy->getState()->getWounds());
    }

    public function testLuckOfShemtek()
    {
        $caster = $this->makeFighterWithSpell(Spell::LUCK_OF_SHEMTEK, [0, 0, 0]);
        $battle = new Battle(new GameField(), [
            new Band('Wizards', [$caster])
        ]);
        Dice::setTestRolls([6, 6]);
        $result = Spell::LUCK_OF_SHEMTEK->getProcessor()->onPhaseMagic($battle, $caster);
        $this->assertTrue($result);
        $this->assertTrue($caster->getState()->hasActiveSpell(Spell::LUCK_OF_SHEMTEK));
    }

    public function testSwordOfRezhebel()
    {
        $caster = $this->makeFighterWithSpell(Spell::SWORD_OF_REZHEBEL, [0, 0, 0]);
        $battle = new Battle(new GameField(), [
            new Band('Wizards', [$caster])
        ]);
        Dice::setTestRolls([6, 6]);
        $result = Spell::SWORD_OF_REZHEBEL->getProcessor()->onPhaseMagic($battle, $caster);
        $this->assertTrue($result);
        $this->assertTrue($caster->getState()->hasActiveSpell(Spell::SWORD_OF_REZHEBEL));
    }
} 