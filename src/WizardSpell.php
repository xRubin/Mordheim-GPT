<?php

namespace Mordheim;

use Mordheim\Data\Spell;

class WizardSpell implements WizardSpellInterface
{
    public function __construct(
        private readonly SpellInterface $spell,
        private readonly int            $difficulty
    )
    {

    }

    public static function create(Spell $spell)
    {
        return new static($spell, $spell->getBlankDifficulty());
    }

    public function getSpell(): SpellInterface
    {
        return $this->spell;
    }

    public function getDifficulty(): int
    {
        return $this->difficulty;
    }
}