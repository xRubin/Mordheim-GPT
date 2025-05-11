<?php

namespace Mordheim;

interface FighterPositionInterface
{
    public function getPosition(): array;
    public function setPosition(array $position): static;
}


