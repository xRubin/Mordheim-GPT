<?php

use Mordheim\BattleStrategyInterface;
use Mordheim\EquipmentManager;
use Mordheim\Rule\Psychology;

class LeaderRuleTest extends MordheimTestCase
{
    private function makeFighterMock($name, $ld, $pos, $hasLeaderRule, $status, $distanceMap = [])
    {
        $fighter = $this->createMock(\Mordheim\FighterInterface::class);
        $fighter->method('getName')->willReturn($name);
        $fighter->method('getLeadership')->willReturn($ld);
        $fighter->method('hasSpecialRule')->willReturnCallback(function ($rule) use ($hasLeaderRule) {
            return $hasLeaderRule && $rule === \Mordheim\SpecialRule::LEADER;
        });
        $fighter->method('getEquipmentManager')->willReturn(new EquipmentManager());
        // getDistance: если передан массив, иначе 0
        $fighter->method('getDistance')->willReturnCallback(function ($target) use ($distanceMap, $name) {
            return $distanceMap[$target->getName()] ?? 0;
        });
        $fighter->method('getState')->willReturn(
            new \Mordheim\FighterState($pos, $this->createMock(BattleStrategyInterface::class), 1, $status)
        );
        return $fighter;
    }

    public function testLeaderBubbleApplied()
    {
        $captain = $this->makeFighterMock('Captain', 9, [0, 0, 0], true, \Mordheim\Status::STANDING, ['Warrior' => 5]);
        $warrior = $this->makeFighterMock('Warrior', 6, [0, 5, 0], false, \Mordheim\Status::STANDING, ['Captain' => 5, 'Warrior' => 0]);
        \Mordheim\Dice::setTestRolls([4, 4]);
        $result = Psychology::leadershipTest($warrior, [$captain, $warrior]);
        $this->assertTrue($result, 'Warrior должен пройти тест с Ld капитана');
    }

    public function testLeaderBubbleNotAppliedIfFar()
    {
        $captain = $this->makeFighterMock('Captain', 9, [0, 0, 0], true, \Mordheim\Status::STANDING, ['Warrior' => 7]);
        $warrior = $this->makeFighterMock('Warrior', 6, [0, 7, 0], false, \Mordheim\Status::STANDING, ['Captain' => 7, 'Warrior' => 0]);
        \Mordheim\Dice::setTestRolls([5, 4]);
        $result = Psychology::leadershipTest($warrior, [$captain, $warrior]);
        $this->assertFalse($result, 'Warrior не должен проходить тест вне 6"');
    }

    public function testLeaderBubbleOnlyIfHasLeaderRule()
    {
        $captain = $this->makeFighterMock('Champion', 9, [0, 0, 0], false, \Mordheim\Status::STANDING, ['Warrior' => 5]);
        $warrior = $this->makeFighterMock('Warrior', 6, [0, 5, 0], false, \Mordheim\Status::STANDING, ['Champion' => 5, 'Warrior' => 0]);
        \Mordheim\Dice::setTestRolls([5, 4]);
        $result = Psychology::leadershipTest($warrior, [$captain, $warrior]);
        $this->assertFalse($result, 'Warrior не должен проходить тест без спецправила Leader');
    }

    public function testLeaderBubbleTakesHighestLd()
    {
        $captain = $this->makeFighterMock('Captain', 9, [0, 0, 0], true, \Mordheim\Status::STANDING, ['Sergeant' => 4, 'Warrior' => 5]);
        $sergeant = $this->makeFighterMock('Sergeant', 8, [0, 4, 0], true, \Mordheim\Status::STANDING, ['Captain' => 4, 'Warrior' => 1]);
        $warrior = $this->makeFighterMock('Warrior', 6, [0, 5, 0], false, \Mordheim\Status::STANDING, ['Captain' => 5, 'Sergeant' => 1, 'Warrior' => 0]);
        \Mordheim\Dice::setTestRolls([4, 4]);
        $result = Psychology::leadershipTest($warrior, [$captain, $sergeant, $warrior]);
        $this->assertTrue($result, 'Warrior должен использовать максимальный Ld из нескольких лидеров');
    }

    public function testDeadLeaderNotApplied()
    {
        $captain = $this->makeFighterMock('Captain', 9, [0, 0, 0], true, \Mordheim\Status::OUT_OF_ACTION, ['Warrior' => 5]);
        $warrior = $this->makeFighterMock('Warrior', 6, [0, 5, 0], false, \Mordheim\Status::STANDING, ['Captain' => 5, 'Warrior' => 0]);
        \Mordheim\Dice::setTestRolls([5, 4]);
        $result = Psychology::leadershipTest($warrior, [$captain, $warrior]);
        $this->assertFalse($result, 'Мёртвый лидер не должен давать Ld бонус');
    }

    public function testLeaderWithSameLdNotApplied()
    {
        $captain = $this->makeFighterMock('Captain', 6, [0, 0, 0], true, \Mordheim\Status::STANDING, ['Warrior' => 5]);
        $warrior = $this->makeFighterMock('Warrior', 6, [0, 5, 0], false, \Mordheim\Status::STANDING, ['Captain' => 5, 'Warrior' => 0]);
        \Mordheim\Dice::setTestRolls([5, 4]);
        $result = Psychology::leadershipTest($warrior, [$captain, $warrior]);
        $this->assertFalse($result, 'Лидер с таким же Ld не должен давать бонус');
    }

    public function testPanickedLeaderNotApplied()
    {
        $captain = $this->makeFighterMock('Captain', 9, [0, 0, 0], true, \Mordheim\Status::PANIC, ['Warrior' => 5]);
        $warrior = $this->makeFighterMock('Warrior', 6, [0, 5, 0], false, \Mordheim\Status::STANDING, ['Captain' => 5, 'Warrior' => 0]);
        \Mordheim\Dice::setTestRolls([5, 4]);
        $result = Psychology::leadershipTest($warrior, [$captain, $warrior]);
        $this->assertFalse($result, 'Лидер в панике не должен давать Ld бонус');
    }
}
