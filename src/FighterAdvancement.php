<?php

namespace Mordheim;

class FighterAdvancement implements FighterAdvancementInterface
{
    public function __construct(
        private CharacteristicsInterface $characteristics,
        private array $specialRules = [],
    )
    {

    }

    public static function empty(): static
    {
        return new static(
            new Characteristics()
        );
    }

    public function getCharacteristics(): CharacteristicsInterface
    {
        return $this->characteristics;
    }

    /**
     * @return SpecialRule[]
     */
    public function getSpecialRules(): array
    {
        return $this->specialRules;
    }
}