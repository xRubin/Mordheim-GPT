<?php

namespace Mordheim;

interface FighterSecondaryCharacteristicsInterface
{
    /**
     * Получить итоговую инициативу для лазания
     */
    public function getClimbInitiative(): int;

    /**
     * Расчет весов для алгоритма передвижения
     */
    public function getMovementWeights(): callable;

    /**
     * Расчёт сейва с учётом всей экипировки
     */
    public function getArmorSave(?EquipmentInterface $attackerWeapon): int;
    public function getHitModifier(?EquipmentInterface $attackerWeapon): int;

    /**
     * Максимальная диистанция для бега
     */
    public function getRunRange(): int;

    /**
     * Максимальная дистанция для Charge
     */
    public function getChargeRange(): int;
}


