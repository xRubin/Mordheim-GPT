<?php

namespace Mordheim;
interface FighterAdvancementInterface extends HasSpecialRuleInterface
{
    public function getCharacteristics(): CharacteristicsInterface;

    /**
     * @return SpecialRuleInterface[]
     */
    public function getSpecialRules(): array;
}