<?php

namespace Mordheim\Attributes;

use Attribute;

#[Attribute(Attribute::IS_REPEATABLE | Attribute::TARGET_CLASS_CONSTANT)]
readonly class SpecialRule
{
    public function __construct(
        private string $value,
    ) {
    }

    public function getValue(): \Mordheim\Data\SpecialRule
    {
        return \Mordheim\Data\SpecialRule::tryFromName($this->value);
    }
}