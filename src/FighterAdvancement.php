<?php

namespace Mordheim;

class FighterAdvancement implements FighterAdvancementInterface
{
    public function __construct(
        private CharacteristicsInterface $characteristics,
        private array $specialRules = [],
        private array $spells = [], // WizardSpellInterface[]
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

    /**
     * @param SpecialRule $specialRule
     * @return bool
     */
    public function hasSpecialRule(SpecialRule $specialRule): bool
    {
        return in_array($specialRule, $this->getSpecialRules());
    }

    /**
     * @param SpecialRule $specialRule
     * @return static
     */
    public function addSpecialRule(SpecialRule $specialRule): static
    {
        $this->specialRules = array_unique(array_merge($this->specialRules, [$specialRule]));
        return $this;
    }

    /**
     * @return WizardSpellInterface[]
     */
    public function getSpells(): array
    {
        return $this->spells;
    }
}