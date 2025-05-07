<?php

namespace Mordheim;

class FighterAdvancement implements AdvancementInterface
{
    public function __construct(
        private Characteristics $characteristics,
        private array $specialRules = [],
    )
    {

    }

    public static function empty(): static
    {
        return new static(
            Characteristics::empty()
        );
    }

    public function getCharacteristics(): Characteristics
    {
        return $this->characteristics;
    }

    /**
     * @return Skill[]
     */
    public function getSpecialRules(): array
    {
        return $this->specialRules;
    }
}