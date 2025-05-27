<?php

namespace Mordheim\Classic;

class WizardSpell
{
    public function __construct(
        private readonly Spell $spell,
        private readonly int   $difficulty
    )
    {

    }

    public static function create(Spell $spell)
    {
        return new static($spell, $spell->getBlankDifficulty());
    }

    public function getSpell(): Spell
    {
        return $this->spell;
    }

    public function getDifficulty(): int
    {
        return $this->difficulty;
    }
}