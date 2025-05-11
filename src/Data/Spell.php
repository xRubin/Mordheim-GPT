<?php

namespace Mordheim\Data;

use Mordheim\Battle;
use Mordheim\Data\Attributes\Difficulty;
use Mordheim\Data\Attributes\StateSpecialRule;
use Mordheim\Data\Attributes\WizardSpecialRule;
use Mordheim\Dice;
use Mordheim\Exceptions\InvalidAttributesException;
use Mordheim\FighterInterface;
use Mordheim\Ruler;
use Mordheim\SpecialRuleInterface;
use Mordheim\SpellInterface;

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
                $battle->addActiveSpell($fighter, $this);
                \Mordheim\BattleLogger::add("{$fighter->getName()} получает +2 к силе и двойной урон в рукопашном бою (через активное заклинание).");
                return true;
            case self::HEARTS_OF_STEEL:
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
                        $enemy->getState()->decreaseWounds($damage);
                        \Mordheim\BattleLogger::add("{$enemy->getName()} получает {$damage} урона от Soulfire (без сейва).");
                        if ($enemy->getState()->getWounds() <= 0) {
                            $battle->killFighter($enemy);
                        }
                    }
                }
                return true;
            case self::SHIELD_OF_FAITH:
                $battle->addActiveSpell($fighter, $this);
                \Mordheim\BattleLogger::add("{$fighter->getName()} становится невосприимчив к заклинаниям (через активное заклинание).");
                return true;
            case self::HEALING_HAND:
                foreach ([$fighter, ...$battle->getAlliesFor($fighter)] as $target) {
                    if (Ruler::distance($fighter->getState()->getPosition(), $target->getState()->getPosition()) <= 2) {
                        $target->getState()->setWounds($target->getWounds());
                        if (in_array($target->getState()->getStatus(), [\Mordheim\Status::STUNNED, \Mordheim\Status::KNOCKED_DOWN])) {
                            $target->getState()->setStatus(\Mordheim\Status::STANDING);
                            \Mordheim\BattleLogger::add("{$target->getName()} встаёт после исцеления Healing Hand.");
                        }
                        \Mordheim\BattleLogger::add("{$target->getName()} полностью исцелен Healing Hand.");
                        return true;
                    }
                }
                \Mordheim\BattleLogger::add("Нет подходящих целей для Healing Hand рядом с {$fighter->getName()}.");
                return false;
            case self::ARMOUR_OF_RIGHTEOUSNESS:
                $battle->addActiveSpell($fighter, $this);
                \Mordheim\BattleLogger::add("{$fighter->getName()} получает сейв 2+, внушает страх и становится невосприимчив к страху (через активное заклинание).");
                return true;
        }

        return false;
    }
}