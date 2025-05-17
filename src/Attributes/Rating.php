<?php

namespace Mordheim\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS_CONSTANT)]
readonly class Rating
{
    public function __construct(
        private int $value,
    ) {
    }

    public function getValue(): int
    {
        return $this->value;
    }
}