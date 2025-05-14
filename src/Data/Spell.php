<?php

namespace Mordheim\Data;

use Mordheim\Data\Attributes\Characteristics;
use Mordheim\Data\Attributes\Difficulty;
use Mordheim\Data\Attributes\SpellProcessor;
use Mordheim\Data\Attributes\StateSpecialRule;
use Mordheim\Data\Attributes\WizardSpecialRule;
use Mordheim\Exceptions\InvalidAttributesException;
use Mordheim\SpecialRuleInterface;
use Mordheim\SpellInterface;
use Mordheim\Spells;

enum Spell implements SpellInterface
{
    #[WizardSpecialRule('PRAYERS_OF_SIGMAR')]
    #[Difficulty(7)]
    #[StateSpecialRule('PLUS_2_STRENGTH'), StateSpecialRule('DOUBLE_DAMAGE')]
    #[SpellProcessor(Spells\PrayersOfSigmar\HammerOfSigmarProcessor::class)]
    case HAMMER_OF_SIGMAR;
    #[WizardSpecialRule('PRAYERS_OF_SIGMAR')]
    #[Difficulty(8)]
    #[SpellProcessor(Spells\PrayersOfSigmar\HeartsOfSteelProcessor::class)]
    case HEARTS_OF_STEEL;
    #[StateSpecialRule('IMMUNE_TO_PSYCHOLOGY'), StateSpecialRule('FEARSOME')]
    case HEARTS_OF_STEEL_TARGET;
    #[WizardSpecialRule('PRAYERS_OF_SIGMAR')]
    #[Difficulty(9)]
    #[SpellProcessor(Spells\PrayersOfSigmar\SoulfireProcessor::class)]
    case SOULFIRE;
    #[WizardSpecialRule('PRAYERS_OF_SIGMAR')]
    #[Difficulty(6)]
    #[StateSpecialRule('IMMUNE_TO_SPELLS')]
    #[SpellProcessor(Spells\PrayersOfSigmar\ShieldOfFaithProcessor::class)]
    case SHIELD_OF_FAITH;
    #[WizardSpecialRule('PRAYERS_OF_SIGMAR')]
    #[Difficulty(5)]
    #[SpellProcessor(Spells\PrayersOfSigmar\HealingHandProcessor::class)]
    case HEALING_HAND;
    #[WizardSpecialRule('PRAYERS_OF_SIGMAR')]
    #[Difficulty(9)]
    #[StateSpecialRule('FEARSOME'), StateSpecialRule('CAUSE_FEAR'), StateSpecialRule('SAVE_2')]
    #[SpellProcessor(Spells\PrayersOfSigmar\ArmourOfRighteousnessProcessor::class)]
    case ARMOUR_OF_RIGHTEOUSNESS;

    #[WizardSpecialRule('WIZARD_NECROMANCY')]
    #[Difficulty(10)]
    #[SpellProcessor(Spells\Necromancy\LifestealerProcessor::class)]
    case LIFESTEALER;
    #[WizardSpecialRule('WIZARD_NECROMANCY')]
    #[Difficulty(5)]
    #[SpellProcessor(Spells\Necromancy\ReAnimationProcessor::class)]
    case RE_ANIMATION;
    #[WizardSpecialRule('WIZARD_NECROMANCY')]
    #[Difficulty(6)]
    #[StateSpecialRule('CAUSE_FEAR')]
    #[SpellProcessor(Spells\Necromancy\DeathVisionProcessor::class)]
    case DEATH_VISION;
    #[WizardSpecialRule('WIZARD_NECROMANCY')]
    #[Difficulty(9)]
    #[SpellProcessor(Spells\Necromancy\SpellOfDoomProcessor::class)]
    case SPELL_OF_DOOM;
    #[WizardSpecialRule('WIZARD_NECROMANCY')]
    #[Difficulty(6)]
    #[SpellProcessor(Spells\Necromancy\CallOfVanhelProcessor::class)]
    case CALL_OF_VANHEL;
    #[WizardSpecialRule('WIZARD_NECROMANCY')]
    #[Difficulty(0)]
    #[SpellProcessor(Spells\Necromancy\SpellOfAwakeningProcessor::class)]
    case SPELL_OF_AWAKENING;

    #[WizardSpecialRule('WIZARD_CHAOS_RITUALS')]
    #[Difficulty(10)]
    #[SpellProcessor(Spells\ChaosRituals\VisionOfTormentProcessor::class)]
    case VISION_OF_TORMENT;
    #[WizardSpecialRule('WIZARD_CHAOS_RITUALS')]
    #[Difficulty(7)]
    #[SpellProcessor(Spells\ChaosRituals\EyeOfGodProcessor::class)]
    case EYE_OF_GOD;
    #[WizardSpecialRule('WIZARD_CHAOS_RITUALS')]
    #[Difficulty(8)]
    #[SpellProcessor(Spells\ChaosRituals\DarkBloodProcessor::class)]
    case DARK_BLOOD;
    #[WizardSpecialRule('WIZARD_CHAOS_RITUALS')]
    #[Difficulty(9)]
    #[SpellProcessor(Spells\ChaosRituals\LureOfChaosProcessor::class)]
    case LURE_OD_CHAOS;
    #[WizardSpecialRule('WIZARD_CHAOS_RITUALS')]
    #[Difficulty(7)]
    #[SpellProcessor(Spells\ChaosRituals\WingsOfDarknessProcessor::class)]
    case WINGS_OF_DARKNESS;
    #[WizardSpecialRule('WIZARD_CHAOS_RITUALS')]
    #[Difficulty(3)]
    #[SpellProcessor(Spells\ChaosRituals\WordOfPainProcessor::class)]
    case WORD_OF_PAIN;

    #[WizardSpecialRule('WIZARD_LESSER_MAGIC')]
    #[Difficulty(7)]
    #[SpellProcessor(Spells\LesserMagic\FiresOfUzhulProcessor::class)]
    case FIRES_OF_UZHUL;
    #[WizardSpecialRule('WIZARD_LESSER_MAGIC')]
    #[Difficulty(7)]
    #[SpellProcessor(Spells\LesserMagic\FlightOfZimmeranProcessor::class)]
    case FLIGHT_OF_ZIMMERAN;
    #[WizardSpecialRule('WIZARD_LESSER_MAGIC')]
    #[Difficulty(7)]
    #[SpellProcessor(Spells\LesserMagic\DreadOfAramarProcessor::class)]
    case DREAD_OF_ARAMAR;
    #[WizardSpecialRule('WIZARD_LESSER_MAGIC')]
    #[Difficulty(7)]
    #[SpellProcessor(Spells\LesserMagic\SilverArrowsOfArhaProcessor::class)]
    case SILVER_ARROWS_OF_ARHA;
    #[WizardSpecialRule('WIZARD_LESSER_MAGIC')]
    #[Difficulty(6)]
    #[StateSpecialRule('REROLL_ANY_FAILED')]
    #[SpellProcessor(Spells\LesserMagic\LuckOfShemtekProcessor::class)]
    case LUCK_OF_SHEMTEK;
    #[WizardSpecialRule('WIZARD_LESSER_MAGIC')]
    #[Difficulty(7)]
    #[SpellProcessor(Spells\LesserMagic\SwordOfRezhebelProcessor::class)]
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

    public function getProcessor(): ?Spells\SpellProcessorInterface
    {
        $ref = new \ReflectionClassConstant(self::class, $this->name);
        $classAttributes = $ref->getAttributes(SpellProcessor::class);

        if (count($classAttributes) === 0)
            return null;

        return $classAttributes[0]->newInstance()->getValue();
    }
}