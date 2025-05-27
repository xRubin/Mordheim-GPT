<?php

namespace Mordheim\Classic\Exceptions;

class PathfinderTargetUnreachableException extends \Exception
{
    private array $position = [];
    private array $target = [];

    public function getPosition(): array
    {
        return $this->position;
    }

    public function setPosition(array $position): static
    {
        $this->position = $position;
        return $this;
    }

    public function getTarget(): array
    {
        return $this->target;
    }

    public function setTarget(array $target): static
    {
        $this->target = $target;
        return $this;
    }
}