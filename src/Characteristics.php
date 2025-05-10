<?php

namespace Mordheim;

class Characteristics
{
    public function __construct(
        public int $movement,
        public int $weaponSkill,
        public int $ballisticSkill,
        public int $strength,
        public int $toughness,
        public int $wounds,
        public int $initiative,
        public int $attacks,
        public int $leadership,
    )
    {
    }

    /**
     * Пустышка
     * @return static
     */
    public static function empty(): static
    {
        return new static(0, 0, 0, 0, 0, 0, 0, 0, 0);
    }
}
