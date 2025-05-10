<?php

namespace Mordheim\Rule;

use Mordheim\Dice;
use Mordheim\FighterInterface;
use Mordheim\SpecialRule;

/**
 * Класс для проверки психологических эффектов и лидерства по правилам Mordheim
 */
class Psychology
{
    /**
     * Проверка теста на лидерство (Leadership test, 2d6 <= Ld)
     * Тест на лидерство с учётом спецправила Leader (Ld-бабл 6 дюймов)
     * @param FighterInterface $fighter
     * @param FighterInterface[] $allies союзники на поле (можно пустой массив)
     * @return bool
     */
    public static function leadershipTest(FighterInterface $fighter, array $allies = []): bool
    {
        $usedLd = $fighter->getLeadership();
        $usedLeader = null;
        // Проверяем спецправило Leader у союзников в 6"
        foreach ($allies as $ally) {
            if (
                $ally !== $fighter &&
                $ally->hasSpecialRule(SpecialRule::LEADER) &&
                $ally->getState()->getStatus()->canAct()
            ) {
                if ($fighter->getDistance($ally) <= 6) {
                    if ($ally->getLeadership() > $usedLd) {
                        $usedLd = $ally->getLeadership();
                        $usedLeader = $ally;
                    }
                }
            }
        }
        $roll = Dice::roll(6) + Dice::roll(6);
        $success = $roll <= $usedLd;
        if ($usedLeader) {
            \Mordheim\BattleLogger::add("{$fighter->getName()} использует Ld капитана {$usedLeader->getName()} ({$usedLd}) для теста на лидерство.");
        }
        \Mordheim\BattleLogger::add("{$fighter->getName()} проходит тест на лидерство: бросок $roll против $usedLd — " . ($success ? 'успех' : 'провал'));
        return $success;
    }

    /**
     * Тест на страх (Fear) с учётом спецправила Leader
     * @param FighterInterface $attacker
     * @param FighterInterface $defender
     * @param FighterInterface[] $allies
     * @return bool
     */
    public static function testFear(FighterInterface $attacker, FighterInterface $defender, array $allies = []): bool
    {
        if ($attacker->getWeaponSkill() < $defender->getWeaponSkill()) {
            $res = self::leadershipTest($attacker, $allies);
            \Mordheim\BattleLogger::add("{$attacker->getName()} проходит тест страха против {$defender->getName()}: " . ($res ? 'успех' : 'провал'));
            return $res;
        }
        return true;
    }

    /**
     * Тест на панику/бегство (Rout/Panic) с учётом спецправила Leader
     * @param FighterInterface $fighter
     * @param FighterInterface[] $allies
     * @return bool
     */
    public static function testRout(FighterInterface $fighter, array $allies = []): bool
    {
        if ($fighter->hasSpecialRule(SpecialRule::UNFEELING))
            return true;
        $res = self::leadershipTest($fighter, $allies);
        \Mordheim\BattleLogger::add("{$fighter->getName()} проходит тест паники/бегства: " . ($res ? 'успех' : 'провал'));
        return $res;
    }

    /**
     * Rout Test (тест на бегство)
     * Проверяет, нужно ли делать тест (1/4 warband out of action), и проводит тест по Ld лидера или максимальному Ld оставшихся.
     * @param FighterInterface[] $warband
     * @param FighterInterface|null $leader
     * @return bool true — тест пройден, false — warband бежит
     */
    public static function routTest(array $warband, ?FighterInterface $leader): bool
    {
        $total = count($warband);
        $out = array_filter($warband, fn($f) => !$f->getState()->getStatus()->isAlive());
        if ($total === 0 || count($out) < ceil($total / 4)) return true;
        // Лидер не может — ищем максимальный Ld
        if ($leader && $leader->getState()->getStatus()->canLead()) {
            $ld = $leader->getLeadership();
        } else {
            $ld = max(array_map(fn($fighter) => $fighter->getState()->getStatus()->canLead() ? $fighter->getLeadership() : 0, $warband));
        }
        $roll = \Mordheim\Dice::roll(6) + \Mordheim\Dice::roll(6);
        $success = $roll <= $ld;
        \Mordheim\BattleLogger::add("Rout test: бросок $roll против $ld — " . ($success ? 'успех' : 'провал'));
        return $success;
    }

    /**
     * All Alone Test (тест одиночества)
     * @param FighterInterface $fighter
     * @param FighterInterface[] $enemies
     * @param FighterInterface[] $allies
     * @return bool true — выдержал, false — бежит
     */
    public static function allAloneTest(FighterInterface $fighter, array $enemies, array $allies): bool
    {
        if ($fighter->hasSpecialRule(SpecialRule::UNFEELING))
            return true;
        $closeEnemies = array_filter($enemies, fn($enemy) => $fighter->getDistance($enemy) <= 1.99);
        if (count($closeEnemies) < 2) return true;
        $closeAllies = array_filter($allies, fn($ally) => $ally !== $fighter && $ally->getState()->getStatus()->canAct() && $fighter->getDistance($ally) <= 6);
        if (count($closeAllies) > 0) return true;
        $roll = \Mordheim\Dice::roll(6) + \Mordheim\Dice::roll(6);
        $success = $roll <= $fighter->getLeadership();
        \Mordheim\BattleLogger::add("All Alone test: бросок $roll против {$fighter->getLeadership()} — " . ($success ? 'успех' : 'провал'));
        \Mordheim\BattleLogger::add("DEBUG: AllAloneTest roll=$roll, Ld={$fighter->getLeadership()}");
        return $success;
    }

    /**
     * Fear Test (тест страха)
     * @param FighterInterface $fighter
     * @return bool true — преодолел страх, false — поддался
     */
    public static function fearTest(FighterInterface $fighter): bool
    {
        if ($fighter->hasSpecialRule(SpecialRule::UNFEELING))
            return true;
        $roll = \Mordheim\Dice::roll(6) + \Mordheim\Dice::roll(6);
        $success = $roll <= $fighter->getLeadership();
        \Mordheim\BattleLogger::add("Fear test: бросок $roll против {$fighter->getLeadership()} — " . ($success ? 'успех' : 'провал'));
        \Mordheim\BattleLogger::add("DEBUG: FearTest roll=$roll, Ld={$fighter->getLeadership()}");
        return $success;
    }

    /**
     * Frenzy Effect (ярость): возвращает true, если воин в ярости должен обязательно атаковать и получает удвоенные атаки.
     * @param FighterInterface $fighter
     * @param FighterInterface[] $enemies
     * @return array [mustCharge=>bool, attacks=>int]
     */
    public static function frenzyEffect(FighterInterface $fighter, array $enemies): array
    {
        $inRange = array_filter($enemies, fn($enemy) => $fighter->getDistance($enemy) <= $fighter->getChargeRange());
        $mustCharge = count($inRange) > 0;
        $attacks = $fighter->getAttacks() * 2;
        \Mordheim\BattleLogger::add("DEBUG: FrenzyEffect baseAttacks={$fighter->getAttacks()}, resultAttacks=$attacks");
        if (!$fighter->getState()->getStatus()->canFrenzy()) {
            $mustCharge = false;
            $attacks = $fighter->getAttacks();
        }
        return ['mustCharge' => $mustCharge, 'attacks' => $attacks];
    }

    /**
     * Hatred Effect: разрешает переброс промахов в 1-й раунд боя против hated-врага
     * @param bool $isFirstRound
     * @return bool true — можно перебросить промахи
     */
    public static function hatredEffect(bool $isFirstRound): bool
    {
        return $isFirstRound;
    }

    /**
     * Stupidity Test
     * @param FighterInterface $fighter
     * @return bool true — преодолел тупость, false — не может действовать
     */
    public static function stupidityTest(FighterInterface $fighter): bool
    {
        if ($fighter->hasSpecialRule(SpecialRule::UNFEELING))
            return true;
        $roll = \Mordheim\Dice::roll(6) + \Mordheim\Dice::roll(6);
        $success = $roll <= $fighter->getLeadership();
        \Mordheim\BattleLogger::add("Stupidity test: бросок $roll против {$fighter->getLeadership()} — " . ($success ? 'успех' : 'провал'));
        \Mordheim\BattleLogger::add("DEBUG: StupidityTest roll=$roll, Ld={$fighter->getLeadership()}");
        return $success;
    }
}
