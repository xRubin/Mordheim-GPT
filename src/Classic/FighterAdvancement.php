<?php

namespace Mordheim\Classic;

use Mordheim\Characteristics;

class FighterAdvancement
{
    public function __construct(
        private Characteristics $characteristics,
        private array           $specialRules = [],
        private array           $spells = [], // WizardSpell[]
        private int             $exp = 0,
    )
    {

    }

    public static function empty(): static
    {
        return new static(
            new Characteristics()
        );
    }

    public function getCharacteristics(): Characteristics
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
     * @return WizardSpell[]
     */
    public function getSpells(): array
    {
        return $this->spells;
    }

    public function addSpell(WizardSpell $spell): static
    {
        $this->spells = array_unique(array_merge($this->spells, [$spell]));
        return $this;
    }

    public function getExp(): int
    {
        return $this->exp;
    }
}