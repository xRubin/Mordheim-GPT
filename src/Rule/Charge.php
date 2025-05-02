<?php

namespace Mordheim\Rule;

use Mordheim\CloseCombat;
use Mordheim\Fighter;
use Mordheim\GameField;
use Mordheim\Battle;

class Charge
{
    /**
     * Выполнить попытку charge (атаки с разбега) по правилам Mordheim 1999
     * Возвращает объект CloseCombat при успехе
     * @param Battle $battle
     * @param Fighter $attacker
     * @param Fighter $defender
     * @param array $otherUnits
     * @return CloseCombat|null
     */
    public static function attempt(Battle $battle, Fighter $attacker, Fighter $defender, array $otherUnits = []): ?CloseCombat
    {
        // Charge запрещён, если атакующий уже вовлечён в ближний бой
        if ($battle->getActiveCombats()->isFighterInCombat($attacker)) {
            \Mordheim\BattleLogger::add("{$attacker->name} не может объявить charge: уже вовлечён в ближний бой.");
            return null;
        }

        $targetPos = self::getNearestChargePosition($battle, $attacker, $defender);
        if (null === $targetPos) {
            \Mordheim\BattleLogger::add("{$attacker->name} не может совершить charge: зона вокруг с целью заблокирована.");
            return null;
        }

        // Проверить, хватает ли движения
        $moveMultiplier = $attacker->hasSkill('Sprint') ? 3 : 2;
        $movePoints = $attacker->getMovement() * $moveMultiplier;
        \Mordheim\BattleLogger::add("{$attacker->name} может бежать на: $movePoints, множитель: $moveMultiplier");
        $path = \Mordheim\PathFinder::findPath($battle->getField(), $attacker->position, $targetPos, $attacker->getMovementWeights(), array_map(fn($u) => $u->position, $otherUnits));
        if (!$path || count($path) < 2) {
            \Mordheim\BattleLogger::add("{$attacker->name} не может совершить charge: путь к цели заблокирован.");
            return null;
        }
        $cost = $path[count($path)-1]['cost'];
        if ($cost > $movePoints) {
            \Mordheim\BattleLogger::add("{$attacker->name} не может совершить charge: не хватает движения (нужно $cost, есть $movePoints).");
            return null;
        }
        // Переместить бойца
        $attacker->position = $targetPos;
        \Mordheim\BattleLogger::add("{$attacker->name} совершает charge на {$defender->name}! Перемещён на [".implode(',', $targetPos)."]");
        return new CloseCombat($attacker, $defender);
    }

    /**
     * TODO: obstacles
     * @param Battle $battle
     * @param Fighter $attacker
     * @param Fighter $defender
     * @return array|null
     */
    protected static function getNearestChargePosition(Battle $battle, Fighter $attacker, Fighter $defender): ?array
    {
        // Определить клетки adjacent к цели
        $adjacent = self::getAdjacentPositions($defender->position);
        $minDist = INF;
        $targetPos = null;
        foreach ($adjacent as $pos) {
            $dist = self::distance($attacker->position, $pos);
            if ($dist < $minDist) {
                $minDist = $dist;
                $targetPos = $pos;
            }
        }
        return $targetPos;
    }

    /**
     * Получить список позиций, смежных с данной
     */
    protected static function getAdjacentPositions(array $position): array
    {
        $adj = [];
        for ($dx = -1; $dx <= 1; $dx++) {
            for ($dy = -1; $dy <= 1; $dy++) {
                for ($dz = -1; $dz <= 1; $dz++) {
                    if ($dx === 0 && $dy === 0 && $dz === 0) continue;
                    $adj[] = [$position[0]+$dx, $position[1]+$dy, $position[2]+$dz];
                }
            }
        }
        return $adj;
    }

    protected static function distance(array $a, array $b): float
    {
        return sqrt(pow($a[0]-$b[0],2)+pow($a[1]-$b[1],2)+pow($a[2]-$b[2],2));
    }
}
