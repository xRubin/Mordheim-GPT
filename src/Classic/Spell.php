<?php

namespace Mordheim\Classic;

use Mordheim\Classic\Attributes\Difficulty;
use Mordheim\Classic\Attributes\SpellProcessor;
use Mordheim\Classic\Attributes\StateRule;
use Mordheim\Classic\Attributes\WizardRule;
use Mordheim\Exceptions\InvalidAttributesException;
use Mordheim\Classic\Spells;

enum Spell
{
    #[WizardRule('PRAYERS_OF_SIGMAR')]
    #[Difficulty(7)]
    #[StateRule('PLUS_2_STRENGTH'), StateRule('DOUBLE_DAMAGE')]
    #[SpellProcessor(Spells\PrayersOfSigmar\HammerOfSigmarProcessor::class)]
    case HAMMER_OF_SIGMAR;
    #[WizardRule('PRAYERS_OF_SIGMAR')]
    #[Difficulty(8)]
    #[SpellProcessor(Spells\PrayersOfSigmar\HeartsOfSteelProcessor::class)]
    case HEARTS_OF_STEEL;
    #[StateRule('IMMUNE_TO_PSYCHOLOGY'), StateRule('FEARSOME')]
    case HEARTS_OF_STEEL_TARGET;
    #[WizardRule('PRAYERS_OF_SIGMAR')]
    #[Difficulty(9)]
    #[SpellProcessor(Spells\PrayersOfSigmar\SoulfireProcessor::class)]
    case SOULFIRE;
    #[WizardRule('PRAYERS_OF_SIGMAR')]
    #[Difficulty(6)]
    #[StateRule('IMMUNE_TO_SPELLS')]
    #[SpellProcessor(Spells\PrayersOfSigmar\ShieldOfFaithProcessor::class)]
    case SHIELD_OF_FAITH;
    #[WizardRule('PRAYERS_OF_SIGMAR')]
    #[Difficulty(5)]
    #[SpellProcessor(Spells\PrayersOfSigmar\HealingHandProcessor::class)]
    case HEALING_HAND;
    #[WizardRule('PRAYERS_OF_SIGMAR')]
    #[Difficulty(9)]
    #[StateRule('FEARSOME'), StateRule('CAUSE_FEAR'), StateRule('SAVE_2')]
    #[SpellProcessor(Spells\PrayersOfSigmar\ArmourOfRighteousnessProcessor::class)]
    case ARMOUR_OF_RIGHTEOUSNESS;

    #[WizardRule('WIZARD_NECROMANCY')]
    #[Difficulty(10)]
    #[SpellProcessor(Spells\Necromancy\LifestealerProcessor::class)]
    case LIFESTEALER;
    #[WizardRule('WIZARD_NECROMANCY')]
    #[Difficulty(5)]
    #[SpellProcessor(Spells\Necromancy\ReAnimationProcessor::class)]
    case RE_ANIMATION;
    #[WizardRule('WIZARD_NECROMANCY')]
    #[Difficulty(6)]
    #[StateRule('CAUSE_FEAR')]
    #[SpellProcessor(Spells\Necromancy\DeathVisionProcessor::class)]
    case DEATH_VISION;
    #[WizardRule('WIZARD_NECROMANCY')]
    #[Difficulty(9)]
    #[SpellProcessor(Spells\Necromancy\SpellOfDoomProcessor::class)]
    case SPELL_OF_DOOM;
    #[WizardRule('WIZARD_NECROMANCY')]
    #[Difficulty(6)]
    #[SpellProcessor(Spells\Necromancy\CallOfVanhelProcessor::class)]
    case CALL_OF_VANHEL;
    #[WizardRule('WIZARD_NECROMANCY')]
    #[Difficulty(0)]
    #[SpellProcessor(Spells\Necromancy\SpellOfAwakeningProcessor::class)]
    case SPELL_OF_AWAKENING;

    #[WizardRule('WIZARD_CHAOS_RITUALS')]
    #[Difficulty(10)]
    #[SpellProcessor(Spells\ChaosRituals\VisionOfTormentProcessor::class)]
    case VISION_OF_TORMENT;
    #[WizardRule('WIZARD_CHAOS_RITUALS')]
    #[Difficulty(7)]
    #[SpellProcessor(Spells\ChaosRituals\EyeOfGodProcessor::class)]
    case EYE_OF_GOD;
    #[WizardRule('WIZARD_CHAOS_RITUALS')]
    #[Difficulty(8)]
    #[SpellProcessor(Spells\ChaosRituals\DarkBloodProcessor::class)]
    case DARK_BLOOD;
    #[WizardRule('WIZARD_CHAOS_RITUALS')]
    #[Difficulty(9)]
    #[SpellProcessor(Spells\ChaosRituals\LureOfChaosProcessor::class)]
    case LURE_OD_CHAOS;
    #[WizardRule('WIZARD_CHAOS_RITUALS')]
    #[Difficulty(7)]
    #[SpellProcessor(Spells\ChaosRituals\WingsOfDarknessProcessor::class)]
    case WINGS_OF_DARKNESS;
    #[WizardRule('WIZARD_CHAOS_RITUALS')]
    #[Difficulty(3)]
    #[SpellProcessor(Spells\ChaosRituals\WordOfPainProcessor::class)]
    case WORD_OF_PAIN;

    #[WizardRule('WIZARD_LESSER_MAGIC')]
    #[Difficulty(7)]
    #[SpellProcessor(Spells\LesserMagic\FiresOfUzhulProcessor::class)]
    case FIRES_OF_UZHUL;
    #[WizardRule('WIZARD_LESSER_MAGIC')]
    #[Difficulty(7)]
    #[SpellProcessor(Spells\LesserMagic\FlightOfZimmeranProcessor::class)]
    case FLIGHT_OF_ZIMMERAN;
    #[WizardRule('WIZARD_LESSER_MAGIC')]
    #[Difficulty(7)]
    #[SpellProcessor(Spells\LesserMagic\DreadOfAramarProcessor::class)]
    case DREAD_OF_ARAMAR;
    #[WizardRule('WIZARD_LESSER_MAGIC')]
    #[Difficulty(7)]
    #[SpellProcessor(Spells\LesserMagic\SilverArrowsOfArhaProcessor::class)]
    case SILVER_ARROWS_OF_ARHA;
    #[WizardRule('WIZARD_LESSER_MAGIC')]
    #[Difficulty(6)]
    #[StateRule('REROLL_ANY_FAILED')]
    #[SpellProcessor(Spells\LesserMagic\LuckOfShemtekProcessor::class)]
    case LUCK_OF_SHEMTEK;
    #[WizardRule('WIZARD_LESSER_MAGIC')]
    #[Difficulty(7)]
    #[SpellProcessor(Spells\LesserMagic\SwordOfRezhebelProcessor::class)]
    case SWORD_OF_REZHEBEL;

    #[WizardRule('WIZARD_MAGIC_OF_THE_HORNED_RAT')]
    #[Difficulty(8)]
    #[SpellProcessor(Spells\MagicOfTheHornedRat\WarpfireProcessor::class)]
    case WARPFIRE;
    #[WizardRule('WIZARD_MAGIC_OF_THE_HORNED_RAT')]
    #[Difficulty(0)]
    #[SpellProcessor(Spells\MagicOfTheHornedRat\ChildrenOfTheHornedRatProcessor::class)]
    case CHILDREN_OF_THE_HORNED_RAT;
    #[WizardRule('WIZARD_MAGIC_OF_THE_HORNED_RAT')]
    #[Difficulty(8)]
    #[SpellProcessor(Spells\MagicOfTheHornedRat\GnawdoomProcessor::class)]
    case GNAWDOOM;
    #[WizardRule('WIZARD_MAGIC_OF_THE_HORNED_RAT')]
    #[Difficulty(4)]
    #[StateRule('PLUS_1_STRENGTH')]
    #[SpellProcessor(Spells\MagicOfTheHornedRat\BlackFuryProcessor::class)]
    case BLACK_FURY;
    #[WizardRule('WIZARD_MAGIC_OF_THE_HORNED_RAT')]
    #[Difficulty(8)]
    #[SpellProcessor(Spells\MagicOfTheHornedRat\EyeOfTheWarpProcessor::class)]
    case EYE_OF_THE_WARP;
    #[WizardRule('WIZARD_MAGIC_OF_THE_HORNED_RAT')]
    #[Difficulty(6)]
    #[SpellProcessor(Spells\MagicOfTheHornedRat\SorcerersCurseProcessor::class)]
    case SORCERERS_CURSE;

    public function getOwnerSpecialRule(): SpecialRule
    {
        $ref = new \ReflectionClassConstant(self::class, $this->name);
        $classAttributes = $ref->getAttributes(WizardRule::class);

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
     * @return SpecialRule[]
     */
    public function getStateRules(): array
    {
        $ref = new \ReflectionClassConstant(self::class, $this->name);
        $classAttributes = $ref->getAttributes(StateRule::class);

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