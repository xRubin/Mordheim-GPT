<?php

namespace Mordheim\Classic\Spells\ChaosRituals;

use Mordheim\Characteristics;
use Mordheim\Classic\Battle;
use Mordheim\Classic\Fighter;
use Mordheim\Classic\Ruler;
use Mordheim\Classic\Spell;
use Mordheim\Classic\Spells\BaseSpellProcessor;
use Mordheim\Dice;

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
                'movement' => $target->getMovement(false),
                'weaponSkill' => $target->getWeaponSkill(false),
                'ballisticSkill' => $target->getBallisticSkill(false),
                'strength' => $target->getStrength(false),
                'toughness' => $target->getToughness(false),
                'initiative' => $target->getInitiative(false),
                'attacks' => $target->getAttacks(false),
                'leadership' => $target->getLeadership(false),

            ];
            arsort($characteristics);
            $char = array_key_first($characteristics);
            $target->getState()->getCharacteristics()->setByName($char, $target->getState()->getCharacteristics()->getByName($char) + 1);
            \Mordheim\BattleLogger::add("{$target->getName()} получает +1 к {$char} до конца боя ({$this->spell->name}).");
        } else {
            // +1 ко всем характеристикам
            $target->getState()->setCharacteristics(
                $target->getState()->getCharacteristics()->add(
                    new Characteristics(1,1,1,1,1,1,1,1,1)
                )
            );
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