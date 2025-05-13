<?php

namespace Mordheim\Spells\PrayersOfSigmar;

use Mordheim\Battle;
use Mordheim\Data\Spell;
use Mordheim\Data\Warband;
use Mordheim\Fighter;
use Mordheim\Ruler;
use Mordheim\Spells\BaseSpellProcessor;

class SoulfireProcessor extends BaseSpellProcessor
{
    public Spell $spell = Spell::SOULFIRE;

    public function __construct(
        public int $difficulty = 9
    )
    {

    }

    public function onPhaseMagic(Battle $battle, Fighter $caster): ?bool
    {
        if (!parent::rollSpellApplied($battle, $caster))
            return false;

        $enemies = $battle->getEnemiesFor($caster);
        foreach ($enemies as $enemy) {
            if (Ruler::distance($caster, $enemy) <= 4) {
                $damage = in_array($enemy->getBlank()->getWarband(), [Warband::UNDEAD, Warband::CULT_OF_THE_POSSESSED]) ? 5 : 3;
                $enemy->getState()->modifyWounds(-$damage);
                \Mordheim\BattleLogger::add("{$enemy->getName()} получает {$damage} урона от {$this->spell->name} (без сейва).");
                if ($enemy->getState()->getWounds() <= 0) {
                    $battle->killFighter($enemy);
                }
            }
        }
        return true;
    }
}