<?php
namespace Mordheim;

use Mordheim\Fighter;
use Mordheim\Skill;

class Advancement
{
    /**
     * Провести повышение бойца по правилам кампании Mordheim
     * Возвращает строку-описание повышения или null
     */
    /**
     * Расширенная таблица максимальных характеристик (пример, можно расширить)
     */
    private static array $maxStatsHero = [
        'movement' => 6,
        'weaponSkill' => 7,
        'ballisticSkill' => 7,
        'strength' => 4,
        'toughness' => 4,
        'wounds' => 3,
        'initiative' => 7,
        'leadership' => 9
    ];
    private static array $maxStatsHenchman = [
        'movement' => 4,
        'weaponSkill' => 4,
        'ballisticSkill' => 4,
        'strength' => 4,
        'toughness' => 4,
        'wounds' => 2,
        'initiative' => 6,
        'leadership' => 7
    ];

    /**
     * Основной метод повышения
     */
    public static function advance(Fighter $fighter): ?string
    {
        if (!$fighter->canAdvance()) return null;
        // Определяем тип юнита (герой или henchman)
        $isHero = property_exists($fighter, 'isHero') ? $fighter->isHero : true;
        $maxStats = $isHero ? self::$maxStatsHero : self::$maxStatsHenchman;
        // Таблица повышения по Mordheim
        // 2-4: +характеристика, 5-6: новый навык (герой), 5-6: +характеристика (henchman)
        $roll = \Mordheim\Dice::roll(6) + \Mordheim\Dice::roll(6);
        $result = '';
        if ($isHero) {
            if ($roll <= 4) {
                // Повышение характеристики
                $categories = self::getAvailableStatCategories($fighter, $maxStats, true);
                if (empty($categories)) return $fighter->name . ': нет доступных характеристик для повышения';
                $cat = $categories[array_rand($categories)];
                $fighter->characteristics->$cat++;
                $result = $fighter->name . ' повысил характеристику: ' . $cat;
            } else {
                // Новый навык
                if (count($fighter->skills) < $fighter->maxSkills) {
                    $newSkill = new Skill('Random Skill ' . uniqid(), uniqid());
                    $fighter->addSkill($newSkill);
                    $result = $fighter->name . ' получил новый навык: ' . $newSkill->name;
                } else {
                    // Если навыков максимум — повышение характеристики
                    $categories = self::getAvailableStatCategories($fighter, $maxStats, true);
                    if (empty($categories)) return $fighter->name . ': нет доступных характеристик для повышения';
                    $cat = $categories[array_rand($categories)];
                    $fighter->characteristics->$cat++;
                    $result = $fighter->name . ' повысил характеристику: ' . $cat . ' (навыков максимум)';
                }
            }
        } else {
            // Henchmen: всегда +характеристика, навык только если это группа и все могут получить
            $categories = self::getAvailableStatCategories($fighter, $maxStats, false);
            if (empty($categories)) return $fighter->name . ': нет доступных характеристик для повышения';
            $cat = $categories[array_rand($categories)];
            $fighter->characteristics->$cat++;
            $result = $fighter->name . ' повысил характеристику: ' . $cat;
        }
        return $result;
    }

    /**
     * Возвращает список категорий характеристик, которые можно повысить (ещё не максимум)
     * $isHero — если true, разрешает wounds, leadership; если false — только базовые
     */
    private static function getAvailableStatCategories(Fighter $fighter, array $maxStats, bool $isHero): array
    {
        $fields = ['movement','weaponSkill','ballisticSkill','strength','toughness','initiative'];
        if ($isHero) {
            $fields[] = 'wounds';
            $fields[] = 'leadership';
        }
        $result = [];
        foreach ($fields as $field) {
            if ($fighter->characteristics->$field < ($maxStats[$field] ?? 99)) {
                $result[] = $field;
            }
        }
        return $result;
    }
}
