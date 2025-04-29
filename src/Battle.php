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
    /** @var CloseCombat[] */
    protected array $activeCombats = [];
    /** @var Warband[] */
    protected array $warbands = [];
    /** @var int Индекс активной банды */
    protected int $activeWarbandIndex = 0;

    public function __construct(GameField $field, array $warbands)
    {
        $this->field = $field;
        $this->warbands = $warbands;
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

    /**
     * Получить активную банду
     */
    public function getActiveWarband(): Warband
    {
        return $this->warbands[$this->activeWarbandIndex];
    }

    /**
     * Перейти к следующей банде (по очереди)
     */
    public function nextWarband(): void
    {
        $this->activeWarbandIndex = ($this->activeWarbandIndex + 1) % count($this->warbands);
        if ($this->activeWarbandIndex === 0) {
            $this->turn++;
        }
    }

    /**
     * Добавить активный рукопашный бой
     */
    public function addCombat(CloseCombat $combat): void
    {
        $this->activeCombats[] = $combat;
    }

    /**
     * Удалить завершённый рукопашный бой
     */
    public function removeCombat(CloseCombat $combat): void
    {
        foreach ($this->activeCombats as $k => $c) {
            if ($c === $combat) {
                unset($this->activeCombats[$k]);
            }
        }
        $this->activeCombats = array_values($this->activeCombats);
    }

    /**
     * Получить список активных рукопашных боёв
     */
    public function getActiveCombats(): array
    {
        return $this->activeCombats;
    }

    /**
     * Контроль очередности ходов по правилам Mordheim 1999
     * (фазы: движение, стрельба, магия, рукопашный бой)
     */
    public function playTurn(): void
    {
        $warband = $this->getActiveWarband();
        \Mordheim\BattleLogger::add("Ход #{$this->turn}, активная банда: {$warband->name}");
        \Mordheim\Rule\RecoveryPhase::apply($warband, $this->warbands);
        $this->phaseMove($warband);
        $this->phaseShoot($warband);
        $this->phaseMagic($warband);
        $this->phaseCloseCombat();
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
                $f->battleStrategy->movePhase($f, $enemies, $this->field);
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
                $f->battleStrategy->shootPhase($f, $enemies, $this->field);
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
                $f->battleStrategy->magicPhase($f, $enemies, $this->field);
            }
        }
    }

    /**
     * Фаза рукопашного боя
     */
    protected function phaseCloseCombat(): void
    {
        \Mordheim\BattleLogger::add("Фаза рукопашного боя");
        foreach ($this->warbands as $warband) {
            foreach ($warband->fighters as $f) {
                if ($f->alive && $f->state !== \Mordheim\FighterState::OUT_OF_ACTION) {
                    $enemies = $this->getEnemiesFor($f);
                    $f->battleStrategy->closeCombatPhase($f, $enemies, $this->field);
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
     */
    protected function getEnemiesFor(Fighter $self): array
    {
        $enemies = [];
        foreach ($this->warbands as $wb) {
            foreach ($wb->fighters as $f) {
                if ($f !== $self && $f->alive && $f->state !== \Mordheim\FighterState::OUT_OF_ACTION && !$this->isAlly($self, $f)) {
                    $enemies[] = $f;
                }
            }
        }
        return $enemies;
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
}
