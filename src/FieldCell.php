<?php
namespace Mordheim;

class FieldCell
{
    public bool $obstacle = false;
    public bool $ladder = false;
    public string $surfaceType = 'normal'; // normal, rough, water, etc.
    public bool $difficultTerrain = false;
    public bool $dangerousTerrain = false;
    public int $height = 0; // высота клетки (для прыжков/падений)
    public bool $water = false;
    // Можно добавить типы препятствий и др. свойства
}
