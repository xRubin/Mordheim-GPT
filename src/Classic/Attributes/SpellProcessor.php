<?php

namespace Mordheim\Classic\Attributes;

use Attribute;
use Mordheim\Classic\Spells\SpellProcessorInterface;

#[Attribute(Attribute::TARGET_CLASS_CONSTANT)]
readonly class SpellProcessor
{
    public function __construct(
        private string $value,
    )
    {
    }

    public function getValue(): SpellProcessorInterface
    {
        return new $this->value();
    }
}