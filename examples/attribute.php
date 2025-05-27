<?php
require_once __DIR__ . '/../vendor/autoload.php';

var_dump(\Mordheim\Warband::MARIENBURG->getBlanks());

var_dump(Mordheim\Equipment::FIST->getStrength(3));
var_dump(Mordheim\Equipment::FIST->getSpecialRules());
var_dump(Mordheim\Equipment::GROMRIL_FLAIL->getSpecialRules());
var_dump(Mordheim\SpecialRule::tryFromName('PARRY'));

var_dump(Mordheim\Equipment::HELMET->getSlot());

var_dump(Mordheim\Classic\Blank::VESKIT->getAllowedWarbands());
var_dump(Mordheim\Classic\Blank::AENUR->getAllowedWarbands());