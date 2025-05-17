<?php

namespace Mordheim;

use Mordheim\Attributes\Cost;
use Mordheim\Attributes\Rule;
use Mordheim\Exceptions\InvalidAttributesException;

enum Mutation
{
    #[Cost(20)]
    #[Rule('MUTATION_DAEMON_SOUL')]
    case DAEMON_SOUL;
    #[Cost(50)]
    #[Rule('MUTATION_GREAT_CLAW')]
    case GREAT_CLAW;
    #[Cost(40)]
    #[Rule('MUTATION_CLOVEN_HOOFS')]
    case CLOVEN_HOOFS;
    #[Cost(35)]
    #[Rule('MUTATION_TENTACLE')]
    case TENTACLE;
    #[Cost(30)]
    #[Rule('MUTATION_BLACKBLOOD')]
    case BLACKBLOOD;
    #[Cost(35)]
    #[Rule('MUTATION_SPINES')]
    case SPINES;
    #[Cost(40)]
    #[Rule('MUTATION_SCORPION_TAIL')]
    case SCORPION_TAIL;
    #[Cost(40)]
    #[Rule('MUTATION_EXTRA_ARM')]
    case EXTRA_ARM;
    #[Cost(40)]
    #[Rule('CAUSE_FEAR')]
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
        $classAttributes = $ref->getAttributes(Rule::class);

        if (count($classAttributes) === 0)
            return [];

        return array_map(
            fn($attribute) => $attribute->newInstance()->getValue(),
            $classAttributes
        );
    }
}