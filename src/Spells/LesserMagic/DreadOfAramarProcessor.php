<?php

namespace Mordheim\Spells\LesserMagic;

use Mordheim\Battle;
use Mordheim\Data\Spell;
use Mordheim\Data\Warband;
use Mordheim\Fighter;
use Mordheim\Ruler;
use Mordheim\SpecialRule;
use Mordheim\Spells\BaseSpellProcessor;

class DreadOfAramarProcessor extends BaseSpellProcessor
{
    public Spell $spell = Spell::DREAD_OF_ARAMAR;

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

        // Проверка на лидерство
        $allies = $battle->getAlliesFor($target);
        if (!\Mordheim\Rule\Psychology::leadershipTest($target, $allies)) {
            foreach ($battle->getActiveCombats()->getByFighter($target) as $combat)
                $battle->getActiveCombats()->remove($combat);

            // Провал — цель убегает на 2D6" от заклинателя
            $distance = \Mordheim\Dice::roll(6) + \Mordheim\Dice::roll(6);
            $from = $target->getState()->getPosition();
            $to = $caster->getState()->getPosition();
            // Вычисляем направление от заклинателя к цели
            $dx = $from[0] - $to[0];
            $dy = $from[1] - $to[1];
            $dz = $from[2] - $to[2];
            $len = sqrt($dx * $dx + $dy * $dy + $dz * $dz);
            if ($len == 0) $len = 1; // чтобы не делить на 0
            // Новая точка на расстоянии distance в том же направлении
            $newX = (int)round($from[0] + $distance * ($dx / $len));
            $newY = (int)round($from[1] + $distance * ($dy / $len));
            $newZ = (int)round($from[2] + $distance * ($dz / $len));
            $targetPos = [$newX, $newY, $newZ];
            try {
                \Mordheim\Rule\Move::common($battle, $target, $targetPos, 0.4);
                \Mordheim\BattleLogger::add("{$target->getName()} не прошёл тест Лидерства и убегает на {$distance}\" от {$caster->getName()} ({$this->spell->name}).");
            } catch (\Throwable $e) {
                \Mordheim\BattleLogger::add("{$target->getName()} не может убежать: " . $e->getMessage());
            }
        } else {
            \Mordheim\BattleLogger::add("{$target->getName()} прошёл тест Лидерства и не поддался страху ({$this->spell->name}).");
        }

        return true;
    }

    private function findEnemy(Battle $battle, Fighter $caster): ?Fighter
    {
        foreach ($battle->getEnemiesFor($caster) as $enemy) {
            if (Ruler::distance($caster, $enemy) > 12)
                continue;
            if ($enemy->getBlank()->getWarband() == Warband::UNDEAD)
                continue;
            if ($enemy->hasSpecialRule(SpecialRule::FEARSOME))
                continue;
            return $enemy;
        }
        return null;
    }
}