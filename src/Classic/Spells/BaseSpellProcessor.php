<?php

namespace Mordheim\Classic\Spells;

use Mordheim\BattleLogger;
use Mordheim\Classic\Battle;
use Mordheim\Classic\Fighter;
use Mordheim\Classic\Rule\Psychology;
use Mordheim\Classic\Spell;
use Mordheim\Dice;

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
            BattleLogger::add("{$this->spell->name} спадает с {$caster->getName()} (бросок: $roll)");
            $battle->removeActiveSpell($caster, $this->spell);
            return true;
        } else {
            BattleLogger::add("{$this->spell->name} остаётся на {$caster->getName()} (бросок: $roll)");
            return false;
        }
    }

    /**
     * false = failed
     * true = success
     */
    public function rollSpellApplied(Battle $battle, Fighter $caster): bool
    {
        if ($this->difficulty <= 1) {
            BattleLogger::add("{$caster->getName()} автоматически применяет заклинание {$this->spell->name}!");
            return true;
        }

        $roll = Dice::roll(6) + Dice::roll(6);

        BattleLogger::add("{$caster->getName()} бросает 2d6=$roll для заклинания {$this->spell->name} (сложность {$this->difficulty})");
        if ($roll < $this->difficulty) {
            BattleLogger::add("{$caster->getName()} не смог применить заклинание {$this->spell->name}.");
            return false; // failed
        }
        BattleLogger::add("{$caster->getName()} применяет заклинание {$this->spell->name}!");
        return true;
    }

    public function runFromCaster(Battle $battle, Fighter $caster, Fighter $target): void
    {
        $allies = $battle->getAlliesFor($target);
        if (!Psychology::leadershipTest($target, $allies)) {
            foreach ($battle->getActiveCombats()->getByFighter($target) as $combat)
                $battle->getActiveCombats()->remove($combat);

            // Провал — цель убегает на 2D6" от заклинателя
            $distance = Dice::roll(6) + Dice::roll(6);
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
            $newX = max(0, min($battle->getField()->getWidth() - 1, $newX));
            $newY = max(0, min($battle->getField()->getLength() - 1, $newY));
            $newZ = max(0, min($battle->getField()->getHeight() - 1, $newZ));
            $targetPos = [$newX, $newY, $newZ];
            // Логирование координат
            BattleLogger::add("runFromCaster: from=" . implode(',', $from) . " to=" . implode(',', $to) . " targetPos=" . implode(',', $targetPos));
            \Mordheim\Classic\Rule\Move::run($battle, $target, $targetPos, 0.4);
            BattleLogger::add("{$target->getName()} не прошёл тест Лидерства и убегает на {$distance}\" от {$caster->getName()} ({$this->spell->name}).");
        } else {
            BattleLogger::add("{$target->getName()} прошёл тест Лидерства и не поддался страху ({$this->spell->name}).");
        }
    }
}