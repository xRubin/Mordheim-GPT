<?php
namespace Mordheim;

class Dice
{
    private static array $testRolls = [];

    /**
     * Установить последовательность бросков для тестов
     */
    public static function setTestRolls(array $rolls): void
    {
        self::$testRolls = $rolls;
    }

    /**
     * Получить текущий массив тестовых бросков
     */
    public static function getTestRolls(): array
    {
        return self::$testRolls;
    }

    /**
     * Бросок одного кубика с указанным числом граней (по умолчанию d6)
     */
    public static function roll(int $sides = 6): int
    {
        if (!empty(self::$testRolls)) {
            return array_shift(self::$testRolls);
        }
        return random_int(1, $sides);
    }

    /**
     * Бросок нескольких кубиков
     */
    public static function rollMany(int $count, int $sides = 6): array
    {
        $results = [];
        for ($i = 0; $i < $count; $i++) {
            $results[] = self::roll($sides);
        }
        return $results;
    }

    /**
     * Проверка успеха броска (например, 4+ на d6)
     */
    public static function check(int $target, int $sides = 6): bool
    {
        return self::roll($sides) >= $target;
    }
}
