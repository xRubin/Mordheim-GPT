<?php

use Mordheim\Characteristics;
use Mordheim\EquipmentManager;
use Mordheim\Rule\Psychology;
use Mordheim\Status;
use Mordheim\Strategy\BattleStrategyInterface;
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

    private function makeLeader($pos = [0, 0, 0])
    {
        return new \Mordheim\Fighter(
            \Mordheim\Data\Blank::MARIENBURG_MERCENARY_CAPTAIN,
            \Mordheim\FighterAdvancement::empty(),
            new EquipmentManager(),
            new \Mordheim\FighterState(
                $pos,
                $this->createMock(BattleStrategyInterface::class),
                2
            )
        );
    }

    private function makeFighter($ws, $ld, $pos = [0, 0, 0])
    {
        return new \Mordheim\Fighter(
            \Mordheim\Data\Blank::MARIENBURG_MERCENARY_CAPTAIN,
            new \Mordheim\FighterAdvancement(
                new Characteristics(0, $ws - 2, 0, 0, 0, 0, 0, 0, $ld - 6)
            ),
            new EquipmentManager(),
            new \Mordheim\FighterState(
                $pos,
                $this->createMock(BattleStrategyInterface::class),
                2
            )
        );
    }

    public function testLeaderBubbleApplied()
    {
        $captain = $this->makeLeader([0, 0, 0]);
        $warrior = $this->makeFighter(3, 6, [0, 5, 0]);
        // В пределах 6". Должен использовать Ld капитана (9), а не свой (6)
        // Мокаем броски для успеха: 4+4=8 (успех для Ld 9)
        \Mordheim\Dice::setTestRolls([4, 4]);
        $result = Psychology::leadershipTest($warrior, [$captain, $warrior]);
        $this->assertTrue($result, 'Warrior должен пройти тест с Ld капитана');
    }

    public function testLeaderBubbleNotAppliedIfFar()
    {
        $captain = $this->makeLeader([0, 0, 0]);
        $warrior = $this->makeFighter(3, 6, [0, 7, 0]); // 7" — вне пузыря
        // Мокаем броски для провала: 5+4=9 (провал для Ld 6)
        \Mordheim\Dice::setTestRolls([5, 4]);
        $result = Psychology::leadershipTest($warrior, [$captain, $warrior]);
        $this->assertFalse($result, 'Warrior не должен проходить тест вне 6"');
    }

    public function testLeaderBubbleOnlyIfHasLeaderRule()
    {
        $captain = new \Mordheim\Fighter(
            \Mordheim\Data\Blank::MARIENBURG_CHAMPION, // Champion не имеет Leader
            \Mordheim\FighterAdvancement::empty(),
            new EquipmentManager(),
            new \Mordheim\FighterState(
                [0, 0, 0],
                $this->createMock(BattleStrategyInterface::class),
                2
            )
        );
        $warrior = $this->makeFighter(3, 6, [0, 5, 0]);
        // Мокаем броски для провала: 5+4=9 (провал для Ld 6)
        \Mordheim\Dice::setTestRolls([5, 4]);
        $result = Psychology::leadershipTest($warrior, [$captain, $warrior]);
        $this->assertFalse($result, 'Warrior не должен проходить тест без спецправила Leader');
    }

    public function testLeaderBubbleTakesHighestLd()
    {
        $captain = $this->makeLeader([0, 0, 0]);
        $sergeant = $this->makeLeader([0, 4, 0]);
        $warrior = $this->makeFighter(3, 6, [0, 5, 0]);
        // Два лидера в пузыре, должен взять максимальный Ld (9)
        // Мокаем броски для успеха: 4+4=8 (успех для Ld 9)
        \Mordheim\Dice::setTestRolls([4, 4]);
        $result = Psychology::leadershipTest($warrior, [$captain, $sergeant, $warrior]);
        $this->assertTrue($result, 'Warrior должен использовать максимальный Ld из нескольких лидеров');
    }

    public function testDeadLeaderNotApplied()
    {
        $captain = $this->makeLeader([0, 0, 0]);
        $captain->getState()->setStatus(Status::OUT_OF_ACTION);
        $warrior = $this->makeFighter(3, 6, [0, 5, 0]);
        // Мокаем броски для провала: 5+4=9 (провал для Ld 6)
        \Mordheim\Dice::setTestRolls([5, 4]);
        $result = Psychology::leadershipTest($warrior, [$captain, $warrior]);
        $this->assertFalse($result, 'Мёртвый лидер не должен давать Ld бонус');
    }

    public function testLeaderWithSameLdNotApplied()
    {
        $captain = $this->makeLeader([0, 0, 0]);
        $warrior = $this->makeFighter(3, 6, [0, 5, 0]);
        // Мокаем броски для провала: 5+4=9 (провал для Ld 6)
        \Mordheim\Dice::setTestRolls([5, 4]);
        $result = Psychology::leadershipTest($warrior, [$captain, $warrior]);
        $this->assertFalse($result, 'Лидер с таким же Ld не должен давать бонус');
    }

    public function testPanickedLeaderNotApplied()
    {
        $captain = $this->makeLeader([0, 0, 0]);
        $captain->getState()->setStatus(Status::PANIC);
        $warrior = $this->makeFighter(3, 6, [0, 5, 0]);
        // Мокаем броски для провала: 5+4=9 (провал для Ld 6)
        \Mordheim\Dice::setTestRolls([5, 4]);
        $result = Psychology::leadershipTest($warrior, [$captain, $warrior]);
        $this->assertFalse($result, 'Лидер в панике не должен давать Ld бонус');
    }
}
