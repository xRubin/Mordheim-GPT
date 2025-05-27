<?php

namespace Mordheim\Classic\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS_CONSTANT)]
readonly class EquipmentList
{
    public function __construct(
        private string $value,
    )
    {
    }

    public function getValue(): \Mordheim\Classic\EquipmentList
    {
        return \Mordheim\Classic\EquipmentList::tryFromName($this->value);
    }
}