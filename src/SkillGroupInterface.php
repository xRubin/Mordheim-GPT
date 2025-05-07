<?php

namespace Mordheim;

interface SkillGroupInterface
{
    public function getTitle(): string;
    public function getSkills(): array;
}