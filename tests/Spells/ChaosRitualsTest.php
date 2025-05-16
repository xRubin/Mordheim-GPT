<?php

use Mordheim\Battle;
use Mordheim\Blank;
use Mordheim\Characteristics;
use Mordheim\EquipmentManager;
use Mordheim\Fighter;
use Mordheim\FighterAdvancement;
use Mordheim\FighterState;
use Mordheim\Spell;
use Mordheim\Status;
use Mordheim\Strategy\AggressiveStrategy;

class ChaosRitualsTest extends MordheimTestCase
{
    private function makeFighter($name = 'Test', $pos = [0, 0, 0], $wounds = 2, $status = Status::STANDING): Fighter
    {
        return (new Fighter(
            Blank::CULT_MAGISTER,
            new FighterAdvancement(new Characteristics(wounds: $wounds)),
            new EquipmentManager([]),
            new FighterState($pos, new AggressiveStrategy(), $wounds, $status))
        )->setName($name);
    }

    public function testVisionOfTormentStunsEnemy()
    {
        $battle = $this->createMock(Battle::class);
        $mage = $this->makeFighter('Mage', [0, 0, 0]);
        $enemy = $this->makeFighter('Enemy', [1, 0, 0]);
        $battle->method('getEnemiesFor')->willReturn([$enemy]);
        \Mordheim\Dice::setTestRolls([6, 6]);
        $result = Spell::VISION_OF_TORMENT->getProcessor()->onPhaseMagic($battle, $mage);
        $this->assertTrue($result);
        $this->assertEquals(Status::STUNNED, $enemy->getState()->getStatus());
    }

    public function testEyeOfGodBuffsAlly()
    {
        $battle = $this->createMock(Battle::class);
        $mage = $this->makeFighter('Mage', [0, 0, 0]);
        $ally = $this->makeFighter('Ally', [1, 0, 0]);
        $oldCharacteristics = clone $ally->getState()->getCharacteristics();
        $battle->method('getAlliesFor')->willReturn([$ally]);
        $battle->method('getEnemiesFor')->willReturn([]);
        \Mordheim\Dice::setTestRolls([6, 6, 3]);
        $result = Spell::EYE_OF_GOD->getProcessor()->onPhaseMagic($battle, $mage);
        $this->assertTrue($result);
        $this->assertNotEquals($oldCharacteristics, $ally->getState()->getCharacteristics());
    }

    public function testDarkBloodDamagesEnemy()
    {
        $battle = $this->createMock(Battle::class);
        $mage = $this->makeFighter('Mage', [0, 0, 0]);
        // Враг без сейва
        $enemy = new class(
            Blank::CULT_MAGISTER,
            new FighterAdvancement(new Characteristics(wounds: 5)),
            new EquipmentManager([]),
            new FighterState([4, 0, 0], new AggressiveStrategy(), 5)
        ) extends Fighter {
            public function getArmourSave(?\Mordheim\Equipment $attackerWeapon): int { return 0; }
        };
        $enemy->setName('Enemy');
        $battle->method('getEnemiesFor')->willReturn([$enemy]);
        \Mordheim\Dice::setTestRolls([6, 6, 1, 6]);
        $result = Spell::DARK_BLOOD->getProcessor()->onPhaseMagic($battle, $mage);
        $this->assertTrue($result);
        $this->assertLessThan(5, $enemy->getState()->getWounds());
    }

    public function testLureOfChaosControlsEnemy()
    {
        $battle = $this->createMock(Battle::class);
        $mage = $this->makeFighter('Mage', [0, 0, 0]);
        $enemy = $this->makeFighter('Enemy', [1, 0, 0]);
        $battle->method('getEnemiesFor')->willReturn([$enemy]);
        \Mordheim\Dice::setTestRolls([6, 6, 6, 1]); // Гарантируем успех мага и провал врага
        $result = Spell::LURE_OD_CHAOS->getProcessor()->onPhaseMagic($battle, $mage);
        $this->assertTrue($result);
        $this->assertTrue($enemy->getState()->hasActiveSpell(Spell::LURE_OD_CHAOS));
    }

    public function testWingsOfDarknessMovesMage()
    {
        $mage = $this->makeFighter('Mage', [0, 0, 0]);
        $enemy = $this->makeFighter('Enemy', [2, 2, 0]);

        $battle = new Battle(
            new \Mordheim\GameField(),
            [
                new \Mordheim\Warband('Attackers', [$mage]),
                new \Mordheim\Warband('Defenders', [$enemy])
            ]
        );
        \Mordheim\Dice::setTestRolls([6, 6]);
        $result = Spell::WINGS_OF_DARKNESS->getProcessor()->onPhaseMagic($battle, $mage);
        $this->assertTrue($result);
        $this->assertNotEquals([0, 0, 0], $mage->getState()->getPosition());
        // Проверяем, что маг стоит рядом с врагом
        $this->assertTrue(\Mordheim\Ruler::isAdjacent($mage, $enemy));
    }

    public function testWordOfPainDamagesAll()
    {
        $battle = $this->createMock(Battle::class);
        $mage = $this->makeFighter('Mage', [0, 0, 0]);
        $enemy1 = $this->makeFighter('Enemy1', [1, 0, 0], 5);
        $enemy2 = $this->makeFighter('Enemy2', [2, 0, 0], 5);
        $battle->method('getFighters')->willReturn([$mage, $enemy1, $enemy2]);
        \Mordheim\Dice::setTestRolls([6, 6, 6, 6]);
        $result = Spell::WORD_OF_PAIN->getProcessor()->onPhaseMagic($battle, $mage);
        $this->assertTrue($result);
        $this->assertLessThan(5, $enemy1->getState()->getWounds());
        $this->assertLessThan(5, $enemy2->getState()->getWounds());
    }
} 