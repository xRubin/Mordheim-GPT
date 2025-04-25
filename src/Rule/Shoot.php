<?php

namespace Mordheim\Rule;

use Mordheim\Fighter;

class Shoot
{
    /**
     * Стрельба по другому бойцу по правилам Mordheim
     * Учитывает Ballistic Skill, модификаторы (дальность, движение, укрытие, размер цели и т.д.)
     */
    public static function apply(Fighter $source, Fighter $target, bool $moved = false, bool $targetInCover = false, bool $targetIsLarge = false, int $shots = 1): bool
    {
        if (!$source->alive || !$target->alive) return false;
        $ranged = null;
        foreach ($source->equipmentManager->getWeapons() as $w) {
            if ($w->damageType === 'Ranged') {
                $ranged = $w;
                break;
            }
        }
        if (!$ranged) return false;
        if ($source->distance($target) > $ranged->range) return false;
        // Move Or Fire: если оружие содержит спецправило, нельзя стрелять после движения
        if ($moved && $ranged->hasRule(\Mordheim\SpecialRule::MOVE_OR_FIRE)) {
            \Mordheim\BattleLogger::add("{$source->name} не может стрелять из {$ranged->name} после движения (Move or Fire).");
            return false;
        }

        $bs = $source->characteristics->ballisticSkill;
        // Mordheim: 2=6+, 3=5+, 4=4+, 5=3+, 6=2+
        $toHitBase = 7 - $bs;
        if ($toHitBase < 2) $toHitBase = 2;
        if ($toHitBase > 6) $toHitBase = 6;

        // Модификаторы
        $mod = 0;
        // Дальний выстрел (больше половины дистанции)
        if ($source->distance($target) > $ranged->range / 2) $mod += 1;
        // Двигался
        if ($moved) $mod += 1;
        // Цель в укрытии
        if ($targetInCover) $mod += 1;
        // Множественный выстрел
        if ($shots > 1) $mod += 1;
        // Большая цель
        if ($targetIsLarge) $mod -= 1;
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
                $target->characteristics->wounds -= 1;
                if ($target->characteristics->wounds <= 0) {
                    $target->alive = false;
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
                    $target->characteristics->wounds -= 1;
                    if ($target->characteristics->wounds <= 0) {
                        $target->alive = false;
                    }
                    $hit = true;
                }
            }
            // Навык Quick Shot: если есть, стреляет дважды, если не двигался
            $hasQuickShot = $source->hasSkill('Quick Shot');
            if ($hasQuickShot && $shots == 1 && !$moved) {
                $shots = 2;
            }
        }
        return $hit;
    }
}