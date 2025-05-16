<?php

namespace Mordheim\Rule;

class Random
{
    public static function fromArray(array $array): mixed
    {
        shuffle($array);
        return array_shift($array);
    }
}