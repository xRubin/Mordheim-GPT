<?php

namespace Mordheim;

interface EquipmentListInterface
{
    public function getTitle(): string;
    public function getItems(): array;
}