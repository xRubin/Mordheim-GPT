<?php

namespace Mordheim;

class GameFieldCell
{
    public function __construct(
        public int $height,
        public bool $obstacle = false,
        public bool $ladder = false,
        public bool $difficultTerrain = false,
        public bool $dangerousTerrain = false,
        public bool $water = false,
    )
    {
    }
}
