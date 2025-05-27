<?php

namespace Mordheim\Classic\Spells\ChaosRituals;

use Mordheim\Classic\Battle;
use Mordheim\Classic\Fighter;
use Mordheim\Classic\Ruler;
use Mordheim\Classic\Spell;
use Mordheim\Classic\Spells\BaseSpellProcessor;
use Mordheim\Dice;

class LureOfChaosProcessor extends BaseSpellProcessor
{
    public Spell $spell = Spell::LURE_OD_CHAOS;

    public function __construct(
        public int $difficulty = 9
    )
    {

    }

    public function onPhaseRecovery(Battle $battle, Fighter $owner): void
    {
        $roll = Dice::roll(6) + Dice::roll(6);
        $success = $roll <= $owner->getLeadership();
        if ($success) {
            $battle->removeActiveSpell($owner, $this->spell);
            \Mordheim\BattleLogger::add("{$owner->getName()} проходит тест Лидерства и освобождается от контроля {$this->spell->name} (бросок $roll против {$owner->getLeadership()}).");
        } else {
            \Mordheim\BattleLogger::add("{$owner->getName()} не прошёл тест Лидерства и остаётся под контролем {$this->spell->name} (бросок $roll против {$owner->getLeadership()}).");
        }
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

        $mageRoll = Dice::roll(6) + $caster->getLeadership();
        $targetRoll = Dice::roll(6) + $target->getLeadership();
        if ($mageRoll > $targetRoll) {
            $target->getState()->addActiveSpell($this->spell);
            \Mordheim\BattleLogger::add("{$caster->getName()} получает контроль над {$target->getName()} ({$this->spell->name})!");
            foreach ($battle->getActiveCombats()->getByFighter($target) as $combat)
                $battle->getActiveCombats()->remove($combat);
        } else {
            \Mordheim\BattleLogger::add("{$target->getName()} устоял против {$this->spell->name}.");
        }
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