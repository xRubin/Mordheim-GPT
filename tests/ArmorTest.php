<?php

use Mordheim\Data\Armors;
use PHPUnit\Framework\TestCase;

class ArmorTest extends TestCase
{
    public function testLightArmorSave()
    {
        $armor = Armors::getByName('Light Armor');
        $this->assertEquals(6, $armor->getFinalSave());
        $this->assertEquals(5, $armor->getFinalSave(1)); // -1 AP
    }

    public function testHeavyArmorSave()
    {
        $armor = Armors::getByName('Heavy Armor');
        $this->assertEquals(5, $armor->getFinalSave());
        $this->assertEquals(4, $armor->getFinalSave(1));
    }

    public function testShieldBonus()
    {
        $armor = Armors::getByName('Shield');
        $this->assertEquals(5, $armor->getFinalSave(0, false, true)); // shield in melee
    }

    public function testSaveCannotBeLowerThan2OrHigherThan6()
    {
        $armor = Armors::getByName('Shield');
        $this->assertEquals(2, $armor->getFinalSave(4, false, false));
        $armor = Armors::getByName('Light Armor');
        $this->assertEquals(6, $armor->getFinalSave(-2));
    }

    public function testHeavyArmorSaveRule()
    {
        $armor = Armors::getByName('Heavy Armor');
        $this->assertEquals(5, $armor->getFinalSave(), 'Heavy Armor with SAVE should always give 5+');
        $armor = Armors::getByName('Heavy Armor');
        $this->assertEquals(5, $armor->getFinalSave(), 'Heavy Armor with SAVE should always give 5+ even if baseSave is 6');
        $armor = Armors::getByName('Heavy Armor');
        $this->assertEquals(5, $armor->getFinalSave(), 'Heavy Armor with SAVE should always give 5+ even if baseSave is 2');
    }
}
