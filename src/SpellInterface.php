<?php

namespace Mordheim;

interface SpellInterface
{
    public function getOwnerSpecialRule(): SpecialRuleInterface;
    public function getBlankDifficulty(): int;
    /**
     * @return SpecialRuleInterface[]
     */
    public function getStateRules(): array;
    public function getProcessor(): ?Spells\SpellProcessorInterface;
}