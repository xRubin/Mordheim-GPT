<?php

namespace Mordheim\Classic\Attributes;

use Attribute;
use Mordheim\Classic\SpecialRule;

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