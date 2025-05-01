<?php

namespace Mordheim\Exceptions;

use Mordheim\FighterState;

class FighterAbnormalStateException extends \Exception
{
    private ?FighterState $state = null;

    public function getState(): ?FighterState
    {
        return $this->state;
    }

    public function setState(?FighterState $state): static
    {
        $this->state = $state;
        return $this;
    }
}