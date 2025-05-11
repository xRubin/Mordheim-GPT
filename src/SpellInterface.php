<?php

namespace Mordheim;

interface SpellInterface
{
    public function getOwnerSpecialRule(): SpecialRule;
    public function getBlankDifficulty(): int;
}