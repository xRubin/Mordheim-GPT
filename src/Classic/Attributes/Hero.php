<?php

namespace Mordheim\Classic\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS_CONSTANT)]
readonly class Hero
{
    public function __construct() {
    }
}