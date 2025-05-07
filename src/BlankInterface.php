<?php

namespace Mordheim;

use Mordheim\Data\SkillGroup;

interface BlankInterface
{
    public function getTitle(): string;
    public function getHireCost(): int;
    public function getStartExp(): int;

    /**
     * @return Weapon[]
     */
    public function getStartWeapons(): array;

    /**
     * @return Armor[]
     */
    public function getStartArmors(): array;
    public function getMinCount(): int;
    public function getMaxCount(): int;
    public function isHero(): bool;
    public function isHenchman(): bool;
    public function isMercenary(): bool;
    public function getCharacteristics(): Characteristics;
    /**
     * @return Skill[]
     */
    public function getSpecialRules(): array;
    public function getEquipmentList(): EquipmentListInterface;

    /**
     * @return SkillGroup[]
     */
    public function getAdvancementSkillGroups(): array;
}