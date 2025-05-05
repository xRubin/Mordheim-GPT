<?php
namespace Mordheim;

class GameField
{
    private int $width = 64;
    private int $length = 64;
    private int $height = 4;
    private array $cells = [];

    public function __construct()
    {
        for ($x = 0; $x < $this->width; $x++) {
            for ($y = 0; $y < $this->length; $y++) {
                for ($z = 0; $z < $this->height; $z++) {
                    $this->cells[$x][$y][$z] = new FieldCell();
                }
            }
        }
    }

    public function getCell(int $x, int $y, int $z): ?FieldCell
    {
        if ($this->isOutOfBounds($x, $y, $z))
            return null;

        if (isset($this->cells[$x][$y][$z])) {
            return $this->cells[$x][$y][$z];
        }
        return new FieldCell(); // obstacle=false по умолчанию
    }

    public function setCell(int $x, int $y, int $z, FieldCell $cell): void
    {
        $this->cells[$x][$y][$z] = $cell;
    }

    public function getWidth(): int
    {
        return $this->width;
    }

    public function getLength(): int
    {
        return $this->length;
    }

    public function getHeight(): int
    {
        return $this->height;
    }

    public function isOutOfBounds(int $x, int $y, int $z): bool
    {
        return $x < 0 || $y < 0 || $z < 0 || $x >= $this->getWidth() || $y >= $this->getLength() || $z >= $this->getHeight();
    }

    public function isObstacle(int $x, int $y, int $z): bool
    {
        return $this->getCell($x, $y, $z)->obstacle;
    }
}
