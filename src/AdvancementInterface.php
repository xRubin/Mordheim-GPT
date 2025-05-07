<?php

namespace Mordheim;
interface AdvancementInterface
{
    public function getCharacteristics(): Characteristics;

    /**
     * @return Skill[]
     */
    public function getSpecialRules(): array;
}