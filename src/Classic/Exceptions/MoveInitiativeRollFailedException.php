<?php

namespace Mordheim\Classic\Exceptions;

use Mordheim\GameFieldCell;

class MoveInitiativeRollFailedException extends \Exception
{
    private ?GameFieldCell $field = null;

    public function getField(): ?GameFieldCell
    {
        return $this->field;
    }

    public function setField(?GameFieldCell $field): static
    {
        $this->field = $field;
        return $this;
    }
}