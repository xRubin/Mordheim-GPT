<?php

namespace Mordheim;
interface FighterAdvancementInterface
{
    public function getCharacteristics(): CharacteristicsInterface;

    /**
     * @return SpecialRuleInterface[]
     */
    public function getSpecialRules(): array;
}