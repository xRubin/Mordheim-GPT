<?php

use Mordheim\Characteristics;
use Mordheim\Data\Skills;
use Mordheim\EquipmentManager;
use Mordheim\Fighter;
use Mordheim\FighterState;
use Mordheim\Rule\Psychology;
use Mordheim\Strategy\AggressiveStrategy;
use PHPUnit\Framework\TestCase;

class LeaderRuleTest extends TestCase
{
    protected function setUp(): void
    {
        \Mordheim\Dice::setTestRolls([]);
    }
    protected function tearDown(): void
    {
        \Mordheim\Dice::setTestRolls([]);
    }

    private function makeLeader($name, $ws, $ld, $pos = [0,0,0]) {
        $char = new Characteristics(
            4,      // movement
            $ws,    // weaponSkill
            3,      // ballisticSkill
            3,      // strength
            3,      // toughness
            2,      // wounds
            4,      // initiative
            1,      // attacks
            $ld     // leadership
        );
        return new Fighter($name, $char, [Skills::getByName('Leader')], new EquipmentManager([]), new AggressiveStrategy(), $pos, FighterState::STANDING, 0);
    }
    private function makeFighter($name, $ws, $ld, $pos = [0,0,0]) {
        $char = new Characteristics(
            4,      // movement
            $ws,    // weaponSkill
            3,      // ballisticSkill
            3,      // strength
            3,      // toughness
            2,      // wounds
            4,      // initiative
            1,      // attacks
            $ld     // leadership
        );
        return new Fighter($name, $char, [], new EquipmentManager([]), new AggressiveStrategy(), $pos, FighterState::STANDING, 0);
    }

    public function testLeaderBubbleApplied() {
        $captain = $this->makeLeader('Captain', 4, 9, [0,0,0]);
        $warrior = $this->makeFighter('Warrior', 3, 6, [0,5,0]);
        // В пределах 6". Должен использовать Ld капитана (9), а не свой (6)
        // Мокаем броски для успеха: 4+4=8 (успех для Ld 9)
        \Mordheim\Dice::setTestRolls([4,4]);
        $result = Psychology::leadershipTest($warrior, [$captain, $warrior]);
        $this->assertTrue($result, 'Warrior должен пройти тест с Ld капитана');
    }

    public function testLeaderBubbleNotAppliedIfFar() {
        $captain = $this->makeLeader('Captain', 4, 9, [0,0,0]);
        $warrior = $this->makeFighter('Warrior', 3, 6, [0,7,0]); // 7" — вне пузыря
        // Мокаем броски для провала: 5+4=9 (провал для Ld 6)
        \Mordheim\Dice::setTestRolls([5,4]);
        $result = Psychology::leadershipTest($warrior, [$captain, $warrior]);
        $this->assertFalse($result, 'Warrior не должен проходить тест вне 6"');
    }

    public function testLeaderBubbleOnlyIfHasLeaderRule() {
        $captain = $this->makeFighter('Captain', 4, 9, [0,0,0]); // Без Leader
        $warrior = $this->makeFighter('Warrior', 3, 6, [0,5,0]);
        // Мокаем броски для провала: 5+4=9 (провал для Ld 6)
        \Mordheim\Dice::setTestRolls([5,4]);
        $result = Psychology::leadershipTest($warrior, [$captain, $warrior]);
        $this->assertFalse($result, 'Warrior не должен проходить тест без спецправила Leader');
    }

    public function testLeaderBubbleTakesHighestLd() {
        $captain = $this->makeLeader('Captain', 4, 8, [0,0,0]);
        $sergeant = $this->makeLeader('Sergeant', 4, 9, [0,4,0]);
        $warrior = $this->makeFighter('Warrior', 3, 6, [0,5,0]);
        // Два лидера в пузыре, должен взять максимальный Ld (9)
        // Мокаем броски для успеха: 4+4=8 (успех для Ld 9)
        \Mordheim\Dice::setTestRolls([4,4]);
        $result = Psychology::leadershipTest($warrior, [$captain, $sergeant, $warrior]);
        $this->assertTrue($result, 'Warrior должен использовать максимальный Ld из нескольких лидеров');
    }

    public function testDeadLeaderNotApplied() {
        $captain = $this->makeLeader('Captain', 4, 9, [0,0,0]);
        $captain->alive = false;
        $warrior = $this->makeFighter('Warrior', 3, 6, [0,5,0]);
        // Мокаем броски для провала: 5+4=9 (провал для Ld 6)
        \Mordheim\Dice::setTestRolls([5,4]);
        $result = Psychology::leadershipTest($warrior, [$captain, $warrior]);
        $this->assertFalse($result, 'Мёртвый лидер не должен давать Ld бонус');
    }

    public function testLeaderWithSameLdNotApplied() {
        $captain = $this->makeLeader('Captain', 4, 6, [0,0,0]);
        $warrior = $this->makeFighter('Warrior', 3, 6, [0,5,0]);
        // Мокаем броски для провала: 5+4=9 (провал для Ld 6)
        \Mordheim\Dice::setTestRolls([5,4]);
        $result = Psychology::leadershipTest($warrior, [$captain, $warrior]);
        $this->assertFalse($result, 'Лидер с таким же Ld не должен давать бонус');
    }

    public function testPanickedLeaderNotApplied() {
        $captain = $this->makeLeader('Captain', 4, 9, [0,0,0]);
        $captain->state = FighterState::PANIC;
        $warrior = $this->makeFighter('Warrior', 3, 6, [0,5,0]);
        // Мокаем броски для провала: 5+4=9 (провал для Ld 6)
        \Mordheim\Dice::setTestRolls([5,4]);
        $result = Psychology::leadershipTest($warrior, [$captain, $warrior]);
        $this->assertFalse($result, 'Лидер в панике не должен давать Ld бонус');
    }
}
