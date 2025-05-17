<?php

namespace Mordheim;

enum Warband
{
    use EnumTryFromNameTrait;

    case REIKLAND;
    case MIDDENHEIM;
    case MARIENBURG;
    case CULT_OF_THE_POSSESSED;
    case WITCH_HUNTERS;
    case SISTERS_OF_SIGMAR;
    case UNDEAD;
    case SKAVEN;
    case HIRED_SWORDS;

    public function getBlanks(): array
    {
        return array_filter(
            (new \ReflectionClass(Blank::class))->getConstants(),
            fn($value) => (new \ReflectionClassConstant(Blank::class, $value->name))
                    ->getAttributes(Attributes\Warband::class)[0]
                    ->newInstance()
                    ->getValue() === $this
        );
    }

    public function getStartWealth(): int
    {
        return match ($this) {
            self::MARIENBURG => 600,
            default => 500,
        };
    }

    public function getMaxFighters(): int
    {
        return match ($this) {
            default => 15,
        };
    }
}