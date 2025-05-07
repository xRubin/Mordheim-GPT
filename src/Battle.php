<?php

namespace Mordheim;

/**
 * Класс для управления боем по правилам Mordheim 1999
 */
class Battle
{
    /** @var GameField */
    protected GameField $field;
    /** @var FighterInterface[] */
    protected array $fighters = [];
    /** @var int */
    protected int $turn = 1;
    /** @var CloseCombatCollection */
    protected CloseCombatCollection $activeCombats;
    /** @var Warband[] */
    protected array $warbands = [];
    /** @var int Индекс активной банды */
    protected int $activeWarbandIndex = 0;

    public function __construct(GameField $field, array $warbands)
    {
        $this->field = $field;
        $this->warbands = $warbands;
        $this->activeCombats = new CloseCombatCollection();
        foreach ($warbands as $wb) {
            foreach ($wb->fighters as $f) {
                $this->fighters[] = $f;
            }
        }
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

        $this->turn++;
        return $this->turn;
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

        foreach ($this->warbands as $warband)
            \Mordheim\Rule\RecoveryPhase::apply($warband, $this->warbands);

        foreach ($this->warbands as $warband)
            $this->phaseMove($warband);

        foreach ($this->warbands as $warband)
            $this->phaseShoot($warband);

        foreach ($this->warbands as $warband)
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
            if ($fighter->getState()->getStatus()->canAct()) {
                $enemies = $this->getEnemiesFor($fighter);
                $fighter->getState()->getBattleStrategy()->movePhase($this, $fighter, $enemies);
            }
        }
    }

    /**
     * Фаза стрельбы
     */
    protected function phaseShoot(Warband $warband): void
    {
        \Mordheim\BattleLogger::add("Фаза стрельбы: {$warband->name}");
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
        foreach ($this->activeCombats->getAll() as $combat) {
            $fighters = $combat->fighters;
            usort($fighters, function (FighterInterface $a, FighterInterface $b) use ($combat) {
                if ($combat->getBonus($a, CloseCombat::BONUS_CHARGED))
                    return ($b->hasSkill('Lightning Reflexes') && !$a->hasSkill('Lightning Reflexes')) ? 1 : -1;

                if ($combat->getBonus($b, CloseCombat::BONUS_CHARGED))
                    return ($a->hasSkill('Lightning Reflexes') && !$b->hasSkill('Lightning Reflexes')) ? -1 : 1;

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
     * @return FighterInterface[]
     */
    public function getEnemiesFor(FighterInterface $target): array
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
     * @param FighterInterface $target
     * @return FighterInterface[]
     */
    public function getAlliesFor(FighterInterface $target): array
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
    protected function isAlly(FighterInterface $a, FighterInterface $b): bool
    {
        foreach ($this->warbands as $wb) {
            if (in_array($a, $wb->fighters, true) && in_array($b, $wb->fighters, true)) {
                return true;
            }
        }
        return false;
    }

    public function killFighter(FighterInterface $fighter): void
    {
        $fighter->getState()->setStatus(Status::OUT_OF_ACTION);
        foreach ($this->getActiveCombats()->getByFighter($fighter) as $combat)
            $this->getActiveCombats()->remove($combat);
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
            if ($this->getField()->getCell($x, $y, $z)->obstacle) {
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
}
