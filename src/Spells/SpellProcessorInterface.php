<?php

namespace Mordheim\Spells;

use Mordheim\Battle;
use Mordheim\Fighter;

interface SpellProcessorInterface
{
    public function onPhaseRecovery(Battle $battle, Fighter $owner): void;
    public function onPhaseShoot(Battle $battle, Fighter $caster): void;

    /**
     * null = not allowed
     * false = failed
     * true = success
     */
    public function onPhaseMagic(Battle $battle, Fighter $caster): ?bool;

    public function onCasterStatusChange(Battle $battle, Fighter $caster): void;
}