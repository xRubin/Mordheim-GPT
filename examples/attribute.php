<?php
require_once __DIR__ . '/../vendor/autoload.php';

var_dump(\Mordheim\Data\Weapon::FIST->getStrength(3));
var_dump(\Mordheim\Data\Weapon::FIST->getSpecialRules());
var_dump(\Mordheim\Data\Weapon::GROMRIL_FLAIL->getSpecialRules());
var_dump(\Mordheim\Data\SpecialRule::tryFromName('PARRY'));
var_dump(\Mordheim\Data\SpecialRule::tryFrom(3));
