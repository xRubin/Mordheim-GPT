<?php
use PHPUnit\Framework\TestCase;
use Mordheim\MordheimGame;
use Mordheim\GameField;
use Mordheim\Data\Warbands;

class MordheimGameTest extends TestCase
{
    public function testGameInitialization()
    {
        $field = new GameField();
        $warbands = Warbands::getAll();
        $game = new MordheimGame($field, $warbands);
        $this->assertInstanceOf(MordheimGame::class, $game);
        $this->assertCount(2, $game->getWarbands()); // Если добавлено 2 банды
        $this->assertInstanceOf(GameField::class, $game->getField());
    }

    public function testNextTurn()
    {
        $game = new MordheimGame(new GameField(), Warbands::getAll());
        $this->assertEquals(0, $game->getCurrentTurn());
        $game->nextTurn();
        $this->assertEquals(1, $game->getCurrentTurn());
    }
}
