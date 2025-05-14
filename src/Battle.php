<?php

namespace Mordheim;

use Mordheim\Data\Spell;
use Mordheim\Exceptions\MoveRunDeprecatedException;
use Mordheim\Exceptions\PathfinderTargetUnreachableException;
use Mordheim\Rule\RecoveryPhase;
use SplObjectStorage;

/**
 * Класс для управления боем по правилам Mordheim 1999
 */
class Battle
{
    /** @var GameField */
    protected GameField $field;
    /** @var Fighter[] */
    protected array $fighters = [];
    /** @var int */
    protected int $turn = 1;
    /** @var CloseCombatCollection */
    protected CloseCombatCollection $activeCombats;
    /** @var Warband[] */
    protected array $warbands = [];
    /** @var int Индекс активной банды */
    protected int $activeWarbandIndex = 0;
    /**
     * Активные заклинания на поле боя
     * @var SplObjectStorage<Fighter, SpellInterface[]>
     */
    protected SplObjectStorage $activeSpells;

    public function __construct(GameField $field, array $warbands)
    {
        $this->field = $field;
        $this->warbands = $warbands;
        $this->activeCombats = new CloseCombatCollection();
        $this->activeSpells = new SplObjectStorage();
        foreach ($warbands as $wb) {
            foreach ($wb->fighters as $fighter) {
                $this->addFighter($fighter);
            }
        }
    }

    public function addFighter(Fighter $fighter): static
    {
        $this->fighters[] = $fighter;
        return $this;
    }

    /**
     * Получить текущий номер хода
     */
    public function getTurn(): int
    {
        return $this->turn;
    }

    public function nextTurn(): int
    {
        foreach ($this->fighters as $fighter) {
            $fighter->getState()->getBattleStrategy()->resetOnTurn();
        }

        foreach ($this->activeCombats as $combat) {
            $combat->nextTurn();
        }

        return ++$this->turn;
    }

    /**
     * Получить список активных рукопашных боёв
     */
    public function getActiveCombats(): CloseCombatCollection
    {
        return $this->activeCombats;
    }

    /**
     * Контроль очередности ходов по правилам Mordheim 1999
     * (фазы: движение, стрельба, магия, рукопашный бой)
     */
    public function playTurn(): void
    {
        \Mordheim\BattleLogger::add("Ход #{$this->turn}");
        $actWarbands = $this->warbands;
        $routed = [];

        foreach ($actWarbands as $warband) {
            foreach ($warband->fighters as $fighter) {
                if (!RecoveryPhase::applyPsychology($this, $fighter, $warband, $this->warbands)) {
                    $fighter->getState()->getBattleStrategy()->spentAll();
                }
            }
        }

        foreach ($actWarbands as $warband) {
            if (!RecoveryPhase::applyRoutTest($warband, $this->warbands)) {
                $routed[] = $warband;
                $this->phaseMove($warband);
            }
        }

        $actWarbands = array_filter(
            $actWarbands,
            fn($wb) => !in_array($wb, $routed, true)
        );

        foreach ($actWarbands as $warband)
            $this->phaseMove($warband);

        foreach ($actWarbands as $warband)
            $this->phaseShoot($warband);

        foreach ($actWarbands as $warband)
            $this->phaseMagic($warband);

        $this->phaseCloseCombat();

        $this->nextTurn();
    }

    /**
     * Фаза движения
     */
    protected function phaseMove(Warband $warband): void
    {
        \Mordheim\BattleLogger::add("Фаза движения: {$warband->name}");
        foreach ($warband->fighters as $fighter) {
            $status = $fighter->getState()->getStatus();
            if ($status === Status::PANIC) {
                foreach ($this->activeCombats->getByFighter($fighter) as $combat)
                    $this->activeCombats->remove($combat);
                // --- Паника: бежим к ближайшему краю ---
                self::runAwayInPanic($fighter);
                continue;
            }
            if ($status->canAct()) {
                $enemies = $this->getEnemiesFor($fighter);
                $fighter->getState()->getBattleStrategy()->movePhase($this, $fighter, $enemies);
            }
        }
    }

    public function runAwayInPanic(Fighter $fighter): void
    {
        [$x, $y, $z] = $fighter->getState()->getPosition();
        $field = $this->getField();
        $edges = [
            [0, $y, $z],
            [$field->getWidth() - 1, $y, $z],
            [$x, 0, $z],
            [$x, $field->getLength() - 1, $z],
        ];

        // Сортируем по расстоянию до текущей позиции
        usort($edges, fn($a, $b) => (abs($x - $a[0]) + abs($y - $a[1])) <=> (abs($x - $b[0]) + abs($y - $b[1])));

        // Ищем ближайшую незанятую клетку на краю
        $target = null;
        foreach ($edges as $edge) {
            $occupied = false;
            foreach ($this->getFighters() as $f) {
                if ($f !== $fighter && $f->getState()->getPosition() === $edge && $f->getState()->getStatus()->isAlive()) {
                    $occupied = true;
                    break;
                }
            }
            if (!$occupied && !$field->getCell($edge[0], $edge[1], $edge[2])->obstacle) {
                $target = $edge;
                break;
            }
        }

        if ($target === null) {
            \Mordheim\BattleLogger::add("{$fighter->getName()} не может убежать в панике: все края заняты.");
            return;
        }

        try {
            \Mordheim\Rule\Move::run($this, $fighter, $target, 0.4, false); // минимальная агрессивность
        } catch (MoveRunDeprecatedException $e) {
            \Mordheim\BattleLogger::add("{$fighter->getName()} не может бежать в панике: " . $e->getMessage());
        } catch (PathfinderTargetUnreachableException $e) {
            \Mordheim\BattleLogger::add("{$fighter->getName()} не может убежать в панике: путь до края недоступен. (" . implode(", ", $target) . ")");
        }
    }

    /**
     * Фаза стрельбы
     */
    protected function phaseShoot(Warband $warband): void
    {
        \Mordheim\BattleLogger::add("Фаза стрельбы: {$warband->name}");
        foreach ($warband->fighters as $fighter) {
            foreach ($fighter->getState()->getActiveSpells() as $spell)
                $spell->getProcessor()?->onPhaseShoot($this, $fighter);
        }
        foreach ($warband->fighters as $fighter) {
            if ($fighter->getState()->getStatus()->canAct()) {
                $enemies = $this->getEnemiesFor($fighter);
                $fighter->getState()->getBattleStrategy()->shootPhase($this, $fighter, $enemies);
            }
        }
    }

    /**
     * Фаза магии
     */
    protected function phaseMagic(Warband $warband): void
    {
        \Mordheim\BattleLogger::add("Фаза магии: {$warband->name}");
        foreach ($warband->fighters as $fighter) {
            if ($fighter->getState()->getStatus()->canAct()) {
                $enemies = $this->getEnemiesFor($fighter);
                $fighter->getState()->getBattleStrategy()->magicPhase($this, $fighter, $enemies);
            }
        }
    }

    /**
     * Фаза рукопашного боя
     */
    protected function phaseCloseCombat(): void
    {
        \Mordheim\BattleLogger::add("Фаза рукопашного боя");
        foreach ($this->getActiveCombats()->getAll() as $combat) {
            $fighters = $combat->fighters;
            usort($fighters, function (Fighter $a, Fighter $b) use ($combat) {
                if ($combat->getBonus($a, CloseCombat::BONUS_CHARGED))
                    return ($b->hasSpecialRule(SpecialRule::LIGHTNING_REFLEXES) && !$a->hasSpecialRule(SpecialRule::LIGHTNING_REFLEXES)) ? 1 : -1;

                if ($combat->getBonus($b, CloseCombat::BONUS_CHARGED))
                    return ($a->hasSpecialRule(SpecialRule::LIGHTNING_REFLEXES) && !$b->hasSpecialRule(SpecialRule::LIGHTNING_REFLEXES)) ? -1 : 1;

                if ($a->getInitiative() === $b->getInitiative())
                    return mt_rand(0, 1) ? 1 : -1;

                return $b->getInitiative() - $a->getInitiative();
            });

            foreach ($fighters as $fighter) {
                if ($fighter->getState()->getStatus()->canAct()) {
                    foreach ($fighters as $target) {
                        if ($fighter !== $target)
                            $fighter->getState()->getBattleStrategy()->closeCombatPhase($this, $fighter, [$target]);
                    }
                }
            }
        }

        foreach ($this->getFighters() as $fighter) {
            if ($fighter->getState()->getStatus()->canAct()) {
                $enemies = $this->getEnemiesFor($fighter);
                $fighter->getState()->getBattleStrategy()->closeCombatPhase($this, $fighter, $enemies);
            }
        }
    }

    /**
     * Получить поле боя
     */
    public function getField(): GameField
    {
        return $this->field;
    }

    /**
     * Получить всех бойцов
     */
    public function getFighters(): array
    {
        return $this->fighters;
    }

    /**
     * Получить врагов для бойца
     * @return Fighter[]
     */
    public function getEnemiesFor(Fighter $target): array
    {
        $enemies = [];
        foreach ($this->warbands as $wb) {
            foreach ($wb->fighters as $fighter) {
                if ($fighter !== $target && $fighter->getState()->getStatus()->canAct() && !$this->isAlly($target, $fighter)) {
                    $enemies[] = $fighter;
                }
            }
        }
        return $enemies;
    }

    /**
     * Получить союзников для бойца
     * @param Fighter $target
     * @return Fighter[]
     */
    public function getAlliesFor(Fighter $target): array
    {
        $allies = [];
        foreach ($this->warbands as $wb) {
            foreach ($wb->fighters as $fighter) {
                if ($fighter !== $target && $fighter->getState()->getStatus()->canAct() && $this->isAlly($target, $fighter)) {
                    $allies[] = $fighter;
                }
            }
        }
        return $allies;
    }

    /**
     * Проверить, союзник ли другой боец
     */
    public function isAlly(Fighter $a, Fighter $b): bool
    {
        foreach ($this->warbands as $wb) {
            if (in_array($a, $wb->fighters, true) && in_array($b, $wb->fighters, true)) {
                return true;
            }
            if ($b->getState()->hasActiveSpell(Spell::LURE_OD_CHAOS))
                return true;
        }
        return false;
    }

    public function killFighter(Fighter $fighter): void
    {
        $fighter->getState()->setStatus(Status::OUT_OF_ACTION);
        foreach ($this->getActiveCombats()->getByFighter($fighter) as $combat)
            $this->getActiveCombats()->remove($combat);
        $this->activeSpells->detach($fighter);
        $fighter->getState()->setActiveSpells([]);
    }

    /**
     * Проверяет есть ли препятствие между двумя координатами
     * @param array $start [x,y,z]
     * @param array $end [x,y,z]
     * @return bool
     */
    public function hasObstacleBetween(array $start, array $end): bool
    {
        $x1 = $start[0];
        $y1 = $start[1];
        $z1 = $start[2];
        $x2 = $end[0];
        $y2 = $end[1];
        $z2 = $end[2];

        // Если координаты совпадают, нет препятствия
        if ($x1 === $x2 && $y1 === $y2 && $z1 === $z2) {
            return false;
        }

        // Проверяем все целые точки на пути между координатами
        $dx = abs($x2 - $x1);
        $dy = abs($y2 - $y1);
        $dz = abs($z2 - $z1);
        $x = $x1;
        $y = $y1;
        $z = $z1;
        $n = 1 + $dx + $dy + $dz;
        $x_inc = ($x2 > $x1) ? 1 : -1;
        $y_inc = ($y2 > $y1) ? 1 : -1;
        $z_inc = ($z2 > $z1) ? 1 : -1;
        $error = $dx - $dy;
        $error2 = $dx - $dz;

        for ($i = 0; $i < $n; $i++) {
            $cell = $this->getField()->getCell($x, $y, $z);
            if (null === $cell || $cell->obstacle) {
                return true;
            }

            $e2 = 2 * $error;
            if ($e2 > -$dy) {
                $error -= $dy;
                $x += $x_inc;
            }
            if ($e2 < $dx) {
                $error += $dx;
                $y += $y_inc;
            }

            $e2 = 2 * $error2;
            if ($e2 > -$dz) {
                $error2 -= $dz;
                $x += $x_inc;
            }
            if ($e2 < $dx) {
                $error2 += $dx;
                $z += $z_inc;
            }
        }

        return false;
    }

    /**
     * Получить активные заклинания для бойца
     * @param Fighter $fighter
     * @return SpellInterface[]
     */
    public function getActiveSpellsFor(Fighter $fighter): array
    {
        return $this->activeSpells->contains($fighter) ? $this->activeSpells[$fighter] : [];
    }

    /**
     * Добавить активное заклинание для бойца
     * @param Fighter $fighter
     * @param SpellInterface $spell
     */
    public function addActiveSpell(Fighter $fighter, SpellInterface $spell): void
    {
        $spells = $this->activeSpells->contains($fighter) ? $this->activeSpells[$fighter] : [];
        $spells[] = $spell;
        $this->activeSpells[$fighter] = $spells;
        $fighter->getState()->addActiveSpell($spell);
    }

    /**
     * Удалить активное заклинание для бойца
     * @param Fighter $fighter
     * @param SpellInterface $spell
     */
    public function removeActiveSpell(Fighter $fighter, SpellInterface $spell): void
    {
        if (!$this->activeSpells->contains($fighter)) return;
        $spells = array_filter(
            $this->activeSpells[$fighter],
            fn($s) => $s !== $spell
        );
        if (empty($spells)) {
            $this->activeSpells->detach($fighter);
        } else {
            $this->activeSpells[$fighter] = $spells;
        }
        $fighter->getState()->removeActiveSpell($spell);
    }

    /**
     * Найти ближайшую свободную клетку в радиусе radius от позиции бойца
     * @param Fighter $fighter
     * @param int $radius
     * @return array|null [x, y, z] или null если нет свободных
     */
    public function findUnoccupiedPosition(Fighter $fighter, int $radius): ?array
    {
        $field = $this->getField();
        $pos = $fighter->getState()->getPosition();
        for ($r = 1; $r <= $radius; $r++) {
            for ($dx = -$r; $dx <= $r; $dx++) {
                for ($dy = -$r; $dy <= $r; $dy++) {
                    for ($dz = -$r; $dz <= $r; $dz++) {
                        if (abs($dx) + abs($dy) + abs($dz) > $r) continue;
                        $cell = [$pos[0] + $dx, $pos[1] + $dy, $pos[2] + $dz];
                        if ($cell == $pos) continue;
                        if ($field->isOutOfBounds($cell[0], $cell[1], $cell[2])) continue;
                        if ($field->getCell($cell[0], $cell[1], $cell[2])->obstacle) continue;
                        $occupied = false;
                        foreach ($this->getFighters() as $f) {
                            if ($f->getState()->getPosition() == $cell) {
                                $occupied = true;
                                break;
                            }
                        }
                        if (!$occupied) {
                            return $cell;
                        }
                    }
                }
            }
        }
        return null;
    }
}
