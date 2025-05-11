<?php

namespace Mordheim;

interface FighterInterface extends CharacteristicsInterface, HasSpecialRuleInterface, FighterSecondaryCharacteristicsInterface
{
    public function getName(): string;
    public function getEquipmentManager(): EquipmentManager;
    public function getState(): ?FighterStateInterface;
    public function getBlank(): BlankInterface;
    public function getAdvancement(): FighterAdvancementInterface;

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
     * Максимальная дистанция для обычного передвижения
     */
    public function getMoveRange(): int;

    /**
     * Максимальная диистанция для бега
     */
    public function getRunRange(): int;

    /**
     * Максимальная дистанция для Charge
     */
    public function getChargeRange(): int;
}


