<?php
use PHPUnit\Framework\TestCase;
use Mordheim\GameField;
use Mordheim\FieldCell;
use Mordheim\PathFinder;

class PathFinderTest extends TestCase
{
    public function testStraightPathNoObstacles()
    {
        $field = new GameField();
        $start = [0,0,0];
        $goal = [3,0,0];
        $path = PathFinder::findPath($field, $start, $goal);
        $this->assertNotNull($path);
        $this->assertEquals($goal, end($path)['pos']);
    }

    public function testWallObstacle()
    {
        $field = new GameField();
        // Стена по y=1, x=0..2
        for ($x=0; $x<=2; $x++) {
            $cell = new FieldCell();
            $cell->obstacle = true;
            $field->setCell($x,1,0,$cell);
        }
        $start = [0,0,0];
        $goal = [2,2,0];
        $path = PathFinder::findPath($field, $start, $goal);
        $this->assertNotNull($path);
        // Должен обойти стену по диагонали
        $this->assertEquals($goal, end($path)['pos']);
    }

    public function testLShapedObstacle()
    {
        $field = new GameField();
        // L-образное препятствие
        for ($x=1; $x<=3; $x++) {
            $cell = new FieldCell(); $cell->obstacle = true;
            $field->setCell($x,1,0,$cell);
        }
        for ($y=2; $y<=3; $y++) {
            $cell = new FieldCell(); $cell->obstacle = true;
            $field->setCell(3,$y,0,$cell);
        }
        $start = [0,0,0];
        $goal = [4,4,0];
        $path = PathFinder::findPath($field, $start, $goal);
        $this->assertNotNull($path);
        $this->assertEquals($goal, end($path)['pos']);
    }

    public function testNoPath()
    {
        $field = new GameField();
        // Перекрываем всё
        for ($x=0; $x<=2; $x++) {
            for ($y=0; $y<=2; $y++) {
                $cell = new FieldCell(); $cell->obstacle = true;
                $field->setCell($x,$y,0,$cell);
            }
        }
        $start = [0,0,0];
        $goal = [2,2,0];
        $path = PathFinder::findPath($field, $start, $goal);
        $this->assertNull($path);
    }

    public function testLadderAndFloors()
    {
        $field = new GameField();
        // Без лестницы нельзя на 1 этаж
        $start = [0,0,0];
        $goal = [0,0,1];
        $path = PathFinder::findPath($field, $start, $goal);
        $this->assertNull($path);
        // Ставим лестницу
        $cell = $field->getCell(0,0,0);
        $cell->ladder = true;
        $field->setCell(0,0,0,$cell);
        $path = PathFinder::findPath($field, $start, $goal);
        $this->assertNotNull($path);
        $this->assertEquals($goal, end($path)['pos']);
    }

    public function testMaxCostPath()
    {
        $field = new GameField();
        $start = [0,0,0];
        $goal = [2,0,0]; // путь по горизонтали, 2 клетки, вес 2.0
        // Без ограничения по стоимости — путь есть
        $path = PathFinder::findPath($field, $start, $goal);
        $this->assertNotNull($path);
        $this->assertEquals($goal, end($path)['pos']);
        // Проверим стоимость итогового пути
        $this->assertEquals(2.0, end($path)['cost']);
        // Проверим диагональ (вес 1.4)
        $goalDiag = [1,1,0];
        $path = PathFinder::findPath($field, $start, $goalDiag);
        $this->assertNotNull($path);
        $this->assertEquals($goalDiag, end($path)['pos']);
        $this->assertEquals(1.4, end($path)['cost']);
    }
}
