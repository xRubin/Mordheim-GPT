<?php

namespace Mordheim\Data;

use Mordheim\Battle;
use Mordheim\CloseCombat;
use Mordheim\Data\Attributes\Difficulty;
use Mordheim\Data\Attributes\StateSpecialRule;
use Mordheim\Data\Attributes\WizardSpecialRule;
use Mordheim\Dice;
use Mordheim\EquipmentManager;
use Mordheim\Exceptions\ChargeFailedException;
use Mordheim\Exceptions\InvalidAttributesException;
use Mordheim\FighterInterface;
use Mordheim\FighterState;
use Mordheim\Rule\AvoidStun;
use Mordheim\Rule\Charge;
use Mordheim\Rule\Injuries;
use Mordheim\Ruler;
use Mordheim\SpecialRuleInterface;
use Mordheim\SpellInterface;
use Mordheim\Status;
use Mordheim\Strategy\AggressiveStrategy;

enum Spell implements SpellInterface
{
    #[WizardSpecialRule('PRAYERS_OF_SIGMAR')]
    #[Difficulty(7)]
    #[StateSpecialRule('PLUS_2_STRENGTH'), StateSpecialRule('DOUBLE_DAMAGE')]
    case HAMMER_OF_SIGMAR;
    #[WizardSpecialRule('PRAYERS_OF_SIGMAR')]
    #[Difficulty(8)]
    #[StateSpecialRule('IMMUNE_TO_PSYCHOLOGY'), StateSpecialRule('FEARSOME')]
    case HEARTS_OF_STEEL;
    #[WizardSpecialRule('PRAYERS_OF_SIGMAR')]
    #[Difficulty(9)]
    case SOULFIRE;
    #[WizardSpecialRule('PRAYERS_OF_SIGMAR')]
    #[Difficulty(6)]
    #[StateSpecialRule('IMMUNE_TO_SPELLS')]
    case SHIELD_OF_FAITH;
    #[WizardSpecialRule('PRAYERS_OF_SIGMAR')]
    #[Difficulty(5)]
    case HEALING_HAND;
    #[WizardSpecialRule('PRAYERS_OF_SIGMAR')]
    #[Difficulty(9)]
    #[StateSpecialRule('FEARSOME'), StateSpecialRule('CAUSE_FEAR'), StateSpecialRule('SAVE_2')]
    case ARMOUR_OF_RIGHTEOUSNESS;

    #[WizardSpecialRule('WIZARD_NECROMANCY')]
    #[Difficulty(10)]
    case LIFESTEALER;
    #[WizardSpecialRule('WIZARD_NECROMANCY')]
    #[Difficulty(5)]
    case RE_ANIMATION;
    #[WizardSpecialRule('WIZARD_NECROMANCY')]
    #[Difficulty(6)]
    #[StateSpecialRule('CAUSE_FEAR')]
    case DEATH_VISION;
    #[WizardSpecialRule('WIZARD_NECROMANCY')]
    #[Difficulty(9)]
    case SPELL_OF_DOOM;
    #[WizardSpecialRule('WIZARD_NECROMANCY')]
    #[Difficulty(6)]
    case CALL_OF_VANHEL;
    #[WizardSpecialRule('WIZARD_NECROMANCY')]
    #[Difficulty(0)]
    case SPELL_OF_AWAKENING;

    #[WizardSpecialRule('WIZARD_CHAOS_RITUALS')]
    #[Difficulty(10)]
    case VISION_OF_TORMENT;
    #[WizardSpecialRule('WIZARD_CHAOS_RITUALS')]
    #[Difficulty(7)]
    case EYE_OF_GOD;
    #[WizardSpecialRule('WIZARD_CHAOS_RITUALS')]
    #[Difficulty(8)]
    case DARK_BLOOD;
    #[WizardSpecialRule('WIZARD_CHAOS_RITUALS')]
    #[Difficulty(9)]
    case LURE_OD_CHAOS;
    #[WizardSpecialRule('WIZARD_CHAOS_RITUALS')]
    #[Difficulty(7)]
    case WINGS_OF_DARKNESS;
    #[WizardSpecialRule('WIZARD_CHAOS_RITUALS')]
    #[Difficulty(3)]
    case WORD_OF_PAIN;

    #[WizardSpecialRule('WIZARD_LESSER_MAGIC')]
    #[Difficulty(7)]
    case FIRES_OF_UZHUL;
    #[WizardSpecialRule('WIZARD_LESSER_MAGIC')]
    #[Difficulty(7)]
    case FLIGHT_OF_ZIMMERAN;
    #[WizardSpecialRule('WIZARD_LESSER_MAGIC')]
    #[Difficulty(7)]
    case DREAD_OF_ARAMAR;
    #[WizardSpecialRule('WIZARD_LESSER_MAGIC')]
    #[Difficulty(7)]
    case SILVER_ARROWS_OF_ARHA;
    #[WizardSpecialRule('WIZARD_LESSER_MAGIC')]
    #[Difficulty(6)]
    case LUCK_OF_SHEMTEK;
    #[WizardSpecialRule('WIZARD_LESSER_MAGIC')]
    #[Difficulty(7)]
    case SWORD_OF_REZHEBEL;

    #[WizardSpecialRule('WIZARD_MAGIC_OF_THE_HORNED_RAT')]
    #[Difficulty(8)]
    case WARPFIRE;
    #[WizardSpecialRule('WIZARD_MAGIC_OF_THE_HORNED_RAT')]
    #[Difficulty(0)]
    case CHILDREN_OF_THE_HORNED_RAT;
    #[WizardSpecialRule('WIZARD_MAGIC_OF_THE_HORNED_RAT')]
    #[Difficulty(8)]
    case GNAWDOOM;
    #[WizardSpecialRule('WIZARD_MAGIC_OF_THE_HORNED_RAT')]
    #[Difficulty(4)]
    case BLACK_FURY;
    #[WizardSpecialRule('WIZARD_MAGIC_OF_THE_HORNED_RAT')]
    #[Difficulty(8)]
    case EYE_OF_THE_WARP;
    #[WizardSpecialRule('WIZARD_MAGIC_OF_THE_HORNED_RAT')]
    #[Difficulty(6)]
    case SORCERERS_CURSE;

    public function getOwnerSpecialRule(): SpecialRuleInterface
    {
        $ref = new \ReflectionClassConstant(self::class, $this->name);
        $classAttributes = $ref->getAttributes(WizardSpecialRule::class);

        if (count($classAttributes) === 0)
            throw new InvalidAttributesException('Invalid attributes for: ' . $this->name);

        return $classAttributes[0]->newInstance()->getValue();
    }

    public function getBlankDifficulty(): int
    {
        $ref = new \ReflectionClassConstant(self::class, $this->name);
        $classAttributes = $ref->getAttributes(Difficulty::class);

        if (count($classAttributes) === 0)
            return 0;

        return $classAttributes[0]->newInstance()->getValue();
    }

    /**
     * @return SpecialRuleInterface[]
     */
    public function getStateRules(): array
    {
        $ref = new \ReflectionClassConstant(self::class, $this->name);
        $classAttributes = $ref->getAttributes(StateSpecialRule::class);

        if (count($classAttributes) === 0)
            return [];

        return array_map(
            fn($attribute) => $attribute->newInstance()->getValue(),
            $classAttributes
        );
    }

    public static function onPhaseShoot(Battle $battle, FighterInterface $fighter): void
    {
        $activeSpells = $fighter->getState()->getActiveSpells();
        foreach ($activeSpells as $spell) {
            switch ($spell) {
                case self::ARMOUR_OF_RIGHTEOUSNESS:
                    \Mordheim\BattleLogger::add("Эффект Armour of Righteousness спадает с {$fighter->getName()} в фазе стрельбы.");
                    $battle->removeActiveSpell($fighter, $spell);
                    break;
                case self::HAMMER_OF_SIGMAR:
                    $roll = Dice::roll(6) + Dice::roll(6);
                    if ($roll < $spell->getBlankDifficulty()) {
                        \Mordheim\BattleLogger::add("Hammer of Sigmar спадает с {$fighter->getName()} (бросок: $roll)");
                        $battle->removeActiveSpell($fighter, $spell);
                    } else {
                        \Mordheim\BattleLogger::add("Hammer of Sigmar остаётся на {$fighter->getName()} (бросок: $roll)");
                    }
                    break;
            }
        }
    }

    public function onPhaseMagic(Battle $battle, FighterInterface $fighter): bool
    {
        switch ($this) {
            case self::HAMMER_OF_SIGMAR:
                if ($fighter->getState()->hasActiveSpell(self::HAMMER_OF_SIGMAR))
                    return false;
                $battle->addActiveSpell($fighter, $this);
                \Mordheim\BattleLogger::add("{$fighter->getName()} получает +2 к силе и двойной урон в рукопашном бою (через активное заклинание).");
                return true;
            case self::HEARTS_OF_STEEL:
                if ($fighter->getState()->hasActiveSpell(self::HEARTS_OF_STEEL))
                    return false;
                $battle->addActiveSpell($fighter, $this);
                foreach ($battle->getAlliesFor($fighter) as $ally) {
                    if (Ruler::distance($fighter->getState()->getPosition(), $ally->getState()->getPosition()) <= 8) {
                        $ally->getState()->addActiveSpell($this);
                        \Mordheim\BattleLogger::add("{$ally->getName()} получает иммунитет к страху и тестам одиночества (через активное заклинание).");
                    }
                }
                // TODO: $warband->heartsOfSteelRoutBonus = 1;
                // \Mordheim\BattleLogger::add("Вся банда {$warband->name} получает +1 к тесту на бегство.");
                return true;
            case self::SOULFIRE:
                $enemies = $battle->getEnemiesFor($fighter);
                foreach ($enemies as $enemy) {
                    if (Ruler::distance($fighter->getState()->getPosition(), $enemy->getState()->getPosition()) <= 4) {
                        $damage = in_array($enemy->getBlank()->getWarband(), [Warband::UNDEAD, Warband::CULT_OF_THE_POSSESSED]) ? 5 : 3;
                        $enemy->getState()->modifyWounds(-$damage);
                        \Mordheim\BattleLogger::add("{$enemy->getName()} получает {$damage} урона от Soulfire (без сейва).");
                        if ($enemy->getState()->getWounds() <= 0) {
                            $battle->killFighter($enemy);
                        }
                    }
                }
                return true;
            case self::SHIELD_OF_FAITH:
                if ($fighter->getState()->hasActiveSpell(self::SHIELD_OF_FAITH))
                    return false;
                $battle->addActiveSpell($fighter, $this);
                \Mordheim\BattleLogger::add("{$fighter->getName()} становится невосприимчив к заклинаниям (через активное заклинание).");
                return true;
            case self::HEALING_HAND:
                foreach ([$fighter, ...$battle->getAlliesFor($fighter)] as $target) {
                    if (Ruler::distance($fighter->getState()->getPosition(), $target->getState()->getPosition()) > 2)
                        continue;
                    if ($target->getState()->getWounds() === $target->getWounds())
                        continue;
                    $target->getState()->setWounds($target->getWounds());
                    if (in_array($target->getState()->getStatus(), [\Mordheim\Status::STUNNED, \Mordheim\Status::KNOCKED_DOWN])) {
                        $target->getState()->setStatus(\Mordheim\Status::STANDING);
                        \Mordheim\BattleLogger::add("{$target->getName()} встаёт после исцеления Healing Hand.");
                    }
                    \Mordheim\BattleLogger::add("{$target->getName()} полностью исцелен Healing Hand.");
                    return true;
                }
                \Mordheim\BattleLogger::add("Нет подходящих целей для Healing Hand рядом с {$fighter->getName()}.");
                return false;
            case self::ARMOUR_OF_RIGHTEOUSNESS:
                if ($fighter->getState()->hasActiveSpell(self::ARMOUR_OF_RIGHTEOUSNESS))
                    return false;
                $battle->addActiveSpell($fighter, $this);
                \Mordheim\BattleLogger::add("{$fighter->getName()} получает сейв 2+, внушает страх и становится невосприимчив к страху (через активное заклинание).");
                return true;
            case self::LIFESTEALER:
                $target = null;
                foreach ($battle->getEnemiesFor($fighter) as $enemy) {
                    if (in_array($enemy->getBlank()->getWarband(), [Warband::UNDEAD, Warband::CULT_OF_THE_POSSESSED]))
                        continue;
                    if (Ruler::distance($fighter->getState()->getPosition(), $enemy->getState()->getPosition()) > 6)
                        continue;
                    $target = $enemy;
                    break;
                }
                if (!$target) {
                    \Mordheim\BattleLogger::add("Нет подходящей цели для Lifestealer.");
                    return false;
                }
                $target->getState()->modifyWounds(-1);
                $fighter->getState()->modifyWounds(+1);
                \Mordheim\BattleLogger::add("{$fighter->getName()} высасывает жизнь у {$target->getName()} (Lifestealer).");
                if ($target->getState()->getWounds() <= 0) {
                    $battle->killFighter($target);
                }
                return true;
            case self::RE_ANIMATION:
                $zombie = null;
                foreach ($battle->getFighters() as $target) {
                    if ($fighter !== $target
                        && !$target->getState()->getStatus()->isAlive()
                        && $battle->isAlly($target, $fighter)
                        && $target->getBlank() == \Mordheim\Data\Blank::UNDEAD_ZOMBIE
                    ) {
                        $zombie = $target;
                        break;
                    }
                }
                if (!$zombie) {
                    \Mordheim\BattleLogger::add("Нет подходящего зомби для Re-Animation.");
                    return false;
                }
                $cell = $battle->findUnoccupiedPosition($fighter, 6);
                if (!$cell) {
                    \Mordheim\BattleLogger::add("Нет свободной клетки для появления зомби (Re-Animation).");
                    return false;
                }
                $zombie->getState()->setWounds($zombie->getWounds());
                $zombie->getState()->setStatus(\Mordheim\Status::STANDING);
                $zombie->getState()->setPosition($cell);
                \Mordheim\BattleLogger::add("{$fighter->getName()} возвращает зомби {$zombie->getName()} в бой (Re-Animation) на клетку [" . implode(",", $cell) . "].");
                return true;
            case self::DEATH_VISION:
                if ($fighter->getState()->hasActiveSpell(self::DEATH_VISION))
                    return false;
                $battle->addActiveSpell($fighter, $this);
                \Mordheim\BattleLogger::add("{$fighter->getName()} внушает страх до конца боя (Death Vision).");
                return true;
            case self::SPELL_OF_DOOM:
                $target = false;
                foreach ($battle->getEnemiesFor($fighter) as $enemy) {
                    if (
                        Ruler::distance($fighter->getState()->getPosition(), $enemy->getState()->getPosition()) <= 12
                    ) {
                        $target = $enemy;
                        break;
                    }
                }
                if ($target) {
                    $roll = \Mordheim\Dice::roll(6);
                    if ($roll > $target->getStrength()) {
                        \Mordheim\BattleLogger::add("{$target->getName()} не прошёл проверку силы (Spell of Doom), бросок: $roll.");
                        Injuries::roll($battle, $fighter, $target);
                    } else {
                        \Mordheim\BattleLogger::add("{$target->getName()} устоял против Spell of Doom, бросок: $roll.");
                    }
                    return true;
                }
                \Mordheim\BattleLogger::add("Нет цели для Spell of Doom.");
                return false;
            case self::CALL_OF_VANHEL:
                $target = false;
                foreach ($battle->getAlliesFor($fighter) as $ally) {
                    if (
                        in_array($ally->getBlank(), [Blank::UNDEAD_ZOMBIE, Blank::UNDEAD_DIRE_WOLF])
                        && Ruler::distance($fighter->getState()->getPosition(), $ally->getState()->getPosition()) <= 6
                    ) {
                        $target = $ally;
                        break;
                    }
                }
                if ($target) {
                    // Дополнительное движение: двигаем к ближайшему врагу или просто вперёд на максимум
                    $enemies = $battle->getEnemiesFor($target);
                    if (empty($enemies))
                        return false;
                    // К ближайшему врагу
                    usort($enemies, fn($a, $b) => Ruler::distance($target->getState()->getPosition(), $a->getState()->getPosition()) <=> Ruler::distance($target->getState()->getPosition(), $b->getState()->getPosition()));
                    \Mordheim\BattleLogger::add("{$target->getName()} получает дополнительное движение от Call of Vanhel.");
                    try {
                        $battle->getActiveCombats()->add(
                            Charge::attempt($battle, $target, $enemies[0], 0.4)
                        );
                    } catch (ChargeFailedException $e) {
                        \Mordheim\Rule\Move::common($battle, $target, $enemies[0]->getState()->getPosition(), 0.4);
                    }
                    return true;
                }
                \Mordheim\BattleLogger::add("Нет подходящей цели для Call of Vanhel.");
                return false;
            case self::SPELL_OF_AWAKENING:
                if ($fighter->getState()->hasActiveSpell(self::DEATH_VISION))
                    return false;

                $cell = $battle->findUnoccupiedPosition($fighter, 6);
                if (!$cell) {
                    \Mordheim\BattleLogger::add("Нет свободной клетки для появления зомби (Re-Animation).");
                    return false;
                }

                $zombieHero = new \Mordheim\Fighter(
                    \Mordheim\Data\Blank::REIKLAND_CHAMPION,
                    new \Mordheim\FighterAdvancement(
                        new \Mordheim\Characteristics(),
                        [
                            \Mordheim\SpecialRule::CAUSE_FEAR,
                            \Mordheim\SpecialRule::MAY_NOT_RUN,
                            \Mordheim\SpecialRule::IMMUNE_TO_PSYCHOLOGY,
                            \Mordheim\SpecialRule::IMMUNE_TO_POISON,
                            \Mordheim\SpecialRule::NO_PAIN,
                            \Mordheim\SpecialRule::NO_BRAIN
                        ]
                    ),
                    new EquipmentManager([Equipment::SWORD, Equipment::HEAVY_ARMOR]),
                    new FighterState($cell, new AggressiveStrategy(), 2)
                );
                $battle->addFighter($zombieHero);
                $battle->addActiveSpell($fighter, $this);
                \Mordheim\BattleLogger::add("{$fighter->getName()} поднимает героя-зомби ({$zombieHero->getName()}) с помощью Spell of Awakening!");
                return true;
            case self::VISION_OF_TORMENT:
            {
                // Ближайший враг в 6" — оглушить (или сбить с ног)
                $enemies = $battle->getEnemiesFor($fighter);
                if (empty($enemies))
                    return false;
                // К ближайшему врагу
                usort($enemies, fn($a, $b) => Ruler::distance($fighter->getState()->getPosition(), $a->getState()->getPosition()) <=> Ruler::distance($fighter->getState()->getPosition(), $b->getState()->getPosition()));
                $closest = $enemies[0];
                if (Ruler::distance($fighter->getState()->getPosition(), $closest->getState()->getPosition()) > 6) {
                    \Mordheim\BattleLogger::add("Нет врагов в 6\" для Vision of Torment.");
                    return false;
                }
                // Если в рукопашной — только из base contact
                if ($battle->getActiveCombats()->isFighterInCombat($fighter)) {
                    $inBase = array_filter($enemies, fn($e) => Ruler::isAdjacent($fighter->getState()->getPosition(), $e->getState()->getPosition()));
                    if (count($inBase)) {
                        $closest = reset($inBase);
                    }
                }
                if (AvoidStun::roll($closest)) {
                    \Mordheim\BattleLogger::add("{$closest->getName()} сбит с ног Vision of Torment.");
                    $closest->getState()->setStatus(Status::KNOCKED_DOWN);
                } else {
                    \Mordheim\BattleLogger::add("{$closest->getName()} оглушён Vision of Torment.");
                    $closest->getState()->setStatus(Status::STUNNED);
                }
                return true;
            }
            case self::EYE_OF_GOD:
            {
                // Раз в бой, цель в 6", d6: 1 — выбыл, 2-5 — +1 к характеристике, 6 — +1 ко всем
                if ($fighter->getState()->hasActiveSpell(self::EYE_OF_GOD)) {
                    \Mordheim\BattleLogger::add("Eye of God уже был использован этим магом в этом бою.");
                    return false;
                }
                $target = null;
                foreach ($battle->getAlliesFor($fighter) as $ally) {
                    if (Ruler::distance($fighter->getState()->getPosition(), $ally->getState()->getPosition()) <= 6) {
                        $target = $ally;
                    }
                }
                if (!$target) {
                    \Mordheim\BattleLogger::add("Нет цели для Eye of God в 6\".");
                    return false;
                }

                $roll = Dice::roll(6);
                if ($roll == 1) {
                    $battle->killFighter($target);
                    \Mordheim\BattleLogger::add("{$target->getName()} поражён гневом богов (Eye of God) и выбывает из боя!");
                } elseif ($roll >= 2 && $roll <= 5) {
                    // +1 к одной характеристике
                    $characteristics = [
                        'movement' => $target->getMovement(),
                        'weaponSkill' => $target->getWeaponSkill(),
                        'ballisticSkill' => $target->getBallisticSkill(),
                        'strength' => $target->getStrength(),
                        'toughness' => $target->getToughness(),
                        'initiative' => $target->getInitiative(),
                        'attacks' => $target->getAttacks(),
                        'leadership' => $target->getLeadership(),

                    ];
                    arsort($characteristics);
                    $selected = array_key_first($characteristics);
                    $target->getAdvancement()->getCharacteristics()->$selected += 1;
                    \Mordheim\BattleLogger::add("{$target->getName()} получает +1 к {$selected} до конца боя (Eye of God).");
                } else {
                    // +1 ко всем характеристикам
                    foreach ([
                                 'movement', 'weaponSkill', 'ballisticSkill', 'strength', 'toughness', 'wounds', 'initiative', 'attacks', 'leadership'
                             ] as $char) {
                        if (property_exists($target->getAdvancement()->getCharacteristics(), $char)) {
                            $target->getAdvancement()->getCharacteristics()->$char += 1;
                        }
                    }
                    \Mordheim\BattleLogger::add("{$target->getName()} получает +1 ко всем характеристикам до конца боя (Eye of God)!");
                }
                $battle->addActiveSpell($fighter, $this);
                return true;
            }
            case self::DARK_BLOOD:
            {
                // 8", D3 попадания S5 по первой цели на линии, маг кидает на травму себе
                $target = null;
                foreach ($battle->getAlliesFor($fighter) as $ally) {
                    if (Ruler::distance($fighter->getState()->getPosition(), $ally->getState()->getPosition()) <= 8) {
                        $target = $ally;
                    }
                }
                if (!$target) {
                    \Mordheim\BattleLogger::add("Нет цели для Dark Blood в 8\".");
                    return false;
                }
                $hits = Dice::roll(3); // D3
                for ($i = 0; $i < $hits; $i++) {
                    $target->getState()->modifyWounds(-5);
                }
                \Mordheim\BattleLogger::add("{$target->getName()} получает {$hits} попаданий силой 5 от Dark Blood.");
                if ($target->getState()->getWounds() <= 0) {
                    $battle->killFighter($target);
                }
                // Маг кидает на травму себе
                \Mordheim\Rule\Injuries::roll($battle, $fighter, $fighter); // TODO treat out of action as stunned
                return true;
            }
            case self::LURE_OD_CHAOS:
            {
                // 12", ближайший враг, сравнение Лидерства, если маг выиграл — контроль
                // К ближайшему врагу
                $enemies = $battle->getEnemiesFor($fighter);
                usort($enemies, fn($a, $b) => Ruler::distance($fighter->getState()->getPosition(), $a->getState()->getPosition()) <=> Ruler::distance($fighter->getState()->getPosition(), $b->getState()->getPosition()));
                $closest = $enemies[0];
                if (Ruler::distance($fighter->getState()->getPosition(), $closest->getState()->getPosition()) > 12) {
                    \Mordheim\BattleLogger::add("Нет врагов в 12\" для Lure of Chaos.");
                    return false;
                }

                $mageRoll = Dice::roll(6) + $fighter->getLeadership();
                $targetRoll = Dice::roll(6) + $closest->getLeadership();
                if ($mageRoll > $targetRoll) {
                    $closest->getState()->addActiveSpell($this);
                    \Mordheim\BattleLogger::add("{$fighter->getName()} получает контроль над {$closest->getName()} (Lure of Chaos)!");
                    foreach ($battle->getActiveCombats()->getByFighter($closest) as $combat)
                        $battle->getActiveCombats()->remove($combat);
                } else {
                    \Mordheim\BattleLogger::add("{$closest->getName()} устоял против Lure of Chaos.");
                }
                return true;
            }
            case self::WINGS_OF_DARKNESS:
            {
                // Перемещение на 12", можно в контакт (атака), если догнал бегущего — автохит
                $enemies = $battle->getEnemiesFor($fighter);
                if (empty($enemies)) {
                    \Mordheim\BattleLogger::add("Нет врагов для Wings of Darkness.");
                    return false;
                }
                // К ближайшему врагу в 12"
                usort($enemies, fn($a, $b) => Ruler::distance($fighter->getState()->getPosition(), $a->getState()->getPosition()) <=> Ruler::distance($fighter->getState()->getPosition(), $b->getState()->getPosition()));
                $closest = $enemies[0];
                $dist = Ruler::distance($fighter->getState()->getPosition(), $closest->getState()->getPosition());
                if ($dist > 12) {
                    \Mordheim\BattleLogger::add("Нет врагов в 12\" для Wings of Darkness.");
                    return false;
                }
                // Найти свободную adjacent клетку
                $adj = \Mordheim\Rule\Charge::getNearestChargePosition($battle, $fighter, $closest);
                if (empty($adj)) {
                    \Mordheim\BattleLogger::add("Нет свободных клеток рядом с врагом для Wings of Darkness.");
                    return false;
                }
                $fighter->getState()->setPosition($adj);
                \Mordheim\BattleLogger::add("{$fighter->getName()} перемещается к {$closest->getName()} с помощью Wings of Darkness.");
                // Charge/Close Combat
                if ($closest->getState()->getStatus() === Status::PANIC) {
                    \Mordheim\BattleLogger::add("{$fighter->getName()} наносит автоматический удар по бегущему {$closest->getName()} (Wings of Darkness).");
                    $closest->getState()->modifyWounds(-1); // Пример: 1 урон
                    if ($closest->getState()->getWounds() <= 0) {
                        $battle->killFighter($closest);
                    }
                } else {
                    $battle->getActiveCombats()->add(new CloseCombat($fighter, $closest));
                }
                return true;
            }
            case self::WORD_OF_PAIN:
            {
                // Все в 3" получают 1 попадание S3, без сейва
                $fighters = $battle->getFighters();
                $count = 0;
                foreach ($fighters as $target) {
                    if (Ruler::distance($fighter->getState()->getPosition(), $target->getState()->getPosition()) <= 3) {
                        if ($target === $fighter)
                            continue;
                        $target->getState()->modifyWounds(-3); // S3, но просто -3 ран
                        if ($target->getState()->getWounds() <= 0) {
                            $battle->killFighter($target);
                        }
                        $count++;
                    }
                }
                \Mordheim\BattleLogger::add("{$count} моделей получают урон от Word of Pain (без сейва).");
                return true;
            }
        }

        return false;
    }
}