<?php

namespace Mordheim\Classic\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS_CONSTANT)]
readonly class Cost
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