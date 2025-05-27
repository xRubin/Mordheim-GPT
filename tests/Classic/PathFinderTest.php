<?php

namespace Classic;

use Mordheim\Classic\PathFinder;
use Mordheim\GameFieldCell;
use Mordheim\GameField;

class PathFinderTest extends \MordheimTestCase
{
    private function getMovementWeights(): callable
    {
        return function (GameFieldCell $from, GameFieldCell $to, $dx, $dy, $dz) {
            if ($dz !== 0) return 2.0;
            if ($dx !== 0 && $dy !== 0) return 1.4;
            return 1.0;
        };
    }

    public function testStraightPathNoObstacles()
    {
        $field = new GameField();
        $start = [0, 0, 0];
        $goal = [3, 0, 0];
        $path = PathFinder::findPath($field, $start, $goal, $this->getMovementWeights(), 0.6);
        $this->assertNotNull($path);
        $this->assertEquals($goal, end($path)['pos']);
    }

    public function testWallObstacle()
    {
        $field = new GameField();
        // Стена по y=1, x=0..2
        for ($x = 0; $x <= 2; $x++) {
            $field->setCell($x, 1, 0, new GameFieldCell(0, obstacle: true));
        }
        $start = [0, 0, 0];
        $goal = [2, 2, 0];
        $path = PathFinder::findPath($field, $start, $goal, $this->getMovementWeights(), 0.6);
        $this->assertNotNull($path);
        // Должен обойти стену по диагонали
        $this->assertEquals($goal, end($path)['pos']);
    }

    public function testLShapedObstacle()
    {
        $field = new GameField();
        // L-образное препятствие
        for ($x = 1; $x <= 3; $x++) {
            $field->setCell($x, 1, 0, new GameFieldCell(0, obstacle: true));
        }
        for ($y = 2; $y <= 3; $y++) {
            $field->setCell(3, $y, 0, new GameFieldCell(0, obstacle: true));
        }
        $start = [0, 0, 0];
        $goal = [4, 4, 0];
        $path = PathFinder::findPath($field, $start, $goal, $this->getMovementWeights(), 0.6);
        $this->assertNotNull($path);
        $this->assertEquals($goal, end($path)['pos']);
    }

    public function testNoPath()
    {
        $field = new GameField();
        // Перекрываем всё
        for ($x = 0; $x <= 2; $x++) {
            for ($y = 0; $y <= 2; $y++) {
                $field->setCell($x, $y, 0, new GameFieldCell(0, obstacle: true));
            }
        }
        $start = [0, 0, 0];
        $goal = [2, 2, 0];
        $path = PathFinder::findPath($field, $start, $goal, $this->getMovementWeights(), 1.0);
        $this->assertNull($path);
    }

    public function testLadderAndFloors()
    {
        $field = new GameField();
        // Без лестницы нельзя на 1 этаж
        $start = [0, 0, 0];
        $goal = [0, 0, 1];
        $path = PathFinder::findPath($field, $start, $goal, $this->getMovementWeights(), 1.0);
        $this->assertNull($path);
        // Ставим лестницу
        $cell = $field->getCell(0, 0, 0);
        $cell->ladder = true;
        $field->setCell(0, 0, 0, $cell);
        $path = PathFinder::findPath($field, $start, $goal, $this->getMovementWeights(), 0.8);
        $this->assertNotNull($path);
        $this->assertEquals($goal, end($path)['pos']);
    }

    public function testMaxCostPath()
    {
        $field = new GameField();
        $start = [0, 0, 0];
        $goal = [2, 0, 0]; // путь по горизонтали, 2 клетки, вес 2.0
        // Без ограничения по стоимости — путь есть
        $path = PathFinder::findPath($field, $start, $goal, $this->getMovementWeights(), 0.6);
        $this->assertNotNull($path);
        $this->assertEquals($goal, end($path)['pos']);
        // Проверим стоимость итогового пути
        $this->assertEquals(2.0, end($path)['cost']);
        // Проверим диагональ (вес 1.4)
        $goalDiag = [1, 1, 0];
        $path = PathFinder::findPath($field, $start, $goalDiag, $this->getMovementWeights(), 0.6);
        $this->assertNotNull($path);
        $this->assertEquals($goalDiag, end($path)['pos']);
        $this->assertEquals(1.4, end($path)['cost']);
    }

    // --- Лазание ---
    public function testMordheimClimbingImpossibleWithoutObstacleBelowTarget()
    {
        $field = new GameField();
        $start = [0, 0, 0];
        $goal = [1, 0, 1];
        // Стены вокруг стартовой позиции (только валидные координаты)
        $field->setCell(0, 1, 0, new GameFieldCell(0, obstacle: true));
        $field->setCell(1, 1, 0, new GameFieldCell(0, obstacle: true));
        // Стены вокруг целевой позиции (только валидные координаты)
        $field->setCell(1, 1, 1, new GameFieldCell(1, obstacle: true));
        $field->setCell(0, 0, 1, new GameFieldCell(1, obstacle: true));
        $field->setCell(2, 0, 1, new GameFieldCell(1, obstacle: true));
        $field->setCell(2, 1, 1, new GameFieldCell(1, obstacle: true));
        $field->setCell(0, 1, 1, new GameFieldCell(1, obstacle: true));
        // Явно выставляем obstacle=false под целевой ячейкой
        $field->setCell(1, 0, 0, new GameFieldCell(0));
        $path = PathFinder::findPath($field, $start, $goal, $this->getMovementWeights(), 1.0);
        $this->assertNull($path);
    }

    public function testMordheimClimbingPossibleWithObstacleBelowTarget()
    {
        $field = new GameField();
        $start = [0, 0, 0];
        $goal = [1, 0, 1];
        $field->setCell(1, 0, 0, new GameFieldCell(0, obstacle: true)); // Стена
        $field->setCell(0, 1, 0, new GameFieldCell(0, obstacle: true));
        $field->setCell(1, 1, 0, new GameFieldCell(0, obstacle: true));
        $field->setCell(0, 0, 1, new GameFieldCell(1, obstacle: true));
        $path = PathFinder::findPath($field, $start, $goal, $this->getMovementWeights(), 0.95);
        $this->assertNotNull($path);
        $this->assertEquals($goal, end($path)['pos']);
    }

    public function testMordheimClimbingImpossibleWithoutWall()
    {
        $field = new GameField();
        $start = [0, 0, 0];
        $goal = [1, 0, 1];
        $field->setCell(0, 1, 0, new GameFieldCell(0, obstacle: true));
        $field->setCell(1, 1, 0, new GameFieldCell(0, obstacle: true));
        // Стены вокруг целевой позиции
        $field->setCell(1, 1, 1, new GameFieldCell(1, obstacle: true));
        $field->setCell(0, 0, 1, new GameFieldCell(1, obstacle: true));
        $field->setCell(2, 0, 1, new GameFieldCell(1, obstacle: true));
        $field->setCell(2, 1, 1, new GameFieldCell(1, obstacle: true));
        $field->setCell(0, 1, 1, new GameFieldCell(1, obstacle: true));
        // Явно выставляем obstacle=false под целевой ячейкой и в целевой
        $field->setCell(1, 0, 0, new GameFieldCell(0));
        $field->setCell(1, 0, 1, new GameFieldCell(1));
        $path = PathFinder::findPath($field, $start, $goal, $this->getMovementWeights(), 0.95);
        $this->assertNull($path);
    }

    public function testMordheimClimbingImpossibleIfTargetHasObstacle()
    {
        $field = new GameField();
        $start = [0, 0, 0];
        $goal = [1, 0, 1];
        $field->setCell(1, 0, 1, new GameFieldCell(1, obstacle: true)); // obstacle в целевой ячейке
        $field->setCell(1, 0, 0, new GameFieldCell(0, obstacle: true));
        $field->setCell(0, 1, 0, new GameFieldCell(0, obstacle: true));
        $field->setCell(1, 1, 0, new GameFieldCell(0, obstacle: true));
        $path = PathFinder::findPath($field, $start, $goal, $this->getMovementWeights(), 0.95);
        $this->assertNull($path);
    }

    // --- Прыжки через пустоту ---
    public function testMordheimJumpingOverGapsImpossibleWithoutObstacleBelowStart()
    {
        $field = new GameField();
        $start = [0, 0, 2];
        $goal = [2, 0, 2];
        $field->setCell(2, 0, 1, new GameFieldCell(1, obstacle: true)); // Крыша под целью
        $field->setCell(0, 1, 2, new GameFieldCell(2, obstacle: true));
        $field->setCell(1, 0, 2, new GameFieldCell(2, obstacle: true));
        $field->setCell(1, 1, 2, new GameFieldCell(2, obstacle: true));
        $field->setCell(2, 1, 2, new GameFieldCell(2, obstacle: true));
        // Явно выставляем obstacle=false под стартом
        $field->setCell(0, 0, 1, new GameFieldCell(1));
        $path = PathFinder::findPath($field, $start, $goal, $this->getMovementWeights(), 0.95);
        $this->assertNull($path);
    }

    public function testMordheimJumpingOverGapsPossibleWithObstacleBelowStart()
    {
        $field = new GameField();
        $start = [0, 0, 2];
        $goal = [2, 0, 2];
        $field->setCell(0, 0, 1, new GameFieldCell(1, obstacle: true)); // obstacle под стартом
        $field->setCell(2, 0, 1, new GameFieldCell(1, obstacle: true)); // obstacle под целью
        // Между крышами — пустота
        $field->setCell(1, 0, 2, new GameFieldCell(2));
        // В стартовой и целевой ячейках препятствий нет
        $field->setCell(0, 0, 2, new GameFieldCell(2));
        $field->setCell(2, 0, 2, new GameFieldCell(2));
        $path = PathFinder::findPath($field, $start, $goal, $this->getMovementWeights(), 0.95);
        $this->assertNotNull($path);
        $this->assertEquals($goal, end($path)['pos']);
    }

    public function testMordheimJumpingOverGapsImpossibleIfObstacleBetweenRoofs()
    {
        $field = new GameField();
        $start = [0, 0, 2];
        $goal = [2, 0, 2];
        $field->setCell(2, 0, 1, new GameFieldCell(1, obstacle: true)); // Крыша под целью
        $field->setCell(0, 1, 2, new GameFieldCell(2, obstacle: true));
        $field->setCell(1, 0, 2, new GameFieldCell(2, obstacle: true));
        $field->setCell(1, 1, 2, new GameFieldCell(2, obstacle: true));
        $field->setCell(2, 1, 2, new GameFieldCell(2, obstacle: true));
        $field->setCell(0, 0, 1, new GameFieldCell(1, obstacle: true)); // obstacle под стартом
        $field->setCell(1, 0, 2, new GameFieldCell(2, obstacle: true)); // obstacle между крышами
        $path = PathFinder::findPath($field, $start, $goal, $this->getMovementWeights(), 0.95);
        $this->assertNull($path);
    }

    public function testMordheimJumpingOverGapsWithHeight()
    {
        $field = new GameField();
        $start = [0, 0, 2];
        $goal = [2, 0, 3];
        $field->setCell(0, 0, 1, new GameFieldCell(1, obstacle: true)); // obstacle под стартом
        $field->setCell(2, 0, 2, new GameFieldCell(2, obstacle: true)); // obstacle под целью
        // Между крышами — пустота
        $field->setCell(1, 0, 2, new GameFieldCell(2));
        $field->setCell(2, 0, 3, new GameFieldCell(3)); // целевая ячейка без obstacle
        // Прыжок невозможен при любой агрессивности
        $path = PathFinder::findPath($field, $start, $goal, $this->getMovementWeights(), 0.5);
        $this->assertNull($path);
        $path = PathFinder::findPath($field, $start, $goal, $this->getMovementWeights(), 0.95);
        $this->assertNull($path);
        // Прыжок невозможен, если между крышами не пусто
        $field->setCell(1, 0, 3, new GameFieldCell(3, obstacle: true));
        $path = PathFinder::findPath($field, $start, $goal, $this->getMovementWeights(), 0.95);
        $this->assertNull($path);
    }

    public function testMordheimCombinedRules()
    {
        $field = new GameField();
        $start = [0, 0, 2];
        $goal = [2, 0, 3];
        $field->setCell(0, 0, 1, new GameFieldCell(1, obstacle: true)); // obstacle под стартом
        $field->setCell(2, 0, 2, new GameFieldCell(2, obstacle: true)); // obstacle под целью
        // Между крышами — пустота
        $field->setCell(1, 0, 2, new GameFieldCell(2));
        $field->setCell(2, 0, 3, new GameFieldCell(3)); // целевая ячейка без obstacle
        // Прыжок невозможен при любой агрессивности
        $path = PathFinder::findPath($field, $start, $goal, $this->getMovementWeights(), 0.5);
        $this->assertNull($path);
        $path = PathFinder::findPath($field, $start, $goal, $this->getMovementWeights(), 0.95);
        $this->assertNull($path);
        // Прыжок невозможен, если между крышами не пусто
        $field->setCell(1, 0, 3, new GameFieldCell(3, obstacle: true));
        $path = PathFinder::findPath($field, $start, $goal, $this->getMovementWeights(), 0.95);
        $this->assertNull($path);
    }

    public function testCanClimb()
    {
        $field = new GameField();
        // Тест 1: Лазание с земли рядом со стеной
        $field->setCell(1, 0, 0, new GameFieldCell(0, obstacle: true)); // Стена (0,0,1) будет целью
        $this->assertTrue(PathFinder::canClimb($field, [0, 0, 0], [1, 0, 1]));
        // Тест 2: Лазание с уровня 1 на уровень 2 рядом со стеной (по правилам Mordheim climb невозможен, если под бойцом нет obstacle)
        $field->setCell(1, 0, 1, new GameFieldCell(1, obstacle: true)); // Стена выше
        $this->assertFalse(PathFinder::canClimb($field, [0, 0, 1], [1, 0, 2]));
        // Тест 3: Невозможность лазания, если нет стены рядом
        $this->assertFalse(PathFinder::canClimb($field, [0, 0, 0], [0, 0, 1]));
        // Тест 4: Невозможность лазания, если в целевой ячейке obstacle
        $field->setCell(1, 0, 1, new GameFieldCell(1, obstacle: true));
        $this->assertFalse(PathFinder::canClimb($field, [0, 0, 0], [1, 0, 1]));
    }

    public function testCanJumpOverGap()
    {
        $field = new GameField();
        // Тест 1: Прыжок через пустоту с крыши на крышу (по x)
        $field->setCell(0, 0, 1, new GameFieldCell(1, obstacle: true)); // Крыша под стартом
        $field->setCell(2, 0, 1, new GameFieldCell(1, obstacle: true)); // Крыша под целью
        // В (0,0,2) и (2,0,2) препятствий нет!
        $result = PathFinder::canJumpOverGap($field, [0, 0, 2], [2, 0, 2]);
        $this->assertTrue($result);
        // Тест 2: Прыжок невозможен, если между крышами не пусто
        $field->setCell(1, 0, 2, new GameFieldCell(2, obstacle: true)); // Препятствие между крышами
        $result = PathFinder::canJumpOverGap($field, [0, 0, 2], [2, 0, 2]);
        $this->assertFalse($result);
        // Тест 3: Прыжок невозможен, если под стартом нет крыши
        $field->setCell(0, 0, 1, new GameFieldCell(1));
        $result = PathFinder::canJumpOverGap($field, [0, 0, 2], [2, 0, 2]);
        $this->assertFalse($result);
        // Тест 4: Прыжок невозможен, если под целью нет крыши
        $field->setCell(2, 0, 1, new GameFieldCell(1));
        $result = PathFinder::canJumpOverGap($field, [0, 0, 2], [2, 0, 2]);
        $this->assertFalse($result);
        // Тест 5: Прыжок невозможен, если прыжок не на 2 клетки
        $result = PathFinder::canJumpOverGap($field, [0, 0, 2], [3, 0, 2]);
        $this->assertFalse($result);
    }

    public function testCanJumpUpDown()
    {
        $field = new GameField();
        // Тест 1: Прыжок вниз с крыши на соседнюю клетку
        $field->setCell(0, 0, 2, new \Mordheim\GameFieldCell(2, obstacle: true)); // Край крыши под бойцом
        $field->setCell(1, 0, 0, new \Mordheim\GameFieldCell(0, obstacle: true)); // Земля под местом приземления
        // Окружаем бойца стенами, чтобы нельзя было обойти
        $field->setCell(0, 1, 3, new \Mordheim\GameFieldCell(3, obstacle: true));
        $field->setCell(-1, 0, 3, new \Mordheim\GameFieldCell(3, obstacle: true));
        $field->setCell(0, -1, 3, new \Mordheim\GameFieldCell(3, obstacle: true));
        $field->setCell(1, 1, 3, new \Mordheim\GameFieldCell(3, obstacle: true));
        $field->setCell(-1, 1, 3, new \Mordheim\GameFieldCell(3, obstacle: true));
        $field->setCell(1, -1, 3, new \Mordheim\GameFieldCell(3, obstacle: true));
        $field->setCell(-1, -1, 3, new \Mordheim\GameFieldCell(3, obstacle: true));
        // В (0,0,3) и (1,0,1) препятствий нет!
        $result = PathFinder::canJumpUpDown($field, [0, 0, 3], [1, 0, 1]);
        $this->assertTrue($result);
        // Тест 2: Невозможность прыжка, если под бойцом нет препятствия
        $field->setCell(0, 0, 2, new \Mordheim\GameFieldCell(2));
        $result = PathFinder::canJumpUpDown($field, [0, 0, 3], [1, 0, 1]);
        $this->assertFalse($result);
        // Тест 3: Невозможность прыжка, если под целью нет препятствия (но nz>0)
        $field->setCell(1, 0, 0, new \Mordheim\GameFieldCell(0));
        $result = PathFinder::canJumpUpDown($field, [0, 0, 3], [1, 0, 1]);
        $this->assertFalse($result);
        // Тест 4: Невозможность прыжка, если в точке приземления есть препятствие
        $field->setCell(1, 0, 1, new \Mordheim\GameFieldCell(1));
        $result = PathFinder::canJumpUpDown($field, [0, 0, 3], [1, 0, 1]);
        $this->assertFalse($result);
        // Тест 5: Невозможность прыжка, если прыжок не на соседнюю клетку
        $result = PathFinder::canJumpUpDown($field, [0, 0, 3], [2, 0, 1]);
        $this->assertFalse($result);
    }
}
