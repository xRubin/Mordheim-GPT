<?php

namespace Mordheim\Data;

use Mordheim\Data\Attributes\Cost;
use Mordheim\Data\Attributes\SpecialRule;
use Mordheim\Exceptions\InvalidAttributesException;
use Mordheim\MutationInterface;

enum Mutation implements MutationInterface
{
    #[Cost(20)]
    #[SpecialRule('MUTATION_DAEMON_SOUL')]
    case DAEMON_SOUL;
    #[Cost(50)]
    #[SpecialRule('MUTATION_GREAT_CLAW')]
    case GREAT_CLAW;
    #[Cost(40)]
    #[SpecialRule('MUTATION_CLOVEN_HOOFS')]
    case CLOVEN_HOOFS;
    #[Cost(35)]
    #[SpecialRule('MUTATION_TENTACLE')]
    case TENTACLE;
    #[Cost(30)]
    #[SpecialRule('MUTATION_BLACKBLOOD')]
    case BLACKBLOOD;
    #[Cost(35)]
    #[SpecialRule('MUTATION_SPINES')]
    case SPINES;
    #[Cost(40)]
    #[SpecialRule('MUTATION_SCORPION_TAIL')]
    case SCORPION_TAIL;
    #[Cost(40)]
    #[SpecialRule('MUTATION_EXTRA_ARM')]
    case EXTRA_ARM;
    #[Cost(40)]
    #[SpecialRule('CAUSE_FEAR')]
    case HIDEOUS;

    public function getCost(): int
    {
        $ref = new \ReflectionClassConstant(self::class, $this->name);
        $classAttributes = $ref->getAttributes(Cost::class);

        if (count($classAttributes) === 0)
            throw new InvalidAttributesException('Invalid attributes for: ' . $this->name);

        return $classAttributes[0]->newInstance()->getValue();
    }

    public function getSpecialRules(): array
    {
        $ref = new \ReflectionClassConstant(self::class, $this->name);
        $classAttributes = $ref->getAttributes(SpecialRule::class);

        if (count($classAttributes) === 0)
            return [];

        return array_map(
            fn($attribute) => $attribute->newInstance()->getValue(),
            $classAttributes
        );
    }
}