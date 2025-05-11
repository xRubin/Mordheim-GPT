<?php

namespace Mordheim;

interface CharacteristicsInterface
{
    public function getMovement(): int;

    public function getWeaponSkill(): int;

    public function getBallisticSkill(): int;

    public function getStrength(): int;

    public function getToughness(): int;

    public function getWounds(): int;

    public function getInitiative(): int;

    public function getAttacks(): int;

    public function getLeadership(): int;
}
