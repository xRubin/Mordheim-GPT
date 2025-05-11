<?php

namespace Mordheim\Data\Attributes;

use Attribute;
use Mordheim\SpecialRuleInterface;

#[Attribute(Attribute::IS_REPEATABLE | Attribute::TARGET_CLASS_CONSTANT)]
readonly class StateSpecialRule
{
    public function __construct(
        private string $value,
    ) {
    }

    public function getValue(): SpecialRuleInterface
    {
        return \Mordheim\SpecialRule::tryFromName($this->value);
    }
}