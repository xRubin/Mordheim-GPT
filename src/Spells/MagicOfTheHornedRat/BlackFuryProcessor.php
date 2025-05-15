<?php

namespace Mordheim\Spells\MagicOfTheHornedRat;

use Mordheim\Battle;
use Mordheim\CloseCombat;
use Mordheim\Data\Equipment;
use Mordheim\Data\Spell;
use Mordheim\Dice;
use Mordheim\Fighter;
use Mordheim\Rule\Attack;
use Mordheim\Rule\Charge;
use Mordheim\Ruler;
use Mordheim\Spells\BaseSpellProcessor;

class BlackFuryProcessor extends BaseSpellProcessor
{
    public Spell $spell = Spell::BLACK_FURY;

    public function __construct(
        public int $difficulty = 7
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

        $adj = Charge::getNearestChargePosition($battle, $caster, $target);
        if (empty($adj)) {
            \Mordheim\BattleLogger::add("Нет свободных клеток рядом с врагом для {$this->spell->name}.");
            return null;
        }

        if (!parent::rollSpellApplied($battle, $caster))
            return false;

        $caster->getState()->setPosition($adj);
        \Mordheim\BattleLogger::add("{$caster->getName()} перемещается к {$target->getName()} с помощью {$this->spell->name}.");

        $battle->getActiveCombats()->add(new CloseCombat($caster, $target));
        $caster->getState()->addActiveSpell($this->spell);
        return true;
    }

    private function findEnemy(Battle $battle, Fighter $caster): ?Fighter
    {
        foreach ($battle->getEnemiesFor($caster) as $enemy) {
            if (Ruler::distance($caster, $enemy) > 12)
                continue;
            return $enemy;
        }
        return null;
    }
}