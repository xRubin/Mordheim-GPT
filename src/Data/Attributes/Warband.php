<?php

namespace Mordheim\Data\Attributes;

use Attribute;
use Mordheim\WarbandInterface;

#[Attribute(Attribute::TARGET_CLASS_CONSTANT)]
readonly class Warband
{
    public function __construct(
        private string $value,
    ) {
    }

    public function getValue(): WarbandInterface
    {
        return \Mordheim\Data\Warband::tryFromName($this->value);
    }
}