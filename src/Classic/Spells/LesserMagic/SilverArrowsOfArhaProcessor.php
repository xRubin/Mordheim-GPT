<?php

namespace Mordheim\Classic\Spells\LesserMagic;

use Mordheim\Classic\Battle;
use Mordheim\Classic\Equipment;
use Mordheim\Classic\Fighter;
use Mordheim\Classic\Rule\Attack;
use Mordheim\Classic\Rule\Dodge;
use Mordheim\Classic\Rule\Injuries;
use Mordheim\Classic\Ruler;
use Mordheim\Classic\Spell;
use Mordheim\Classic\Spells\BaseSpellProcessor;
use Mordheim\Dice;

class SilverArrowsOfArhaProcessor extends BaseSpellProcessor
{
    public Spell $spell = Spell::SILVER_ARROWS_OF_ARHA;
    public Equipment $weapon = Equipment::SILVER_ARROW_OF_ARHA;

    public function __construct(
        public int $difficulty = 7
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

        $hits = Dice::roll(6) + 2; // D6 + 2
        [$toHit, $_] = Attack::calculateRangedParams($battle, $caster, $target, $this->weapon, false);
        \Mordheim\BattleLogger::add("{$target->getName()} получает {$hits} попаданий силой 3 от {$this->spell->name}.");
        for ($i = 0; $i < $hits; $i++) {
            if (!
            $target->getState()->getStatus()->isAlive())
                break;
            $roll = Dice::roll(6);
            if ($roll < $toHit) continue;
            if (Dodge::roll($target)) continue;
            if (!Attack::tryArmourSaveRanged($caster, $target, $this->weapon)) {
                $target->getState()->modifyWounds(-1);
                \Mordheim\BattleLogger::add("{$target->getName()} получает 1 ранение от {$this->weapon->name}.");
                Injuries::rollIfNoWounds($battle, $caster, $target, $this->weapon);
            }
        }
        return true;
    }

    private function findEnemy(Battle $battle, Fighter $caster): ?Fighter
    {
        if ($battle->getActiveCombats()->isFighterInCombat($caster))
            return null;
        foreach ($battle->getEnemiesFor($caster) as $enemy) {
            if (Ruler::distance($caster, $enemy) < 1.5)
                continue;
            if (Ruler::distance($caster, $enemy) > 24)
                continue;
            return $enemy;
        }
        return null;
    }
}