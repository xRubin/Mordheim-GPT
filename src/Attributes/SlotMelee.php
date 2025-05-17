<?php

namespace Mordheim\Attributes;

use Attribute;
use Mordheim\Slot;

#[Attribute(Attribute::TARGET_CLASS_CONSTANT)]
readonly class SlotMelee
{
    public function __construct() {
    }

    public function getValue(): Slot
    {
        return Slot::MELEE;
    }
}