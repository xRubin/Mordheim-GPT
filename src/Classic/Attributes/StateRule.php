<?php

namespace Mordheim\Classic\Attributes;

use Attribute;
use Mordheim\Classic\SpecialRule;

#[Attribute(Attribute::IS_REPEATABLE | Attribute::TARGET_CLASS_CONSTANT)]
readonly class StateRule
{
    public function __construct(
        private string $value,
    )
    {
    }

    public function getValue(): SpecialRule
    {
        return SpecialRule::tryFromName($this->value);
    }
}