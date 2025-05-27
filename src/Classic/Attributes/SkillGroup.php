<?php

namespace Mordheim\Classic\Attributes;

use Attribute;

#[Attribute(Attribute::IS_REPEATABLE | Attribute::TARGET_CLASS_CONSTANT)]
readonly class SkillGroup
{
    public function __construct(
        private string $value,
    )
    {
    }

    public function getValue(): \Mordheim\Classic\SkillGroup
    {
        return \Mordheim\Classic\SkillGroup::tryFromName($this->value);
    }
}