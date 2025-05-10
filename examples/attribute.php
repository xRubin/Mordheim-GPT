<?php
require_once __DIR__ . '/../vendor/autoload.php';

var_dump(\Mordheim\Data\Warband::MARIENBURG->getBlanks());

var_dump(\Mordheim\Data\Equipment::FIST->getStrength(3));
var_dump(\Mordheim\Data\Equipment::FIST->getSpecialRules());
var_dump(\Mordheim\Data\Equipment::GROMRIL_FLAIL->getSpecialRules());
var_dump(Mordheim\SpecialRule::tryFromName('PARRY'));

var_dump(\Mordheim\Data\Equipment::HELMET->getSlot());

var_dump(\Mordheim\Data\Blank::VESKIT->getAllowedWarbands());
var_dump(\Mordheim\Data\Blank::AENUR->getAllowedWarbands());