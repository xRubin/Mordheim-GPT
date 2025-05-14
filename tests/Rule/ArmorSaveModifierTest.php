<?php

use Mordheim\Data\Equipment;
use Mordheim\EquipmentManager;

class ArmorSaveModifierTest extends MordheimTestCase
{
    /**
     * @dataProvider equipmentStrengthModifierProvider
     */
    public function testEquipmentTotalArmorSaveModifier(Equipment $equipment, int $fighterStrength, int $expectedModifier)
    {
        $actualStrength = $equipment->getStrength($fighterStrength);

        // Модификатор по таблице Mordheim
        $tableModifier = \Mordheim\Rule\Attack::getStrengthArmorSaveModifier($actualStrength);

        // Модификатор от спецправил оружия
        $equipmentManager = new EquipmentManager([$equipment]);
        $specialModifier = $equipmentManager->getArmorSaveModifier($equipment);

        // Итоговый модификатор: по правилам Mordheim модификаторы складываются (оба отрицательные)
        $totalModifier = $tableModifier - $specialModifier;

        $this->assertEquals(
            $expectedModifier,
            $totalModifier,
            sprintf(
                'Оружие: %s, сила бойца: %d, итоговая сила удара: %d, модификатор должен быть %d (таблица: %d, спецправила: %d)',
                $equipment->getName(),
                $fighterStrength,
                $actualStrength,
                $expectedModifier,
                $tableModifier,
                $specialModifier
            )
        );
    }

    public static function equipmentStrengthModifierProvider(): array
    {
        return [
            // Ближний бой (S=3)
            [Equipment::FIST, 3, 0],
            [Equipment::DAGGER, 3, 0],
            [Equipment::HAMMER, 3, 0],
            [Equipment::STAFF, 3, 0],
            [Equipment::MACE, 3, 0],
            [Equipment::CLUB, 3, 0],
            [Equipment::AXE, 3, -1],
            [Equipment::SWORD, 3, 0],
            [Equipment::FLAIL, 3, -4],
            [Equipment::MORNING_STAR, 3, -1],
            [Equipment::HALBERD, 3, -3],
            [Equipment::SPEAR, 3, 0],
            [Equipment::LANCE, 3, -2],
            [Equipment::DOUBLE_HANDED_SWORD, 3, -4],
            [Equipment::DOUBLE_HANDED_HAMMER, 3, -4],
            [Equipment::DOUBLE_HANDED_AXE, 3, -5],
            // Дальнобойное оружие (S=3)
            [Equipment::SHORT_BOW, 3, 0],
            [Equipment::BOW, 3, 0],
            [Equipment::LONG_BOW, 3, 0],
            [Equipment::ELF_BOW, 3, -1],
            [Equipment::CROSSBOW, 3, -1],
            [Equipment::SLING, 3, 0],
            [Equipment::THROWING_STARS, 3, 0],
            [Equipment::THROWING_KNIVES, 3, 0],
            [Equipment::REPEATER_CROSSBOW, 3, 0],
            [Equipment::CROSSBOW_PISTOL, 3, -1],
            [Equipment::PISTOL, 3, -2],
            [Equipment::DUELLING_PISTOL, 3, -2],
            [Equipment::BLUNDERBUSS, 3, 0],
            [Equipment::HANDGUN, 3, -2],
            [Equipment::HOCHLAND_LONG_RIFFLE, 3, -2],
            // Магия и экзотика (S=3)
            [Equipment::DARK_BLOOD, 3, -2],
            [Equipment::FIRE_OF_UZHUL, 3, -1],
            [Equipment::WORD_OF_PAIN, 3, 0],
            [Equipment::SILVER_ARROW_OF_ARHA, 3, 0],
            // Особое (S=3)
            [Equipment::SWORD_OF_REZHEBEL, 3, -2],

            // Примеры для бойца с силой 8
            // Ближний бой
            [Equipment::FIST, 8, -4],
            [Equipment::FLAIL, 8, -8],
            [Equipment::MORNING_STAR, 8, -6],
            [Equipment::DOUBLE_HANDED_SWORD, 8, -8],
            // Дальнобойное
            [Equipment::CROSSBOW, 8, -1],
            [Equipment::HANDGUN, 8, -2],
            [Equipment::ELF_BOW, 8, -1],
            // Магия и экзотика
            [Equipment::DARK_BLOOD, 8, -2],
            // Особое
            [Equipment::SWORD_OF_REZHEBEL, 8, -6],
        ];
    }
} 