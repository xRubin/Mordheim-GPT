<?php

namespace Mordheim\Spells\PrayersOfSigmar;

use Mordheim\Battle;
use Mordheim\Data\Spell;
use Mordheim\Fighter;
use Mordheim\Ruler;
use Mordheim\Spells\BaseSpellProcessor;
use Mordheim\Status;

class HeartsOfSteelProcessor extends BaseSpellProcessor
{
    public Spell $spell = Spell::HEARTS_OF_STEEL;
    public Spell $effect = Spell::HEARTS_OF_STEEL_TARGET;

    public function __construct(
        public int $difficulty = 8
    )
    {

    }

    public function onPhaseMagic(Battle $battle, Fighter $caster): ?bool
    {
        if (!parent::rollSpellApplied($battle, $caster))
            return false;

        $battle->addActiveSpell($caster, $this->spell);

        foreach ($battle->getAlliesFor($caster) as $ally) {
            if (Ruler::distance($caster, $ally) <= 8) {
                $battle->addActiveSpell($ally, $this->effect);
                \Mordheim\BattleLogger::add("{$ally->getName()} получает иммунитет к страху и тестам одиночества (через {$this->spell->name}).");
            }
        }
        // TODO: $warband->heartsOfSteelRoutBonus = 1;
        // \Mordheim\BattleLogger::add("Вся банда {$warband->name} получает +1 к тесту на бегство.");
        return true;
    }

    public function onCasterStatusChange(Battle $battle, Fighter $caster): void
    {
        if (!in_array($caster->getState()->getStatus(), [Status::KNOCKED_DOWN, Status::STUNNED, Status::OUT_OF_ACTION]))
            return;

        foreach ($battle->getAlliesFor($caster) as $ally) {
            if ($ally->getState()->hasActiveSpell($this->effect))
                $battle->removeActiveSpell($ally, $this->spell);
        }

        $battle->removeActiveSpell($caster, $this->spell);
    }
}