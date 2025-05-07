<?php
namespace Mordheim;

class Characteristics
{
    public int $movement;
    public int $weaponSkill;
    public int $ballisticSkill;
    public int $strength;
    public int $toughness;
    public int $wounds;
    public int $initiative;
    public int $attacks;
    public int $leadership;

    public function __construct(
        int $movement, int $weaponSkill, int $ballisticSkill, int $strength,
        int $toughness, int $wounds, int $initiative, int $attacks, int $leadership
    ) {
        $this->movement = $movement;
        $this->weaponSkill = $weaponSkill;
        $this->ballisticSkill = $ballisticSkill;
        $this->strength = $strength;
        $this->toughness = $toughness;
        $this->wounds = $wounds;
        $this->initiative = $initiative;
        $this->attacks = $attacks;
        $this->leadership = $leadership;
    }

    /**
     * Пустышка
     * @return static
     */
    public static function empty(): static
    {
        return new static(0,0,0,0,0,0,0,0,0);
    }
}
