<?php

namespace Mordheim;

interface SpellInterface
{
    public function getOwnerSpecialRule(): SpecialRuleInterface;
    public function getBlankDifficulty(): int;
    /**
     * @return SpecialRuleInterface[]
     */
    public function getStateRules(): array;
    public static function onPhaseShoot(Battle $battle, FighterInterface $fighter): void;
    public function onPhaseMagic(Battle $battle, FighterInterface $fighter): bool;
}