<?php

namespace Mordheim;

/**
 * Класс для проверки психологических эффектов и лидерства по правилам Mordheim
 */
class Psychology
{
    /**
     * Проверка теста на лидерство (Leadership test, 2d6 <= Ld)
     * Тест на лидерство с учётом спецправила Leader (Ld-бабл 6 дюймов)
     * @param Fighter $fighter
     * @param Fighter[] $allies союзники на поле (можно пустой массив)
     * @return bool
     */
    public static function leadershipTest(Fighter $fighter, array $allies = []): bool
    {
        $usedLd = $fighter->characteristics->leadership;
        $usedLeader = null;
        // Проверяем спецправило Leader у союзников в 6"
        foreach ($allies as $ally) {
            if (
                $ally !== $fighter &&
                $ally->hasSkill('Leader') &&
                $ally->alive &&
                !in_array($ally->state, [
                    \Mordheim\FighterState::PANIC,
                    \Mordheim\FighterState::OUT_OF_ACTION,
                    \Mordheim\FighterState::STUNNED,
                    \Mordheim\FighterState::KNOCKED_DOWN
                ], true)
            ) {
                if ($fighter->distance($ally) <= 6) {
                    if ($ally->characteristics->leadership > $usedLd) {
                        $usedLd = $ally->characteristics->leadership;
                        $usedLeader = $ally;
                    }
                }
            }
        }
        $roll = Dice::roll(6) + Dice::roll(6);
        $success = $roll <= $usedLd;
        if ($usedLeader) {
            \Mordheim\BattleLogger::add("{$fighter->name} использует Ld капитана {$usedLeader->name} ({$usedLd}) для теста на лидерство.");
        }
        \Mordheim\BattleLogger::add("{$fighter->name} проходит тест на лидерство: бросок $roll против $usedLd — " . ($success ? 'успех' : 'провал'));
        return $success;
    }

    /**
     * Проверка страха (Fear): если WS атакующего < WS защищающего, тест на лидерство
     */
    /**
     * Тест на страх (Fear) с учётом спецправила Leader
     * @param Fighter $attacker
     * @param Fighter $defender
     * @param Fighter[] $allies
     * @return bool
     */
    public static function testFear(Fighter $attacker, Fighter $defender, array $allies = []): bool
    {
        if ($attacker->characteristics->weaponSkill < $defender->characteristics->weaponSkill) {
            $res = self::leadershipTest($attacker, $allies);
            \Mordheim\BattleLogger::add("{$attacker->name} проходит тест страха против {$defender->name}: " . ($res ? 'успех' : 'провал'));
            return $res;
        }
        return true;
    }

    /**
     * Проверка ужаса (Terror): всегда тест на лидерство
     */
    /**
     * Тест на ужас (Terror) с учётом спецправила Leader
     * @param Fighter $fighter
     * @param Fighter[] $allies
     * @return bool
     */
    public static function testTerror(Fighter $fighter, array $allies = []): bool
    {
        $res = self::leadershipTest($fighter, $allies);
        \Mordheim\BattleLogger::add("{$fighter->name} проходит тест ужаса: " . ($res ? 'успех' : 'провал'));
        return $res;
    }

    /**
     * Проверка устойчивости к панике (Rout/Panic): тест на лидерство
     */
    /**
     * Тест на панику/бегство (Rout/Panic) с учётом спецправила Leader
     * @param Fighter $fighter
     * @param Fighter[] $allies
     * @return bool
     */
    public static function testRout(Fighter $fighter, array $allies = []): bool
    {
        $res = self::leadershipTest($fighter, $allies);
        \Mordheim\BattleLogger::add("{$fighter->name} проходит тест паники/бегства: " . ($res ? 'успех' : 'провал'));
        return $res;
    }

    /**
     * Rout Test (тест на бегство)
     * Проверяет, нужно ли делать тест (1/4 warband out of action), и проводит тест по Ld лидера или максимальному Ld оставшихся.
     * @param Fighter[] $warband
     * @param Fighter|null $leader
     * @return bool true — тест пройден, false — warband бежит
     */
    public static function routTest(array $warband, ?Fighter $leader): bool
    {
        $total = count($warband);
        $out = array_filter($warband, fn($f) => !$f->alive || $f->state === \Mordheim\FighterState::OUT_OF_ACTION);
        if ($total === 0 || count($out) < ceil($total / 4)) return true;
        // Лидер не может — ищем максимальный Ld
        if ($leader && $leader->alive && !in_array($leader->state, [
                \Mordheim\FighterState::OUT_OF_ACTION,
                \Mordheim\FighterState::STUNNED
            ], true)) {
            $ld = $leader->characteristics->leadership;
        } else {
            $ld = max(array_map(fn($f) => ($f->alive && !in_array($f->state, [\Mordheim\FighterState::OUT_OF_ACTION, \Mordheim\FighterState::STUNNED], true)) ? $f->characteristics->leadership : 0, $warband));
        }
        $roll = \Mordheim\Dice::roll(6) + \Mordheim\Dice::roll(6);
        $success = $roll <= $ld;
        \Mordheim\BattleLogger::add("Rout test: бросок $roll против $ld — " . ($success ? 'успех' : 'провал'));
        return $success;
    }

    /**
     * All Alone Test (тест одиночества)
     * @param Fighter $fighter
     * @param Fighter[] $enemies
     * @param Fighter[] $allies
     * @return bool true — выдержал, false — бежит
     */
    public static function allAloneTest(Fighter $fighter, array $enemies, array $allies): bool
    {
        $closeEnemies = array_filter($enemies, fn($e) => $fighter->distance($e) <= 1.99);
        if (count($closeEnemies) < 2) return true;
        $closeAllies = array_filter($allies, fn($a) => $a !== $fighter && $a->alive && $a->state === \Mordheim\FighterState::STANDING && $fighter->distance($a) <= 6);
        if (count($closeAllies) > 0) return true;
        $roll = \Mordheim\Dice::roll(6) + \Mordheim\Dice::roll(6);
        $success = $roll <= $fighter->characteristics->leadership;
        \Mordheim\BattleLogger::add("All Alone test: бросок $roll против {$fighter->characteristics->leadership} — " . ($success ? 'успех' : 'провал'));
        \Mordheim\BattleLogger::add("DEBUG: AllAloneTest roll=$roll, Ld={$fighter->characteristics->leadership}");
        return $success;
    }

    /**
     * Fear Test (тест страха)
     * @param Fighter $fighter
     * @return bool true — преодолел страх, false — поддался
     */
    public static function fearTest(Fighter $fighter): bool
    {
        $roll = \Mordheim\Dice::roll(6) + \Mordheim\Dice::roll(6);
        $success = $roll <= $fighter->characteristics->leadership;
        \Mordheim\BattleLogger::add("Fear test: бросок $roll против {$fighter->characteristics->leadership} — " . ($success ? 'успех' : 'провал'));
        \Mordheim\BattleLogger::add("DEBUG: FearTest roll=$roll, Ld={$fighter->characteristics->leadership}");
        return $success;
    }

    /**
     * Frenzy Effect (ярость): возвращает true, если воин в ярости должен обязательно атаковать и получает удвоенные атаки.
     * @param Fighter $fighter
     * @param Fighter[] $enemies
     * @return array [mustCharge=>bool, attacks=>int]
     */
    public static function frenzyEffect(Fighter $fighter, array $enemies): array
    {
        $inRange = array_filter($enemies, fn($e) => $fighter->distance($e) <= $fighter->chargeRange());
        $mustCharge = count($inRange) > 0;
        $attacks = $fighter->characteristics->attacks * 2;
        \Mordheim\BattleLogger::add("DEBUG: FrenzyEffect baseAttacks={$fighter->characteristics->attacks}, resultAttacks=$attacks");
        if ($fighter->state === \Mordheim\FighterState::KNOCKED_DOWN || $fighter->state === \Mordheim\FighterState::STUNNED) {
            $mustCharge = false;
            $attacks = $fighter->characteristics->attacks;
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
     * @param Fighter $fighter
     * @return bool true — преодолел тупость, false — не может действовать
     */
    public static function stupidityTest(Fighter $fighter): bool
    {
        $roll = \Mordheim\Dice::roll(6) + \Mordheim\Dice::roll(6);
        $success = $roll <= $fighter->characteristics->leadership;
        \Mordheim\BattleLogger::add("Stupidity test: бросок $roll против {$fighter->characteristics->leadership} — " . ($success ? 'успех' : 'провал'));
        \Mordheim\BattleLogger::add("DEBUG: StupidityTest roll=$roll, Ld={$fighter->characteristics->leadership}");
        return $success;
    }
}
