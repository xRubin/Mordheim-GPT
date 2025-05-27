<?php

namespace Mordheim;

use Mordheim\Classic\Fighter;

class Band
{
    public string $name;
    /** @var Fighter[] */
    public array $fighters = [];

    public int $wyrdStones = 0;

    public function __construct(string $name, array $fighters = [])
    {
        $this->name = $name;
        $this->fighters = $fighters;
    }
}
