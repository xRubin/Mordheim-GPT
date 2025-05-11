<?php

namespace Mordheim\Data\Attributes;

use Attribute;
use Mordheim\SpecialRuleInterface;

#[Attribute(Attribute::TARGET_CLASS_CONSTANT)]
readonly class WizardSpecialRule
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