<?php

namespace Mordheim\Data\Attributes;

use Attribute;
use Mordheim\SpecialRule;

#[Attribute(Attribute::TARGET_CLASS_CONSTANT)]
readonly class WizardRule
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