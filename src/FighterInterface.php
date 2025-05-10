<?php

namespace Mordheim;

interface FighterInterface
{
    public function getName(): string;
    public function getEquipmentManager(): EquipmentManager;
    public function getState(): ?FighterStateInterface;

    /**
     * Получить итоговое движение с учетом экипировки
     */
    public function getMovement(): int;
    public function getWeaponSkill(): int;
    public function getBallisticSkill(): int;
    public function getToughness(): int;
    public function getInitiative(): int;
    public function getLeadership(): int;

    public function getStrength(): int;

    /**
     * Итоговое количество атак с учётом экипировки (два одноручных оружия = +1 атака)
     */
    public function getAttacks(): int;

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

    /**
     * Проверка навыка
     */
    public function hasSpecialRule(SpecialRule $specialRule): bool;

    public function isAdjacent(FighterInterface $target): bool;
    public function getDistance(FighterInterface $target): bool;
}


