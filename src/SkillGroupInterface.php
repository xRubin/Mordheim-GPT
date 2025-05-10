<?php

namespace Mordheim;

interface SkillGroupInterface
{
    public function getSkills(WarbandInterface $warband): array;
}