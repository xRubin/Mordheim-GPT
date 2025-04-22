<?php
namespace Mordheim;

/**
 * BattleLogger — простой статический логгер для ведения журнала событий боя.
 * Позволяет добавлять сообщения, очищать лог, получать все сообщения и выводить их.
 */
class BattleLogger
{
    /** @var string[] */
    private static array $log = [];

    /**
     * Добавить сообщение в журнал боя
     * @param string $message
     */
    public static function add(string $message): void
    {
        self::$log[] = $message;
    }

    /**
     * Получить все сообщения лога
     * @return string[]
     */
    public static function getAll(): array
    {
        return self::$log;
    }

    /**
     * Очистить журнал боя
     */
    public static function clear(): void
    {
        self::$log = [];
    }

    /**
     * Вывести журнал боя в консоль (для CLI)
     */
    public static function print(): void
    {
        foreach (self::$log as $line) {
            echo $line . PHP_EOL;
        }
    }
}
