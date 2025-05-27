<?php

namespace Mordheim\Classic\Attributes;

use Attribute;
use Mordheim\Slot;

#[Attribute(Attribute::TARGET_CLASS_CONSTANT)]
readonly class SlotArmour
{
    public function __construct() {
    }

    public function getValue(): Slot
    {
        return Slot::ARMOUR;
    }
}