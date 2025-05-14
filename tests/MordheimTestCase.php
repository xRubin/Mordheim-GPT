<?php

use PHPUnit\Framework\TestCase;

abstract class MordheimTestCase extends TestCase
{
    protected function setUp(): void
    {
        \Mordheim\Dice::setTestRolls([]);
        \Mordheim\BattleLogger::clear();
        if (method_exists($this, 'name')) {
            \Mordheim\BattleLogger::add("### Test: {$this->name()}");
        }
    }

    protected function tearDown(): void
    {
        \Mordheim\Dice::setTestRolls([]);
        if (!$this->status()->isSuccess())
            \Mordheim\BattleLogger::print();
    }
}