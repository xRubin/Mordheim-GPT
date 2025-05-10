<?php

namespace Mordheim\Rule;

use Mordheim\Battle;
use Mordheim\FighterInterface;
use Mordheim\Slot;
use Mordheim\SpecialRule;

class Shoot
{
    /**
     * Стрельба по другому бойцу по правилам Mordheim
     * Учитывает Ballistic Skill, модификаторы (дальность, движение, укрытие, размер цели и т.д.)
     */
    public static function apply(Battle $battle, FighterInterface $source, FighterInterface $target, bool $moved = false): bool
    {
        if (!$source->getState()->getStatus()->isAlive() || !$target->getState()->getStatus()->isAlive()) return false;
        $weapons = $source->getEquipmentManager()->getItemsBySlot(Slot::RANGED);
        if (!($weapon = reset($weapons))) return false;
        if ($source->getDistance($target) > $weapon->getRange()) return false;
        // Move Or Fire: если оружие содержит спецправило, нельзя стрелять после движения
        if ($moved && $weapon->hasSpecialRule(SpecialRule::MOVE_OR_FIRE)) {
            \Mordheim\BattleLogger::add("{$source->getName()} не может стрелять из {$weapon->getName()} после движения (Move or Fire).");
            return false;
        }

        $bs = $source->getBallisticSkill();
        // Mordheim: 2=6+, 3=5+, 4=4+, 5=3+, 6=2+
        $toHitBase = 7 - $bs;
        if ($toHitBase < 2) $toHitBase = 2;
        if ($toHitBase > 6) $toHitBase = 6;

        // Навык Quick Shot: если есть, стреляет дважды, если не двигался
        $shots = ($source->hasSpecialRule(SpecialRule::QUICK_SHOT) && !$moved) ? 2 : 1;

        // Модификаторы
        $mod = 0;
        // Дальний выстрел (больше половины дистанции)
        if ($source->getDistance($target) > $weapon->getRange() / 2) $mod += 1;
        // Двигался
        if ($moved) $mod += 1;
        // Цель в укрытии
        if ($battle->hasObstacleBetween($source->getState()->getPosition(), $target->getState()->getPosition())) $mod += 1;
        // Большая цель
        if ($target->hasSpecialRule(SpecialRule::LARGE_TARGET)) $mod -= 1;
        // Модификатор оружия
        $mod += $source->getHitModifier($weapon);

        $toHit = $toHitBase + $mod;
        if ($toHit > 6) $toHit = 6;
        if ($toHit < 2) $toHit = 2;

        $hit = false;
        for ($i = 0; $i < $shots; $i++) {
            $roll = \Mordheim\Dice::roll(6);
            // Критическое попадание: 6 на попадание — автоматическое ранение, сейв невозможен
            if ($roll == 6) {
                $target->getState()->decreaseWounds();
                if ($target->getState()->getWounds() <= 0) {
                    $battle->killFighter($target);
                }
                $hit = true;
                continue;
            }
            if ($roll >= $toHit) {
                // Навык Dodge: 5+ save против стрельбы
                $hasDodge = $target->hasSpecialRule(SpecialRule::DODGE);
                if ($hasDodge && \Mordheim\Dice::roll(6) >= 5) continue; // уклонился

                $armorSave = $target->getArmorSave($weapon);
                $saveRoll = $armorSave > 0 ? \Mordheim\Dice::roll(6) : 7;
                if ($saveRoll < $armorSave) {
                    $target->getState()->decreaseWounds();
                    if ($target->getState()->getWounds() <= 0) {
                        $battle->killFighter($target);
                    }
                    $hit = true;
                }
            }
        }
        return $hit;
    }
}