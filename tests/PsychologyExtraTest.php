<?php

use Mordheim\Dice;
use Mordheim\Rule\Psychology;
use Mordheim\Status;
use PHPUnit\Framework\TestCase;

class PsychologyExtraTest extends TestCase
{
    public function setUp(): void
    {
        \Mordheim\Dice::setTestRolls([]);
        \Mordheim\BattleLogger::clear();
        \Mordheim\BattleLogger::add("### Test: {$this->name()}");
    }

    public function tearDown(): void
    {
        \Mordheim\Dice::setTestRolls([]);
        \Mordheim\BattleLogger::print();
    }

    private function makeFighter($ld, $state = Status::STANDING, $attacks = 1, $pos = [0, 0, 0])
    {
        return new class (
            \Mordheim\Data\Blank::MARIENBURG_YOUNGBLOOD,
            \Mordheim\FighterAdvancement::empty(),
            new \Mordheim\EquipmentManager([]),
            new \Mordheim\FighterState(
                $pos,
                new \Mordheim\Strategy\AggressiveStrategy(),
                4,
                $state,
            ),
            $attacks,
            $ld
        ) extends \Mordheim\Fighter {
            public function __construct(
                private readonly \Mordheim\BlankInterface       $blank,
                private readonly \Mordheim\AdvancementInterface $advancement,
                private readonly \Mordheim\EquipmentManager     $equipmentManager,
                private ?\Mordheim\FighterStateInterface        $fighterState = null,
                private int                                     $attacks,
                private int                                     $ld
            )
            {
                parent::__construct(
                    $blank,
                    $advancement,
                    $equipmentManager,
                    $fighterState,
                );
            }

            public function getAttacks(): int
            {
                return $this->attacks;
            }

            public function getLeadership(): int
            {
                return $this->ld;
            }
        };
    }

    public function testRoutTestLeaderAlive()
    {
        Dice::setTestRolls([4, 4]); // 8 <= 9 успех
        $leader = $this->makeFighter(9, Status::STANDING);
        $a = $this->makeFighter(6, Status::STANDING);
        $b = $this->makeFighter(6, Status::OUT_OF_ACTION);
        $warband = [$leader, $a, $b];
        $this->assertTrue(Psychology::routTest($warband, $leader));
    }

    public function testRoutTestLeaderDown()
    {
        Dice::setTestRolls([5, 4]); // 9 > 6 провал
        $leader = $this->makeFighter(9, Status::OUT_OF_ACTION);
        $alt = $this->makeFighter(6, Status::STANDING);
        $b = $this->makeFighter(6, Status::OUT_OF_ACTION);
        $warband = [$leader, $alt, $b];
        $this->assertFalse(Psychology::routTest($warband, $leader));
    }

    public function testAllAloneTestSuccess()
    {
        Dice::setTestRolls([4, 4]); // 8 <= 9 успех
        $hero = $this->makeFighter(9, Status::STANDING);
        $enemy1 = $this->makeFighter(6, Status::STANDING, 1, [1, 0, 0]);
        $enemy2 = $this->makeFighter(6, Status::STANDING, 1, [1, 1, 0]);
        $this->assertTrue(Psychology::allAloneTest($hero, [$enemy1, $enemy2], []));
    }

    public function testAllAloneTestFail()
    {
        Dice::setTestRolls([5, 4]); // 9 > 6 провал
        $hero = $this->makeFighter(6, Status::STANDING);
        $enemy1 = $this->makeFighter(6, Status::STANDING, 1, [1, 0, 0]);
        $enemy2 = $this->makeFighter(6, Status::STANDING, 1, [1, 1, 0]);
        $this->assertFalse(Psychology::allAloneTest($hero, [$enemy1, $enemy2], []));
    }

    public function testFearTest()
    {
        Dice::setTestRolls([4, 4]); // 8 <= 9 успех
        $hero = $this->makeFighter(9, Status::STANDING);
        $this->assertTrue(Psychology::fearTest($hero));
    }

    public function testFrenzyEffect()
    {
        $hero = $this->makeFighter(9, Status::STANDING, 2);
        $enemy = $this->makeFighter(6, Status::STANDING, 1, [6, 0, 0]);
        $result = Psychology::frenzyEffect($hero, [$enemy]);
        $this->assertTrue($result['mustCharge']);
        $this->assertEquals(4, $result['attacks']);
    }

    public function testHatredEffect()
    {
        $this->assertTrue(Psychology::hatredEffect(true));
        $this->assertFalse(Psychology::hatredEffect(false));
    }

    public function testStupidityTest()
    {
        Dice::setTestRolls([4, 4]); // 8 <= 9 успех
        $hero = $this->makeFighter(9, Status::STANDING);
        $this->assertTrue(Psychology::stupidityTest($hero));
        Dice::setTestRolls([5, 4]); // 9 > 6 провал
        $hero = $this->makeFighter(6, Status::STANDING);
        $this->assertFalse(Psychology::stupidityTest($hero));
    }
}
