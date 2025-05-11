<?php

namespace Mordheim;

interface HasSpecialRuleInterface
{
    public function hasSpecialRule(SpecialRule $specialRule): bool;
}