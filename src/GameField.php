<?php

namespace Mordheim;

class GameField
{
    private array $cells = [];

    public function __construct(
        private readonly int $width = 48,
        private readonly int $length = 48,
        private readonly int $height = 4,
    )
    {

    }

    public function getCell(int $x, int $y, int $z): ?FieldCell
    {
        if ($this->isOutOfBounds($x, $y, $z))
            return null;

        if (isset($this->cells[$x][$y][$z])) {
            return $this->cells[$x][$y][$z];
        }
        return new FieldCell($z); // obstacle=false по умолчанию
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
