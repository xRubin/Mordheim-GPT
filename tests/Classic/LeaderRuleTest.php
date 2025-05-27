<?php

namespace Classic;

use Mordheim\Classic\BattleStrategyInterface;
use Mordheim\Classic\EquipmentManager;
use Mordheim\Classic\Fighter;
use Mordheim\Classic\FighterState;
use Mordheim\Classic\Rule\Psychology;
use Mordheim\Classic\SpecialRule;
use Mordheim\Classic\Status;
use Mordheim\Dice;

class LeaderRuleTest extends \MordheimTestCase
{
    private function makeFighterMock($name, $ld, $pos, $hasLeaderRule, $status)
    {
        $fighter = $this->createMock(Fighter::class);
        $fighter->method('getName')->willReturn($name);
        $fighter->method('getLeadership')->willReturn($ld);
        $fighter->method('hasSpecialRule')->willReturnCallback(function ($rule) use ($hasLeaderRule) {
            return $hasLeaderRule && $rule === SpecialRule::LEADER;
        });
        $fighter->method('getEquipmentManager')->willReturn(new EquipmentManager());
        // getDistance: если передан массив, иначе 0
        $fighter->method('getState')->willReturn(
            new FighterState($pos, $this->createMock(BattleStrategyInterface::class), 1, $status)
        );
        return $fighter;
    }

    public function testLeaderBubbleApplied()
    {
        $captain = $this->makeFighterMock('Captain', 9, [0, 0, 0], true, Status::STANDING);
        $warrior = $this->makeFighterMock('Warrior', 6, [0, 5, 0], false, Status::STANDING);
        Dice::setTestRolls([4, 4]);
        $result = Psychology::leadershipTest($warrior, [$captain, $warrior]);
        $this->assertTrue($result, 'Warrior должен пройти тест с Ld капитана');
    }

    public function testLeaderBubbleNotAppliedIfFar()
    {
        $captain = $this->makeFighterMock('Captain', 9, [0, 0, 0], true, Status::STANDING);
        $warrior = $this->makeFighterMock('Warrior', 6, [0, 7, 0], false, Status::STANDING);
        Dice::setTestRolls([5, 4]);
        $result = Psychology::leadershipTest($warrior, [$captain, $warrior]);
        $this->assertFalse($result, 'Warrior не должен проходить тест вне 6"');
    }

    public function testLeaderBubbleOnlyIfHasLeaderRule()
    {
        $captain = $this->makeFighterMock('Champion', 9, [0, 0, 0], false, Status::STANDING);
        $warrior = $this->makeFighterMock('Warrior', 6, [0, 5, 0], false, Status::STANDING);
        Dice::setTestRolls([5, 4]);
        $result = Psychology::leadershipTest($warrior, [$captain, $warrior]);
        $this->assertFalse($result, 'Warrior не должен проходить тест без спецправила Leader');
    }

    public function testLeaderBubbleTakesHighestLd()
    {
        $captain = $this->makeFighterMock('Captain', 9, [0, 0, 0], true, Status::STANDING);
        $sergeant = $this->makeFighterMock('Sergeant', 8, [0, 4, 0], true, Status::STANDING);
        $warrior = $this->makeFighterMock('Warrior', 6, [0, 5, 0], false, Status::STANDING);
        Dice::setTestRolls([4, 4]);
        $result = Psychology::leadershipTest($warrior, [$captain, $sergeant, $warrior]);
        $this->assertTrue($result, 'Warrior должен использовать максимальный Ld из нескольких лидеров');
    }

    public function testDeadLeaderNotApplied()
    {
        $captain = $this->makeFighterMock('Captain', 9, [0, 0, 0], true, Status::OUT_OF_ACTION);
        $warrior = $this->makeFighterMock('Warrior', 6, [0, 5, 0], false, Status::STANDING);
        Dice::setTestRolls([5, 4]);
        $result = Psychology::leadershipTest($warrior, [$captain, $warrior]);
        $this->assertFalse($result, 'Мёртвый лидер не должен давать Ld бонус');
    }

    public function testLeaderWithSameLdNotApplied()
    {
        $captain = $this->makeFighterMock('Captain', 6, [0, 0, 0], true, Status::STANDING);
        $warrior = $this->makeFighterMock('Warrior', 6, [0, 5, 0], false, Status::STANDING);
        Dice::setTestRolls([5, 4]);
        $result = Psychology::leadershipTest($warrior, [$captain, $warrior]);
        $this->assertFalse($result, 'Лидер с таким же Ld не должен давать бонус');
    }

    public function testPanickedLeaderNotApplied()
    {
        $captain = $this->makeFighterMock('Captain', 9, [0, 0, 0], true, Status::PANIC);
        $warrior = $this->makeFighterMock('Warrior', 6, [0, 5, 0], false, Status::STANDING);
        Dice::setTestRolls([5, 4]);
        $result = Psychology::leadershipTest($warrior, [$captain, $warrior]);
        $this->assertFalse($result, 'Лидер в панике не должен давать Ld бонус');
    }
}
