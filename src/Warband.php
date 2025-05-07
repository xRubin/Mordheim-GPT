<?php

namespace Mordheim;

class Warband
{
    public string $name;
    /** @var FighterInterface[] */
    public array $fighters = [];

    public function __construct(string $name, array $fighters = [])
    {
        $this->name = $name;
        $this->fighters = $fighters;
    }
}
