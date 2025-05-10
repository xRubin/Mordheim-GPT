<?php

namespace Mordheim\Data\Attributes;

use Attribute;
use Mordheim\EquipmentListInterface;

#[Attribute(Attribute::TARGET_CLASS_CONSTANT)]
readonly class EquipmentList
{
    public function __construct(
        private string $value,
    ) {
    }

    public function getValue(): EquipmentListInterface
    {
        return \Mordheim\Data\EquipmentList::tryFromName($this->value);
    }
}