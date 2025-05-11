<?php

namespace Mordheim;
interface FighterAdvancementInterface extends HasSpecialRuleInterface
{
    public function getCharacteristics(): CharacteristicsInterface;

    /**
     * @return SpecialRuleInterface[]
     */
    public function getSpecialRules(): array;
    public function addSpecialRule(SpecialRule $specialRule): static;

    /**
     * @return WizardSpellInterface[] Список доступных заклинаний
     */
    public function getSpells(): array;
}