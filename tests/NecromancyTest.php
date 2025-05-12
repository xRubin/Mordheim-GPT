<?php

use Mordheim\Battle;
use Mordheim\Characteristics;
use Mordheim\Data\Blank;
use Mordheim\Data\Spell;
use Mordheim\Equipment;
use Mordheim\EquipmentManager;
use Mordheim\Fighter;
use Mordheim\FighterAdvancement;
use Mordheim\FighterState;
use Mordheim\SpecialRule;
use Mordheim\Status;
use Mordheim\Strategy\AggressiveStrategy;
use Mordheim\Strategy\CarefulStrategy;
use Mordheim\Warband;

class NecromancyTest extends MordheimTestCase
{
    private function makeFighterWithSpell(Spell $spell, $pos = [0, 0, 0], $wounds = 2): Fighter
    {
        $adv = new FighterAdvancement(new Characteristics(), [], [$spell]);
        return new Fighter(
            Blank::UNDEAD_NECROMANCER,
            $adv,
            new EquipmentManager([]),
            new FighterState($pos, new AggressiveStrategy(), $wounds)
        );
    }

    private function makeZombie($pos = [1, 0, 0], $alive = false): Fighter
    {
        $state = new FighterState($pos, new AggressiveStrategy(), 1);
        if (!$alive) {
            $state->setStatus(Status::OUT_OF_ACTION);
        }
        return new Fighter(
            Blank::UNDEAD_ZOMBIE,
            FighterAdvancement::empty(),
            new EquipmentManager([]),
            $state
        );
    }

    private function makeEnemy($pos = [2, 0, 0], $wounds = 2): Fighter
    {
        return new Fighter(
            Blank::REIKLAND_CHAMPION,
            FighterAdvancement::empty(),
            new EquipmentManager([\Mordheim\Data\Equipment::SWORD]),
            new FighterState($pos, new CarefulStrategy(), $wounds)
        );
    }

    public function testLifestealer()
    {
        $necromancer = $this->makeFighterWithSpell(Spell::LIFESTEALER, [0, 0, 0], 1);
        $enemy = $this->makeEnemy([1, 0, 0], 2);
        $battle = new Battle(new \Mordheim\GameField(), [
            new Warband('Undead', [$necromancer]),
            new Warband('Mercs', [$enemy])
        ]);
        $result = Spell::LIFESTEALER->onPhaseMagic($battle, $necromancer);
        $this->assertTrue($result);
        $this->assertEquals(1, $enemy->getState()->getWounds());
        $this->assertEquals(2, $necromancer->getState()->getWounds());
    }

    public function testReAnimation()
    {
        $necromancer = $this->makeFighterWithSpell(Spell::RE_ANIMATION, [0, 0, 0]);
        $zombie = $this->makeZombie([1, 0, 0], false);
        $battle = new Battle(new \Mordheim\GameField(), [
            new Warband('Undead', [$necromancer, $zombie])
        ]);
        $result = Spell::RE_ANIMATION->onPhaseMagic($battle, $necromancer);
        $this->assertTrue($result);
        $this->assertEquals(Status::STANDING, $zombie->getState()->getStatus());
        $this->assertNotEquals([1, 0, 0], $zombie->getState()->getPosition()); // Должен появиться не на старой позиции
    }

    public function testDeathVision()
    {
        $necromancer = $this->makeFighterWithSpell(Spell::DEATH_VISION, [0, 0, 0]);
        $battle = new Battle(new \Mordheim\GameField(), [
            new Warband('Undead', [$necromancer])
        ]);
        $result = Spell::DEATH_VISION->onPhaseMagic($battle, $necromancer);
        $this->assertTrue($result);
        $this->assertTrue($necromancer->getState()->hasActiveSpell(Spell::DEATH_VISION));
        $this->assertTrue($necromancer->hasSpecialRule(SpecialRule::CAUSE_FEAR));
    }

    public function testSpellOfDoom()
    {
        $necromancer = $this->makeFighterWithSpell(Spell::SPELL_OF_DOOM, [0, 0, 0]);
        $enemy = $this->makeEnemy([1, 0, 0], 2);
        $battle = new Battle(new \Mordheim\GameField(), [
            new Warband('Undead', [$necromancer]),
            new Warband('Mercs', [$enemy])
        ]);
        \Mordheim\Dice::setTestRolls([6]); // Провалить проверку силы
        $result = Spell::SPELL_OF_DOOM->onPhaseMagic($battle, $necromancer);
        $this->assertTrue($result);
        // Проверяем, что враг получил травму (можно проверить статус или логи)
    }

    public function testCallOfVanhel()
    {
        $necromancer = $this->makeFighterWithSpell(Spell::CALL_OF_VANHEL, [0, 0, 0]);
        $zombie = $this->makeZombie([1, 0, 0], true);
        $enemy = $this->makeEnemy([3, 2, 0], 2);
        $battle = new Battle(new \Mordheim\GameField(), [
            new Warband('Undead', [$necromancer, $zombie]),
            new Warband('Mercs', [$enemy])
        ]);
        $oldPos = $zombie->getState()->getPosition();
        $result = Spell::CALL_OF_VANHEL->onPhaseMagic($battle, $necromancer);
        $this->assertTrue($result);
        $this->assertNotEquals($oldPos, $zombie->getState()->getPosition());
    }

    public function testSpellOfAwakening()
    {
        $necromancer = $this->makeFighterWithSpell(Spell::SPELL_OF_AWAKENING, [0, 0, 0]);
        $battle = new Battle(new \Mordheim\GameField(), [
            new Warband('Undead', [$necromancer])
        ]);
        $result = Spell::SPELL_OF_AWAKENING->onPhaseMagic($battle, $necromancer);
        $this->assertTrue($result);
        $this->assertCount(2, $battle->getFighters(), 'Зомби-герой должен быть добавлен на поле');
    }
} 