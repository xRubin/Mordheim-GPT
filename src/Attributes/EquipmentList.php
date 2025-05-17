<?php

namespace Mordheim\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS_CONSTANT)]
readonly class EquipmentList
{
    public function __construct(
        private string $value,
    )
    {
    }

    public function getValue(): \Mordheim\EquipmentList
    {
        return \Mordheim\EquipmentList::tryFromName($this->value);
    }
}