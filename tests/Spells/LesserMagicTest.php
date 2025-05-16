<?php

class LesserMagicTest extends MordheimTestCase
{
    private function makeFighterWithSpell(Mordheim\Spell $spell, $pos = [0, 0, 0], $wounds = 2): \Mordheim\Fighter
    {
        $adv = new \Mordheim\FighterAdvancement(new \Mordheim\Characteristics(), [], [$spell]);
        return new \Mordheim\Fighter(
            Mordheim\Blank::WARLOCK,
            $adv,
            new \Mordheim\EquipmentManager([Mordheim\Equipment::STAFF]),
            new \Mordheim\FighterState($pos, new \Mordheim\Strategy\AggressiveStrategy(), $wounds)
        );
    }

    private function makeEnemy($pos = [2, 0, 0], $wounds = 2): \Mordheim\Fighter
    {
        return new \Mordheim\Fighter(
            Mordheim\Blank::UNDEAD_ZOMBIE,
            \Mordheim\FighterAdvancement::empty(),
            new \Mordheim\EquipmentManager([]),
            new \Mordheim\FighterState($pos, new \Mordheim\Strategy\AggressiveStrategy(), $wounds)
        );
    }

    public function testFiresOfUzhul()
    {
        $caster = $this->makeFighterWithSpell(Mordheim\Spell::FIRES_OF_UZHUL, [0, 0, 0]);
        $enemy = $this->makeEnemy([1, 0, 0], 2);
        $battle = new \Mordheim\Battle(new \Mordheim\GameField(), [
            new \Mordheim\Warband('Wizards', [$caster]),
            new \Mordheim\Warband('Undead', [$enemy])
        ]);
        \Mordheim\Dice::setTestRolls([6, 6, 6]);
        $result = Mordheim\Spell::FIRES_OF_UZHUL->getProcessor()->onPhaseMagic($battle, $caster);
        $this->assertTrue($result);
        $this->assertLessThan(2, $enemy->getState()->getWounds());
    }

    public function testFlightOfZimmeran()
    {
        $caster = $this->makeFighterWithSpell(Mordheim\Spell::FLIGHT_OF_ZIMMERAN, [0, 0, 0]);
        $enemy = $this->makeEnemy([5, 0, 0], 2);
        $battle = new \Mordheim\Battle(new \Mordheim\GameField(), [
            new \Mordheim\Warband('Wizards', [$caster]),
            new \Mordheim\Warband('Undead', [$enemy])
        ]);
        \Mordheim\Dice::setTestRolls([6, 6]);
        $result = Mordheim\Spell::FLIGHT_OF_ZIMMERAN->getProcessor()->onPhaseMagic($battle, $caster);
        $this->assertTrue($result);
        $this->assertNotEquals([0, 0, 0], $caster->getState()->getPosition());
    }

    public function testDreadOfAramar()
    {
        $caster = $this->makeFighterWithSpell(Mordheim\Spell::DREAD_OF_ARAMAR, [0, 0, 0]);
        $enemy = new \Mordheim\Fighter(
            Mordheim\Blank::REIKLAND_YOUNGBLOOD,
            \Mordheim\FighterAdvancement::empty(),
            new \Mordheim\EquipmentManager([]),
            new \Mordheim\FighterState([3, 3, 0], new \Mordheim\Strategy\AggressiveStrategy(), 2)
        );
        $battle = new \Mordheim\Battle(new \Mordheim\GameField(), [
            new \Mordheim\Warband('Wizards', [$caster]),
            new \Mordheim\Warband('Reikland', [$enemy])
        ]);
        \Mordheim\Dice::setTestRolls([6, 6, 6, 6]); // Провал лидерства
        $result = Mordheim\Spell::DREAD_OF_ARAMAR->getProcessor()->onPhaseMagic($battle, $caster);
        $this->assertTrue($result);
        $this->assertNotEquals([3, 3, 0], $enemy->getState()->getPosition());
    }

    public function testSilverArrowsOfArha()
    {
        $caster = $this->makeFighterWithSpell(Mordheim\Spell::SILVER_ARROWS_OF_ARHA, [0, 0, 0]);
        $enemy = $this->makeEnemy([10, 0, 0], 3);
        $battle = new \Mordheim\Battle(new \Mordheim\GameField(), [
            new \Mordheim\Warband('Wizards', [$caster]),
            new \Mordheim\Warband('Undead', [$enemy])
        ]);
        \Mordheim\Dice::setTestRolls([6, 6, 6, 6, 6, 6, 6, 6, 6]); // D6+2 попаданий, все успешные
        $result = Mordheim\Spell::SILVER_ARROWS_OF_ARHA->getProcessor()->onPhaseMagic($battle, $caster);
        $this->assertTrue($result);
        $this->assertLessThan(3, $enemy->getState()->getWounds());
    }

    public function testLuckOfShemtek()
    {
        $caster = $this->makeFighterWithSpell(Mordheim\Spell::LUCK_OF_SHEMTEK, [0, 0, 0]);
        $battle = new \Mordheim\Battle(new \Mordheim\GameField(), [
            new \Mordheim\Warband('Wizards', [$caster])
        ]);
        \Mordheim\Dice::setTestRolls([6, 6]);
        $result = Mordheim\Spell::LUCK_OF_SHEMTEK->getProcessor()->onPhaseMagic($battle, $caster);
        $this->assertTrue($result);
        $this->assertTrue($caster->getState()->hasActiveSpell(Mordheim\Spell::LUCK_OF_SHEMTEK));
    }

    public function testSwordOfRezhebel()
    {
        $caster = $this->makeFighterWithSpell(Mordheim\Spell::SWORD_OF_REZHEBEL, [0, 0, 0]);
        $battle = new \Mordheim\Battle(new \Mordheim\GameField(), [
            new \Mordheim\Warband('Wizards', [$caster])
        ]);
        \Mordheim\Dice::setTestRolls([6, 6]);
        $result = Mordheim\Spell::SWORD_OF_REZHEBEL->getProcessor()->onPhaseMagic($battle, $caster);
        $this->assertTrue($result);
        $this->assertTrue($caster->getState()->hasActiveSpell(Mordheim\Spell::SWORD_OF_REZHEBEL));
    }
} 