<?php

namespace Mordheim\Rule;

use Mordheim\CloseCombat;
use Mordheim\Fighter;
use Mordheim\GameField;

class Charge
{
    /**
     * Выполнить попытку charge (атаки с разбега) по правилам Mordheim 1999
     * Возвращает объект CloseCombat при успехе
     * @param GameField $field
     * @param Fighter $attacker
     * @param Fighter $defender
     * @param array $otherUnits
     * @return CloseCombat|null
     */
    public static function attempt(GameField $field, Fighter $attacker, Fighter $defender, array $otherUnits = []): ?CloseCombat
    {
        // Charge запрещён, если атакующий уже вовлечён в ближний бой
        if (self::isEngaged($attacker, $otherUnits)) {
            \Mordheim\BattleLogger::add("{$attacker->name} не может объявить charge: уже вовлечён в ближний бой.");
            return null;
        }
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
        // Проверить, хватает ли движения (без Sprint!)
        $movePoints = $attacker->getMovement();
        $path = \Mordheim\PathFinder::findPath($field, $attacker->position, $targetPos, $attacker->getMovementWeights(), array_map(fn($u) => $u->position, $otherUnits));
        if (!$path || count($path) < 2) {
            \Mordheim\BattleLogger::add("{$attacker->name} не может совершить charge: путь к цели заблокирован.");
            return null;
        }
        $cost = $path[count($path)-1]['cost'];
        if ($cost > $movePoints + 1e-6) {
            \Mordheim\BattleLogger::add("{$attacker->name} не может совершить charge: не хватает движения (нужно $cost, есть $movePoints).");
            return null;
        }
        // Переместить бойца
        $attacker->position = $targetPos;
        \Mordheim\BattleLogger::add("{$attacker->name} совершает charge на {$defender->name}! Перемещён на [".implode(',', $targetPos)."]");
        return new CloseCombat($attacker, $defender);
    }

    /**
     * Проверить, вовлечён ли боец в рукопашную (есть ли adjacent враги)
     */
    public static function isEngaged(Fighter $fighter, array $otherUnits): bool
    {
        foreach ($otherUnits as $unit) {
            if ($unit !== $fighter && $unit->alive && $fighter->isAdjacent($unit)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Получить список позиций, смежных с данной
     */
    public static function getAdjacentPositions(array $position): array
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

    public static function distance(array $a, array $b): float
    {
        return sqrt(pow($a[0]-$b[0],2)+pow($a[1]-$b[1],2)+pow($a[2]-$b[2],2));
    }
}
