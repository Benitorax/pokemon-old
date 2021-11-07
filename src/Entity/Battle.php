<?php

namespace App\Entity;

use Symfony\Component\Uid\Uuid;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\Traits\EntityIdTrait;

/**
 * @ORM\Entity(repositoryClass="App\Repository\BattleRepository")
 */
class Battle
{
    use EntityIdTrait;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private string $type;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Habitat")
     * @ORM\JoinColumn(nullable=false)
     */
    private Habitat $arena;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\BattleTeam", cascade={"persist"}, orphanRemoval=true)
     * @ORM\JoinColumn(nullable=false)
     */
    private BattleTeam $playerTeam;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\BattleTeam", cascade={"persist"}, orphanRemoval=true)
     * @ORM\JoinColumn(nullable=false)
     */
    private BattleTeam $opponentTeam;

    /**
     * @ORM\Column(type="string", length=50)
     */
    private string $turn = 'player';

    /**
     * @ORM\Column(type="boolean")
     */
    private bool $isStart = false;

    /**
     * @ORM\Column(type="boolean")
     */
    private bool $isEnd = false;

    public function __construct()
    {
        $this->uuid = Uuid::v4();
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getArena(): Habitat
    {
        return $this->arena;
    }

    public function setArena(Habitat $arena): self
    {
        $this->arena = $arena;

        return $this;
    }

    public function getPlayerTeam(): BattleTeam
    {
        return $this->playerTeam;
    }

    public function setPlayerTeam(BattleTeam $playerTeam): self
    {
        $this->playerTeam = $playerTeam;

        return $this;
    }

    public function getOpponentTeam(): BattleTeam
    {
        return $this->opponentTeam;
    }

    public function setOpponentTeam(BattleTeam $opponentTeam): self
    {
        $this->opponentTeam = $opponentTeam;

        return $this;
    }

    public function getTurn(): ?string
    {
        return $this->turn;
    }

    public function setTurn(string $turn): self
    {
        if (in_array($turn, ['player', 'opponent'])) {
            $this->turn = $turn;
        }

        return $this;
    }

    public function getIsStart(): ?bool
    {
        return $this->isStart;
    }

    public function setIsStart(bool $isStart): self
    {
        $this->isStart = $isStart;

        return $this;
    }

    public function getIsEnd(): ?bool
    {
        return $this->isEnd;
    }

    public function setIsEnd(bool $isEnd): self
    {
        $this->isEnd = $isEnd;

        return $this;
    }
}
