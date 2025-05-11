<?php

namespace Mordheim;

interface EquipmentInterface extends HasSpecialRuleInterface
{
    public function getName(): string;
    public function getRange(): int;
    public function getStrength(int $fighterStrength): int;

    /**
     * @return SpecialRuleInterface[]
     */
    public function getSpecialRules(): array;
    public function getSlot(): Slot;
}
