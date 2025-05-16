<?php

namespace Mordheim;

trait EnumTryFromNameTrait
{
    public static function tryFromName(string $name): ?static
    {
        $reflection = new \ReflectionEnum(static::class);

        if (!$reflection->hasCase($name))
            return null;

        return $reflection->getCase($name)->getValue();
    }
}