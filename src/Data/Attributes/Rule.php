<?php

namespace Mordheim\Data\Attributes;

use Attribute;
use Mordheim\SpecialRule;

#[Attribute(Attribute::IS_REPEATABLE | Attribute::TARGET_CLASS_CONSTANT)]
readonly class Rule
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