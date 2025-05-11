<?php

namespace Mordheim;

interface SkillGroupInterface
{
    public function getSpecialRules(WarbandInterface $warband): array;
}