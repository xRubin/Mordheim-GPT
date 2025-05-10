<?php

namespace Mordheim\Data\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS_CONSTANT)]
readonly class Henchman
{
    public function __construct() {
    }
}