<?php

namespace Mordheim\Data\Attributes;

use Attribute;
use Mordheim\SkillGroupInterface;

#[Attribute(Attribute::IS_REPEATABLE | Attribute::TARGET_CLASS_CONSTANT)]
readonly class SkillGroup
{
    public function __construct(
        private string $value,
    ) {
    }

    public function getValue(): SkillGroupInterface
    {
        return \Mordheim\Data\SkillGroup::tryFromName($this->value);
    }
}