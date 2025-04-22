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

    public function getCell(int $x, int $y, int $z): FieldCell
    {
        return $this->cells[$x][$y][$z];
    }

    public function setCell(int $x, int $y, int $z, FieldCell $cell): void
    {
        $this->cells[$x][$y][$z] = $cell;
    }
}
