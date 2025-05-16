<?php

namespace Mordheim\Spells\MagicOfTheHornedRat;

use Mordheim\Battle;
use Mordheim\Fighter;
use Mordheim\Ruler;
use Mordheim\Spell;
use Mordheim\Spells\BaseSpellProcessor;

class SorcerersCurseProcessor extends BaseSpellProcessor
{
    public Spell $spell = Spell::SORCERERS_CURSE;

    public function __construct(
        public int $difficulty = 6
    )
    {

    }

    public function onPhaseRecovery(Battle $battle, Fighter $owner): void
    {
        $battle->removeActiveSpell($owner, $this->spell);
    }

    public function onPhaseMagic(Battle $battle, Fighter $caster): ?bool
    {
        $target = $this->findEnemy($battle, $caster);
        if (!$target) {
            \Mordheim\BattleLogger::add("Нет подходящей цели для {$this->spell->name}.");
            return null;
        }

        if (!parent::rollSpellApplied($battle, $caster))
            return false;

        $target->getState()->addActiveSpell($this->spell);
        \Mordheim\BattleLogger::add("{$caster->getName()} накладывает проклятие на {$target->getName()} ({$this->spell->name})!");
        return true;
    }

    private function findEnemy(Battle $battle, Fighter $caster): ?Fighter
    {
        $enemies = $battle->getEnemiesFor($caster);
        if (empty($enemies))
            return null;
        usort($enemies, fn($a, $b) => Ruler::distance($caster, $a) <=> Ruler::distance($caster, $b));
        $closest = $enemies[0];
        if (Ruler::distance($caster, $closest) > 12)
            return null;
        return $closest;
    }
}