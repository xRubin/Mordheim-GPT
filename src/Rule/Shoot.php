<?php

namespace Mordheim\Rule;

use Mordheim\Battle;
use Mordheim\FighterInterface;

class Shoot
{
    /**
     * Стрельба по другому бойцу по правилам Mordheim
     * Учитывает Ballistic Skill, модификаторы (дальность, движение, укрытие, размер цели и т.д.)
     */
    public static function apply(Battle $battle, FighterInterface $source, FighterInterface $target, bool $moved = false): bool
    {
        if (!$source->getState()->getStatus()->isAlive() || !$target->getState()->getStatus()->isAlive()) return false;
        $ranged = null;
        foreach ($source->getEquipmentManager()->getWeapons() as $w) {
            if ($w->damageType === 'Ranged') {
                $ranged = $w;
                break;
            }
        }
        if (!$ranged) return false;
        if ($source->getDistance($target) > $ranged->range) return false;
        // Move Or Fire: если оружие содержит спецправило, нельзя стрелять после движения
        if ($moved && $ranged->hasRule(\Mordheim\SpecialRule::MOVE_OR_FIRE)) {
            \Mordheim\BattleLogger::add("{$source->getName()} не может стрелять из {$ranged->name} после движения (Move or Fire).");
            return false;
        }

        $bs = $source->getBallisticSkill();
        // Mordheim: 2=6+, 3=5+, 4=4+, 5=3+, 6=2+
        $toHitBase = 7 - $bs;
        if ($toHitBase < 2) $toHitBase = 2;
        if ($toHitBase > 6) $toHitBase = 6;

        // Навык Quick Shot: если есть, стреляет дважды, если не двигался
        $shots = ($source->hasSkill('Quick Shot') && !$moved) ? 2 : 1;

        // Модификаторы
        $mod = 0;
        // Дальний выстрел (больше половины дистанции)
        if ($source->getDistance($target) > $ranged->range / 2) $mod += 1;
        // Двигался
        if ($moved) $mod += 1;
        // Цель в укрытии
        if ($battle->hasObstacleBetween($source->getState()->getPosition(), $target->getState()->getPosition())) $mod += 1;
        // Большая цель
        if ($target->hasSkill('Large Target')) $mod -= 1;
        // Модификатор оружия
        $mod += $ranged->toHitModifier;

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
                $hasDodge = $target->hasSkill('Dodge');
                if ($hasDodge && \Mordheim\Dice::roll(6) >= 5) continue; // уклонился

                // Особые эффекты оружия: игнор сейва/повтор броска
                $ignoreSave = $ranged->hasRule(\Mordheim\SpecialRule::IGNORE_ARMOR_SAVE);

                $armorSave = $ignoreSave ? 0 : $target->getArmorSave($ranged);
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