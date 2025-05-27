<?php

namespace Mordheim\Classic\Attributes;

use Attribute;

#[Attribute(Attribute::IS_REPEATABLE | Attribute::TARGET_CLASS_CONSTANT)]
readonly class AllowedWarband
{
    public function __construct(
        private string $value,
    )
    {
    }

    public function getValue(): \Mordheim\Classic\Warband
    {
        return \Mordheim\Classic\Warband::tryFromName($this->value);
    }
}