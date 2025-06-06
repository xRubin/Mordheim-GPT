<?php

namespace Mordheim\Classic\Spells\ChaosRituals;

use Mordheim\Classic\Battle;
use Mordheim\Classic\Equipment;
use Mordheim\Classic\Fighter;
use Mordheim\Classic\Rule\Attack;
use Mordheim\Classic\Ruler;
use Mordheim\Classic\Spell;
use Mordheim\Classic\Spells\BaseSpellProcessor;
use Mordheim\Dice;

class DarkBloodProcessor extends BaseSpellProcessor
{
    public Spell $spell = Spell::DARK_BLOOD;
    public Equipment $weapon = Equipment::DARK_BLOOD;

    public function __construct(
        public int $difficulty = 8
    )
    {

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

        $hits = Dice::roll(3); // D3
        \Mordheim\BattleLogger::add("{$target->getName()} получает {$hits} попаданий силой 5 от {$this->spell->name}.");
        for ($i = 0; $i < $hits; $i++) {
            Attack::magic($battle, $caster, $target, $this->weapon);
        }
        // Маг кидает на травму себе
        \Mordheim\Classic\Rule\Injuries::roll($battle, $caster, $caster); // TODO treat out of action as stunned
        return true;
    }

    private function findEnemy(Battle $battle, Fighter $caster): ?Fighter
    {
        $enemies = $battle->getEnemiesFor($caster);
        if (empty($enemies))
            return null;
        // К ближайшему врагу
        usort($enemies, fn($a, $b) => Ruler::distance($caster, $a) <=> Ruler::distance($caster, $b));
        $closest = $enemies[0];
        if (Ruler::distance($caster, $closest) > 8)
            return null;
        return $closest;
    }
}