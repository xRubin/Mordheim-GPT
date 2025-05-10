<?php

namespace Mordheim\Data\Attributes;

use Attribute;
use Mordheim\EquipmentInterface;

#[Attribute(Attribute::IS_REPEATABLE | Attribute::TARGET_CLASS_CONSTANT)]
readonly class Equipment
{
    public function __construct(
        private string $value,
    )
    {
    }

    public function getValue(): EquipmentInterface
    {
        return \Mordheim\Data\Equipment::tryFromName($this->value);
    }
}