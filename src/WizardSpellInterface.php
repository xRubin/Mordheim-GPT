<?php

namespace Mordheim;

interface WizardSpellInterface
{
    public function getSpell(): SpellInterface;
    public function getDifficulty(): int;
}