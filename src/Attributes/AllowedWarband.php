<?php

namespace Mordheim\Attributes;

use Attribute;

#[Attribute(Attribute::IS_REPEATABLE | Attribute::TARGET_CLASS_CONSTANT)]
readonly class AllowedWarband
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