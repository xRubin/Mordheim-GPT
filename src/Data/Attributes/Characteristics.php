<?php

namespace Mordheim\Data\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS_CONSTANT)]
readonly class Characteristics
{
    public function __construct(
        private int $movement,
        private int $weaponSkill,
        private int $ballisticSkill,
        private int $strength,
        private int $toughness,
        private int $wounds,
        private int $initiative,
        private int $attacks,
        private int $leadership,
    ) {
    }

    public function getValue(): \Mordheim\Characteristics
    {
        return new \Mordheim\Characteristics(
            $this->movement,
            $this->weaponSkill,
            $this->ballisticSkill,
            $this->strength,
            $this->toughness,
            $this->wounds,
            $this->initiative,
            $this->attacks,
            $this->leadership
        );
    }
}