<?php
namespace Mordheim;

use Mordheim\Strategy;

class Warband
{
    public string $name;
    /** @var Fighter[] */
    public array $fighters = [];

    public function __construct(string $name, array $fighters = [])
    {
        $this->name = $name;
        $this->fighters = $fighters;
    }
}
