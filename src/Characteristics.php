<?php

namespace Mordheim;

class Characteristics
{
    public function __construct(
        public int $movement = 0,
        public int $weaponSkill = 0,
        public int $ballisticSkill = 0,
        public int $strength = 0,
        public int $toughness = 0,
        public int $wounds = 0,
        public int $initiative = 0,
        public int $attacks = 0,
        public int $leadership = 0,
    )
    {
    }

    public function getMovement(): int
    {
        return $this->movement;
    }

    public function getWeaponSkill(): int
    {
        return $this->weaponSkill;
    }

    public function getBallisticSkill(): int
    {
        return $this->ballisticSkill;
    }

    public function getStrength(): int
    {
        return $this->strength;
    }

    public function getToughness(): int
    {
        return $this->toughness;
    }

    public function getWounds(): int
    {
        return $this->wounds;
    }

    public function getInitiative(): int
    {
        return $this->initiative;
    }

    public function getAttacks(): int
    {
        return $this->attacks;
    }

    public function getLeadership(): int
    {
        return $this->leadership;
    }

    public function getByName(string $name): int
    {
        return $this->$name;
    }

    public function setByName(string $name, int $value): static
    {
        $this->$name = $value;
        return $this;
    }

    public function add(Characteristics $characteristics): Characteristics
    {
        return new Characteristics(
            movement: $this->getMovement() + $characteristics->getMovement(),
            weaponSkill: $this->getWeaponSkill() + $characteristics->getWeaponSkill(),
            ballisticSkill: $this->getBallisticSkill() + $characteristics->getBallisticSkill(),
            strength: $this->getStrength() + $characteristics->getStrength(),
            toughness: $this->getToughness() + $characteristics->getToughness(),
            wounds: $this->getWounds() + $characteristics->getWounds(),
            initiative: $this->getInitiative() + $characteristics->getInitiative(),
            attacks: $this->getAttacks() + $characteristics->getAttacks(),
            leadership: $this->getLeadership() + $characteristics->getLeadership()
        );
    }
}
