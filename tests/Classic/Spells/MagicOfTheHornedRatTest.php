<?php

namespace Classic\Spells;

use Mordheim\Band;
use Mordheim\Characteristics;
use Mordheim\Classic\Battle;
use Mordheim\Classic\Blank;
use Mordheim\Classic\EquipmentManager;
use Mordheim\Classic\Fighter;
use Mordheim\Classic\FighterAdvancement;
use Mordheim\Classic\FighterState;
use Mordheim\Classic\Ruler;
use Mordheim\Classic\Spell;
use Mordheim\Classic\Status;
use Mordheim\Classic\Strategy\AggressiveStrategy;
use Mordheim\Dice;
use Mordheim\GameField;

class MagicOfTheHornedRatTest extends \MordheimTestCase
{
    private function makeFighter($name = 'Test', $pos = [0, 0, 0], $wounds = 2, $status = Status::STANDING): Fighter
    {
        return (new Fighter(
            Blank::SKAVEN_ESHIN_SORCERER,
            new FighterAdvancement(new Characteristics(wounds: $wounds)),
            new EquipmentManager([]),
            new FighterState($pos, new AggressiveStrategy(), $wounds, $status))
        )->setName($name);
    }

    // --- Тест Warpfire ---
    public function testWarpfireDamagesTargetAndAoE()
    {
        $caster = $this->makeFighter('Eshin Sorcerer', [0, 0, 0]);
        $enemy = $this->makeFighter('Enemy', [2, 0, 0]);
        $aoe = $this->makeFighter('AoE', [2, 1, 0]);
        $battle = new Battle(new GameField(), [
            new Band('Skaven', [$caster]),
            new Band('Enemies', [$enemy, $aoe])
        ]);
        Dice::setTestRolls([6, 6, 1, 6, 6, 6]); // D3=1 попадания
        $result = Spell::WARPFIRE->getProcessor()->onPhaseMagic($battle, $caster);
        $this->assertTrue($result);
        $this->assertLessThan(2, $enemy->getState()->getWounds());
        $this->assertLessThan(2, $aoe->getState()->getWounds());
    }

    // --- Тест Children of the Horned Rat ---
    public function testChildrenOfTheHornedRatSummonsRats()
    {
        $caster = $this->makeFighter('Eshin Sorcerer', [0, 0, 0]);
        $battle = new Battle(new GameField(), [
            new Band('Skaven', [$caster])
        ]);
        Dice::setTestRolls([3]); // D3=3 крыс
        $result = Spell::CHILDREN_OF_THE_HORNED_RAT->getProcessor()->onPhaseMagic($battle, $caster);
        $this->assertTrue($result);
        $rats = array_filter($battle->getFighters(), fn($f) => $f->getBlank() === Blank::SKAVEN_GIANT_RAT);
        $this->assertCount(3, $rats);
    }

    // --- Тест Gnawdoom ---
    public function testGnawdoomDealsManyHits()
    {
        $caster = $this->makeFighter('Eshin Sorcerer', [0, 0, 0]);
        $enemy = $this->makeFighter('Enemy', [2, 0, 0]);
        $battle = new Battle(new GameField(), [
            new Band('Skaven', [$caster]),
            new Band('Enemies', [$enemy])
        ]);
        Dice::setTestRolls([6, 6, 1, 1, 6, 6]); // 2D6=2 попаданий
        $result = Spell::GNAWDOOM->getProcessor()->onPhaseMagic($battle, $caster);
        $this->assertTrue($result);
        $this->assertLessThan(2, $enemy->getState()->getWounds());
    }

    // --- Тест Black Fury ---
    public function testBlackFuryChargesAndBuffs()
    {
        $caster = $this->makeFighter('Eshin Sorcerer', [0, 0, 0]);
        $enemy = $this->makeFighter('Enemy', [10, 0, 0]);
        $battle = new Battle(new GameField(), [
            new Band('Skaven', [$caster]),
            new Band('Enemies', [$enemy])
        ]);
        Dice::setTestRolls([6, 6]);
        $result = Spell::BLACK_FURY->getProcessor()->onPhaseMagic($battle, $caster);
        $this->assertTrue($result);
        $this->assertTrue(Ruler::isAdjacent($caster, $enemy));
        $this->assertTrue($caster->getState()->hasActiveSpell(Spell::BLACK_FURY));
    }

    // --- Тест Eye of the Warp ---
    public function testEyeOfTheWarpCausesLeadershipTest()
    {
        $caster = $this->makeFighter('Eshin Sorcerer', [0, 0, 0]);
        $enemy = $this->makeFighter('Enemy', [1, 0, 0]);
        $battle = new Battle(new GameField(), [
            new Band('Skaven', [$caster]),
            new Band('Enemies', [$enemy])
        ]);
        Dice::setTestRolls([6, 6, 6, 6]);
        $result = Spell::EYE_OF_THE_WARP->getProcessor()->onPhaseMagic($battle, $caster);
        $this->assertTrue($result);
        // Проверяем, что враг больше не в контакте (убежал)
        $this->assertFalse(Ruler::isAdjacent($caster, $enemy));
    }

    // --- Тест Sorcerer's Curse ---
    public function testSorcerersCurseAffectsTarget()
    {
        $caster = $this->makeFighter('Eshin Sorcerer', [0, 0, 0]);
        $enemy = $this->makeFighter('Enemy', [10, 0, 0]);
        $battle = new Battle(new GameField(), [
            new Band('Skaven', [$caster]),
            new Band('Enemies', [$enemy])
        ]);
        Dice::setTestRolls([6, 6]);
        $result = Spell::SORCERERS_CURSE->getProcessor()->onPhaseMagic($battle, $caster);
        $this->assertTrue($result);
        $this->assertTrue($enemy->getState()->hasActiveSpell(Spell::SORCERERS_CURSE));
    }
} 