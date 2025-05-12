<?php

use Mordheim\Characteristics;
use Mordheim\Data\Blank;
use Mordheim\Data\Spell;
use Mordheim\EquipmentManager;
use Mordheim\Fighter;
use Mordheim\FighterAdvancement;
use Mordheim\SpecialRule;
use Mordheim\Status;
use Mordheim\Strategy\AggressiveStrategy;

class PrayersOfSigmarTest extends MordheimTestCase
{
    private function makeFighterWithSpell(Spell $spell, array $pos = [0, 0, 0]): Fighter
    {
        $adv = new FighterAdvancement(new Characteristics(), [], [$spell]);
        return new Fighter(
            Blank::REIKLAND_CHAMPION,
            $adv,
            new EquipmentManager([]),
            new \Mordheim\FighterState($pos, new AggressiveStrategy(), 2)
        );
    }

    public function testArmourOfRighteousnessEffects()
    {
        $fighter = $this->makeFighterWithSpell(Spell::ARMOUR_OF_RIGHTEOUSNESS);
        $fighter->getState()->addActiveSpell(Spell::ARMOUR_OF_RIGHTEOUSNESS);
        $this->assertEquals(2, $fighter->getArmorSave(null));
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
        $fighter->getState()->addActiveSpell(Spell::HEARTS_OF_STEEL);
        $this->assertTrue($fighter->hasSpecialRule(SpecialRule::FEARSOME));
    }

    public function testHealingHandHealsAndStands()
    {
        $fighter = $this->makeFighterWithSpell(Spell::HEALING_HAND, [0, 0, 0]);
        $target = $this->makeFighterWithSpell(Spell::HAMMER_OF_SIGMAR, [1, 0, 0]);
        $target->getState()->modifyWounds(-1);
        $fighter->getState()->setStatus(Status::STUNNED);
        $battle = new \Mordheim\Battle(new \Mordheim\GameField(), [
            new \Mordheim\Warband('Sigmar', [$fighter, $target])
        ]);
        $result = Spell::HEALING_HAND->onPhaseMagic($battle, $fighter);
        $this->assertTrue($result);
        $this->assertEquals($fighter->getWounds(), $fighter->getState()->getWounds());
        $this->assertEquals(Status::STANDING, $fighter->getState()->getStatus());
    }
} 