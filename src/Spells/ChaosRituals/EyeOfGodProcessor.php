<?php

namespace Mordheim\Spells\ChaosRituals;

use Mordheim\Battle;
use Mordheim\Data\Spell;
use Mordheim\Dice;
use Mordheim\Fighter;
use Mordheim\Ruler;
use Mordheim\Spells\BaseSpellProcessor;

class EyeOfGodProcessor extends BaseSpellProcessor
{
    public Spell $spell = Spell::EYE_OF_GOD;

    public function __construct(
        public int $difficulty = 7
    )
    {

    }

    public function onPhaseMagic(Battle $battle, Fighter $caster): ?bool
    {
        if ($caster->getState()->hasActiveSpell($this->spell))
            return null;

        $target = $this->findAlly($battle, $caster);
        if (!$target) {
            \Mordheim\BattleLogger::add("Нет подходящей цели для {$this->spell->name}.");
            return null;
        }

        if (!parent::rollSpellApplied($battle, $caster))
            return false;

        $roll = Dice::roll(6);
        if ($roll == 1) {
            $battle->killFighter($target);
            \Mordheim\BattleLogger::add("{$target->getName()} поражён гневом богов ({$this->spell->name}) и выбывает из боя!");
        } elseif ($roll >= 2 && $roll <= 5) {
            // +1 к одной характеристике
            $characteristics = [
                'movement' => $target->getMovement(),
                'weaponSkill' => $target->getWeaponSkill(),
                'ballisticSkill' => $target->getBallisticSkill(),
                'strength' => $target->getStrength(),
                'toughness' => $target->getToughness(),
                'initiative' => $target->getInitiative(),
                'attacks' => $target->getAttacks(),
                'leadership' => $target->getLeadership(),

            ];
            arsort($characteristics);
            $selected = array_key_first($characteristics);
            $target->getAdvancement()->getCharacteristics()->$selected += 1;
            \Mordheim\BattleLogger::add("{$target->getName()} получает +1 к {$selected} до конца боя ({$this->spell->name}).");
        } else {
            // +1 ко всем характеристикам
            foreach ([
                         'movement', 'weaponSkill', 'ballisticSkill', 'strength', 'toughness', 'wounds', 'initiative', 'attacks', 'leadership'
                     ] as $char) {
                if (property_exists($target->getAdvancement()->getCharacteristics(), $char)) {
                    $target->getAdvancement()->getCharacteristics()->$char += 1;
                }
            }
            \Mordheim\BattleLogger::add("{$target->getName()} получает +1 ко всем характеристикам до конца боя ({$this->spell->name})!");
        }

        $battle->addActiveSpell($caster, $this->spell);
        return true;
    }

    private function findAlly(Battle $battle, Fighter $caster): ?Fighter
    {
        foreach ($battle->getAlliesFor($caster) as $target) {
            if (Ruler::distance($caster, $target) <= 6)
                return $target;
        }
        return null;
    }
}