<?php

namespace Mordheim\Classic\Attributes;

use Attribute;

#[Attribute(Attribute::IS_REPEATABLE | Attribute::TARGET_CLASS_CONSTANT)]
readonly class Equipment
{
    public function __construct(
        private string $value,
    )
    {
    }

    public function getValue(): \Mordheim\Classic\Equipment
    {
        return \Mordheim\Classic\Equipment::tryFromName($this->value);
    }
}