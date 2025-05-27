<?php

namespace Mordheim\Classic\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS_CONSTANT)]
readonly class HireFee
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