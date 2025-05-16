<?php

namespace Mordheim\Spells\MagicOfTheHornedRat;

use Mordheim\Battle;
use Mordheim\Dice;
use Mordheim\Equipment;
use Mordheim\Fighter;
use Mordheim\Rule\Attack;
use Mordheim\Ruler;
use Mordheim\Spell;
use Mordheim\Spells\BaseSpellProcessor;

class WarpfireProcessor extends BaseSpellProcessor
{
    public Spell $spell = Spell::WARPFIRE;

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
        \Mordheim\BattleLogger::add("{$target->getName()} получает {$hits} попаданий силой 4 от {$this->spell->name}.");
        for ($i = 0; $i < $hits; $i++) {
            Attack::magic($battle, $caster, $target, Equipment::WARPFIRE_DIRECT);
        }

        foreach ($this->findTargets($battle, $target) as $aoeTarget) {
            \Mordheim\BattleLogger::add("{$target->getName()} получает урон силой 3 от {$this->spell->name}.");
            Attack::magic($battle, $caster, $aoeTarget, Equipment::WARPFIRE_AOE);
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
        if (Ruler::distance($caster, $closest) > 8)
            return null;
        return $closest;
    }

    private function findTargets(Battle $battle, Fighter $directTarget): \Generator
    {
        foreach ($battle->getFighters() as $target) {
            if (Ruler::distance($directTarget, $target) <= 2) {
                if ($target === $directTarget)
                    continue;
                yield $target;
            }
        }
    }
}