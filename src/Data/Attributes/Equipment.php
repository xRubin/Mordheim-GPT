<?php

namespace Mordheim\Data\Attributes;

use Attribute;

#[Attribute(Attribute::IS_REPEATABLE | Attribute::TARGET_CLASS_CONSTANT)]
readonly class Equipment
{
    public function __construct(
        private string $value,
    )
    {
    }

    public function getValue(): \Mordheim\Equipment
    {
        return \Mordheim\Equipment::tryFromName($this->value);
    }
}