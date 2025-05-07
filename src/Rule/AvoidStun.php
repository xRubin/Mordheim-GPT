<?php

namespace Mordheim\Rule;

use Mordheim\FighterInterface;

class AvoidStun
{
    /**
     * Попытка избежать стана с помощью шлема (Avoid stun, 4+)
     * @return bool true — спасся (stun превращается в knockdown), false — не спасся
     */
    public static function roll(FighterInterface $fighter): bool
    {
        if ($fighter->getEquipmentManager()->hasHelmetProtection()) {
            $roll = \Mordheim\Dice::roll(6);
            if ($roll >= 4) {
                \Mordheim\BattleLogger::add("{$fighter->getName()} спасся от стана шлемом (бросок $roll)");
                return true;
            }
        }
        return false;
    }
}