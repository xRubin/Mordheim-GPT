<?php

namespace Mordheim;

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
            $fighter->battleStrategy->resetOnTurn();
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
        foreach ($warband->fighters as $f) {
            if ($f->alive && $f->state !== \Mordheim\FighterState::OUT_OF_ACTION) {
                $enemies = $this->getEnemiesFor($f);
                $f->battleStrategy->movePhase($this, $f, $enemies);
            }
        }
    }

    /**
     * Фаза стрельбы
     */
    protected function phaseShoot(Warband $warband): void
    {
        \Mordheim\BattleLogger::add("Фаза стрельбы: {$warband->name}");
        foreach ($warband->fighters as $f) {
            if ($f->alive && $f->state !== \Mordheim\FighterState::OUT_OF_ACTION) {
                $enemies = $this->getEnemiesFor($f);
                $f->battleStrategy->shootPhase($this, $f, $enemies);
            }
        }
    }

    /**
     * Фаза магии
     */
    protected function phaseMagic(Warband $warband): void
    {
        \Mordheim\BattleLogger::add("Фаза магии: {$warband->name}");
        foreach ($warband->fighters as $f) {
            if ($f->alive && $f->state !== \Mordheim\FighterState::OUT_OF_ACTION) {
                $enemies = $this->getEnemiesFor($f);
                $f->battleStrategy->magicPhase($this, $f, $enemies);
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
            foreach ($combat->fighters as $fighter) {
                if ($fighter->alive && $fighter->state !== \Mordheim\FighterState::OUT_OF_ACTION) {
                    $enemies = $this->getEnemiesFor($fighter);
                    $fighter->battleStrategy->closeCombatPhase($this, $fighter, $enemies);
                }
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
    public function getEnemiesFor(Fighter $fighter): array
    {
        $enemies = [];
        foreach ($this->warbands as $wb) {
            foreach ($wb->fighters as $f) {
                if ($f !== $fighter && $f->alive && $f->state !== \Mordheim\FighterState::OUT_OF_ACTION && !$this->isAlly($fighter, $f)) {
                    $enemies[] = $f;
                }
            }
        }
        return $enemies;
    }

    /**
     * Получить союзников для бойца
     * @param Fighter $fighter
     * @return Fighter[]
     */
    public function getAlliesFor(Fighter $fighter): array
    {
        $allies = [];
        foreach ($this->warbands as $wb) {
            foreach ($wb->fighters as $f) {
                if ($f !== $fighter && $f->alive && $f->state !== \Mordheim\FighterState::OUT_OF_ACTION && $this->isAlly($fighter, $f)) {
                    $allies[] = $f;
                }
            }
        }
        return $allies;
    }

    /**
     * Проверить, союзник ли другой боец
     */
    protected function isAlly(Fighter $a, Fighter $b): bool
    {
        foreach ($this->warbands as $wb) {
            if (in_array($a, $wb->fighters, true) && in_array($b, $wb->fighters, true)) {
                return true;
            }
        }
        return false;
    }

    public function killFighter(Fighter $fighter): void
    {
        $fighter->state = FighterState::OUT_OF_ACTION;
        $fighter->alive = false;
        $fighter->characteristics->wounds = 0;
        foreach ($this->getActiveCombats()->getByFighter($fighter) as $combat)
            $this->getActiveCombats()->remove($combat);
    }
}
