<?php

namespace Mordheim;

interface EquipmentInterface
{
    public function getName(): string;
    public function getRange(): int;
    public function getStrength(int $fighterStrength): int;

    /**
     * @return SpecialRuleInterface[]
     */
    public function getSpecialRules(): array;
    public function getSlot(): Slot;
    public function hasSpecialRule(SpecialRuleInterface $rule): bool;
}
