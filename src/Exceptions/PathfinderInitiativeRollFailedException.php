<?php

namespace Mordheim\Exceptions;

use Mordheim\FieldCell;

class PathfinderInitiativeRollFailedException extends \Exception
{
    private ?FieldCell $field = null;

    public function getField(): ?FieldCell
    {
        return $this->field;
    }

    public function setField(?FieldCell $field): static
    {
        $this->field = $field;
        return $this;
    }
}