<?php

namespace Mordheim\Rule;

use Mordheim\Battle;
use Mordheim\CloseCombat;
use Mordheim\Exceptions\ChargeFailedException;
use Mordheim\Fighter;
use Mordheim\Ruler;

class Charge
{
    /**
     * Выполнить попытку charge (атаки с разбега) по правилам Mordheim 1999
     * Возвращает объект CloseCombat при успехе
     * @param Battle $battle
     * @param Fighter $attacker
     * @param Fighter $defender
     * @param float $aggressiveness
     * @param array $otherUnits
     * @return CloseCombat|null
     * @throws ChargeFailedException
     */
    public static function attempt(Battle $battle, Fighter $attacker, Fighter $defender, float $aggressiveness, array $otherUnits = []): ?CloseCombat
    {
        // Charge запрещён, если атакующий уже вовлечён в ближний бой
        if ($battle->getActiveCombats()->isFighterInCombat($attacker)) {
            \Mordheim\BattleLogger::add("{$attacker->name} не может объявить charge: уже вовлечён в ближний бой.");
            throw new ChargeFailedException();
        }

        $targetPos = self::getNearestChargePosition($battle, $attacker, $defender);
        if (null === $targetPos) {
            \Mordheim\BattleLogger::add("{$attacker->name} не может совершить charge: зона вокруг с целью заблокирована.");
            throw new ChargeFailedException();
        }

        // Проверить, хватает ли движения
        $movePoints = $attacker->getChargeRange();
        $path = \Mordheim\PathFinder::findPath($battle->getField(), $attacker->position, $targetPos, $attacker->getMovementWeights(), array_map(fn($u) => $u->position, $aggressiveness, $otherUnits));
        if (!$path || count($path) < 2) {
            \Mordheim\BattleLogger::add("{$attacker->name} не может совершить charge: путь к цели заблокирован.");
            throw new ChargeFailedException();
        }
        $cost = $path[count($path) - 1]['cost'];
        if ($cost > $movePoints) {
            \Mordheim\BattleLogger::add("{$attacker->name} не может совершить charge: не хватает движения (нужно $cost, есть $movePoints).");
            throw new ChargeFailedException();
        }

        // Проверка инициативы для скрытой цели
        if (Ruler::distance($attacker->position, $defender->position) && Ruler::hasObstacleBetween($battle, $attacker->position, $defender->position)) {
            $roll = \Mordheim\Dice::roll(6);
            \Mordheim\BattleLogger::add("{$attacker->name} бросает Initiative для hidden цели: $roll против {$defender->getInitiative()}");
            if ($roll <= $defender->getInitiative()) {
                \Mordheim\BattleLogger::add("{$attacker->name} не может совершить charge: не прошёл проверку инициативы для атаки на скрытую цель.");
                throw new ChargeFailedException();
            }
            \Mordheim\BattleLogger::add("{$attacker->name} прошёл проверку инициативы для атаки на скрытую цель.");
        }

        // Переместить бойца
        $attacker->position = $targetPos;
        \Mordheim\BattleLogger::add("{$attacker->name} совершает charge на {$defender->name}! Перемещён на [" . implode(',', $targetPos) . "]");
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
        $adjacent = self::getAdjacentPositions($battle, $defender->position);
        $minDist = INF;
        $targetPos = null;
        foreach ($adjacent as $pos) {
            $dist = Ruler::distance($attacker->position, $pos);
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
    protected static function getAdjacentPositions(Battle $battle, array $position): array
    {
        $adj = [];
        for ($dx = -1; $dx <= 1; $dx++) {
            for ($dy = -1; $dy <= 1; $dy++) {
                for ($dz = -1; $dz <= 1; $dz++) {
                    if ($dx === 0 && $dy === 0 && $dz === 0) continue;
                    if ($battle->getField()->getCell($position[0] + $dx, $position[1] + $dy, $position[2] + $dz)->obstacle) continue;
                    $adj[] = [$position[0] + $dx, $position[1] + $dy, $position[2] + $dz];
                }
            }
        }
        return $adj;
    }
}
