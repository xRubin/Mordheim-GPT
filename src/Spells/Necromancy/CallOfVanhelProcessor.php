<?php

namespace Mordheim\Spells\Necromancy;

use Mordheim\Battle;
use Mordheim\Data\Blank;
use Mordheim\Data\Spell;
use Mordheim\Exceptions\ChargeFailedException;
use Mordheim\Fighter;
use Mordheim\Rule\Charge;
use Mordheim\Rule\Move;
use Mordheim\Ruler;
use Mordheim\Spells\BaseSpellProcessor;

class CallOfVanhelProcessor extends BaseSpellProcessor
{
    public Spell $spell = Spell::CALL_OF_VANHEL;

    public function __construct(
        public int $difficulty = 6
    )
    {

    }

    public function onPhaseMagic(Battle $battle, Fighter $caster): ?bool
    {
        $target = $this->findTarget($battle, $caster);
        if (!$target) {
            \Mordheim\BattleLogger::add("Нет подходящей цели для {$this->spell->name}.");
            return null;
        }

        $enemies = $battle->getEnemiesFor($target);
        if (empty($enemies)) {
            \Mordheim\BattleLogger::add("Нет подходящей цели для {$this->spell->name}.");
            return null;
        }

        if (!parent::rollSpellApplied($battle, $caster))
            return false;

        usort($enemies, fn($a, $b) => Ruler::distance($target, $a) <=> Ruler::distance($target, $b));
        \Mordheim\BattleLogger::add("{$target->getName()} получает дополнительное движение от {$this->spell->name}.");
        try {
            $battle->getActiveCombats()->add(
                Charge::attempt($battle, $target, $enemies[0], 0.4)
            );
        } catch (ChargeFailedException $e) {
            Move::common($battle, $target, $enemies[0]->getState()->getPosition(), 0.4);
        }
        return true;
    }

    private function findTarget(Battle $battle, Fighter $caster): ?Fighter
    {
        foreach ($battle->getAlliesFor($caster) as $ally) {
            if (
                in_array($ally->getBlank(), [Blank::UNDEAD_ZOMBIE, Blank::UNDEAD_DIRE_WOLF])
                && Ruler::distance($caster, $ally) <= 6
            ) {
                return $ally;
            }
        }
        return null;
    }
}