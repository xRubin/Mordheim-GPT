<?php

namespace Mordheim\Data\Attributes;

use Attribute;
use Mordheim\Slot;

#[Attribute(Attribute::TARGET_CLASS_CONSTANT)]
readonly class SlotArmor
{
    public function __construct() {
    }

    public function getValue(): Slot
    {
        return Slot::ARMOR;
    }
}