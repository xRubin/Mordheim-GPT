<?php

namespace Mordheim\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS_CONSTANT)]
readonly class Warband
{
    public function __construct(
        private string $value,
    )
    {
    }

    public function getValue(): \Mordheim\Warband
    {
        return \Mordheim\Warband::tryFromName($this->value);
    }
}