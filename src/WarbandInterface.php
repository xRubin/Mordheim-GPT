<?php

namespace Mordheim;

interface WarbandInterface
{
    public function getBlanks(): array;
    public function getStartWealth(): int;
    public function getMaxFighters(): int;
}