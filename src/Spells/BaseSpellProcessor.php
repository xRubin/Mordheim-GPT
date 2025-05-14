<?php

namespace Mordheim\Spells;

use Mordheim\Battle;
use Mordheim\Data\Spell;
use Mordheim\Dice;
use Mordheim\Fighter;

abstract class BaseSpellProcessor implements SpellProcessorInterface
{
    public Spell $spell;
    public int $difficulty;

    public function onPhaseRecovery(Battle $battle, Fighter $owner): void
    {

    }

    public function onPhaseShoot(Battle $battle, Fighter $caster): void
    {

    }

    public function onCasterStatusChange(Battle $battle, Fighter $caster): void
    {

    }

    /**
     * false = not changed
     * true = diffused
     */
    public function rollSpellDiffused(Battle $battle, Fighter $caster): bool
    {
        $roll = Dice::roll(6) + Dice::roll(6);
        if ($roll < $this->difficulty) {
            \Mordheim\BattleLogger::add("{$this->spell->name} спадает с {$caster->getName()} (бросок: $roll)");
            $battle->removeActiveSpell($caster, $this->spell);
            return true;
        } else {
            \Mordheim\BattleLogger::add("{$this->spell->name} остаётся на {$caster->getName()} (бросок: $roll)");
            return false;
        }
    }

    /**
     * false = failed
     * true = success
     */
    public function rollSpellApplied(Battle $battle, Fighter $caster): bool
    {
        $roll = Dice::roll(6) + Dice::roll(6);

        \Mordheim\BattleLogger::add("{$caster->getName()} бросает 2d6=$roll для заклинания {$this->spell->name} (сложность {$this->difficulty})");
        if ($roll < $this->difficulty) {
            \Mordheim\BattleLogger::add("{$caster->getName()} не смог применить заклинание {$this->spell->name}.");
            return false; // failed
        }
        \Mordheim\BattleLogger::add("{$caster->getName()} применяет заклинание {$this->spell->name}!");
        return true;
    }
}