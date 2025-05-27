<?php

namespace Mordheim\Classic\Attributes;

use Attribute;
use Mordheim\Slot;

#[Attribute(Attribute::TARGET_CLASS_CONSTANT)]
readonly class SlotRanged
{
    public function __construct() {
    }

    public function getValue(): Slot
    {
        return Slot::RANGED;
    }
}