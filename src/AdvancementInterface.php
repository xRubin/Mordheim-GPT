<?php

namespace Mordheim;
interface AdvancementInterface
{
    public function getCharacteristics(): Characteristics;

    /**
     * @return SpecialRuleInterface[]
     */
    public function getSpecialRules(): array;
}