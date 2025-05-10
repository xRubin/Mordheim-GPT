<?php

namespace Mordheim;

interface MutationInterface
{
    public function getCost(): int;

    public function getSpecialRules(): array;
}