<?php

namespace Mordheim\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS_CONSTANT)]
readonly class MaxCharacteristics
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
    )
    {
    }

    public function getValue(): \Mordheim\Characteristics
    {
        return new \Mordheim\Characteristics(
            movement: $this->movement,
            weaponSkill: $this->weaponSkill,
            ballisticSkill: $this->ballisticSkill,
            strength: $this->strength,
            toughness: $this->toughness,
            wounds: $this->wounds,
            initiative: $this->initiative,
            attacks: $this->attacks,
            leadership: $this->leadership
        );
    }
}