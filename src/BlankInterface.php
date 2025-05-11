<?php

namespace Mordheim;

interface BlankInterface extends HasSpecialRuleInterface
{
    public function getWarband(): ?WarbandInterface;

    /**
     * @return WarbandInterface[]
     */
    public function getAllowedWarbands(): array;

    public function getHireFee(): int;
    public function getUpkeepFee(): int;

    public function getStartExp(): int;
    public function getRating(): int;

    /**
     * @return EquipmentInterface[]
     */
    public function getEquipment(): array;

    public function getMinCount(): int;

    public function getMaxCount(): int;

    public function isHero(): bool;

    public function isHenchman(): bool;

    public function isHiredSword(): bool;

    public function getCharacteristics(): CharacteristicsInterface;

    /**
     * @return SpecialRuleInterface[]
     */
    public function getSpecialRules(): array;

    public function getEquipmentList(): EquipmentListInterface;

    /**
     * @return SkillGroupInterface[]
     */
    public function getAdvancementSkillGroups(): array;
}