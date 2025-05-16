<?php

use Mordheim\Blank;
use Mordheim\Characteristics;
use Mordheim\EquipmentManager;
use Mordheim\Fighter;
use Mordheim\FighterAdvancement;
use Mordheim\SpecialRule;
use Mordheim\Spell;
use Mordheim\Status;
use Mordheim\Strategy\AggressiveStrategy;

class PrayersOfSigmarTest extends MordheimTestCase
{
    private function makeFighterWithSpell(Spell $spell, array $pos = [0, 0, 0]): Fighter
    {
        $adv = new FighterAdvancement(new Characteristics(), [], [$spell]);
        return (new Fighter(
            Blank::REIKLAND_CHAMPION,
            $adv,
            new EquipmentManager([]),
            new \Mordheim\FighterState($pos, new AggressiveStrategy(), 2)
        ))->setName(uniqid());
    }

    public function testArmourOfRighteousnessEffects()
    {
        $fighter = $this->makeFighterWithSpell(Spell::ARMOUR_OF_RIGHTEOUSNESS);
        $fighter->getState()->addActiveSpell(Spell::ARMOUR_OF_RIGHTEOUSNESS);
        $this->assertEquals(2, $fighter->getArmourSave(null));
        $this->assertTrue($fighter->getState()->hasActiveSpell(Spell::ARMOUR_OF_RIGHTEOUSNESS));
        $this->assertTrue($fighter->hasSpecialRule(SpecialRule::FEARSOME));
    }

    public function testHammerOfSigmarEffects()
    {
        $fighter = $this->makeFighterWithSpell(Spell::HAMMER_OF_SIGMAR);
        $fighter->getState()->addActiveSpell(Spell::HAMMER_OF_SIGMAR);
        $this->assertTrue($fighter->hasSpecialRule(SpecialRule::PLUS_2_STRENGTH));
        $this->assertTrue($fighter->hasSpecialRule(SpecialRule::DOUBLE_DAMAGE));
    }

    public function testShieldOfFaithImmuneToMagic()
    {
        $fighter = $this->makeFighterWithSpell(Spell::SHIELD_OF_FAITH);
        $fighter->getState()->addActiveSpell(Spell::SHIELD_OF_FAITH);
        $this->assertTrue($fighter->hasSpecialRule(SpecialRule::IMMUNE_TO_SPELLS));
    }

    public function testHeartsOfSteelImmuneToFear()
    {
        $fighter = $this->makeFighterWithSpell(Spell::HEARTS_OF_STEEL);
        $target = $this->makeFighterWithSpell(Spell::HAMMER_OF_SIGMAR, [1, 0, 0]);
        $battle = new \Mordheim\Battle(new \Mordheim\GameField(), [
            new \Mordheim\Warband('Sigmar', [$fighter, $target])
        ]);
        \Mordheim\Dice::setTestRolls([6, 6]);
        $result = Spell::HEARTS_OF_STEEL->getProcessor()->onPhaseMagic($battle, $fighter);
        $this->assertTrue($result);
        $this->assertContains(Spell::HEARTS_OF_STEEL_TARGET, $target->getState()->getActiveSpells());
        $this->assertTrue($target->hasSpecialRule(SpecialRule::FEARSOME));
    }

    public function testHealingHandHealsAndStands()
    {
        $fighter = $this->makeFighterWithSpell(Spell::HEALING_HAND, [0, 0, 0]);
        $target = $this->makeFighterWithSpell(Spell::HAMMER_OF_SIGMAR, [1, 0, 0]);
        $target->getState()->modifyWounds(-1);
        $target->getState()->setStatus(Status::STUNNED);
        $battle = new \Mordheim\Battle(new \Mordheim\GameField(), [
            new \Mordheim\Warband('Sigmar', [$fighter, $target])
        ]);
        \Mordheim\Dice::setTestRolls([6, 6]);
        $result = Spell::HEALING_HAND->getProcessor()->onPhaseMagic($battle, $fighter);
        $this->assertTrue($result);
        $this->assertEquals($fighter->getWounds(), $fighter->getState()->getWounds());
        $this->assertEquals(Status::STANDING, $fighter->getState()->getStatus());
    }
} 