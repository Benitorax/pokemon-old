<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;

/**
 * @ORM\Entity(repositoryClass="App\Repository\BattleRepository")
 */
class Battle
{
    /**
     * @ORM\Id()
     * @ORM\Column(type="uuid", unique=true)
     * @ORM\GeneratedValue(strategy="CUSTOM")
     * @ORM\CustomIdGenerator(class="Ramsey\Uuid\Doctrine\UuidGenerator")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $type;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Habitat")
     * @ORM\JoinColumn(nullable=false)
     */
    private $arena;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\BattleTeam", cascade={"persist"}, orphanRemoval=true)
     * @ORM\JoinColumn(nullable=false)
     */
    private $playerTeam;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\BattleTeam", cascade={"persist"}, orphanRemoval=true)
     * @ORM\JoinColumn(nullable=false)
     */
    private $opponentTeam;

    public function getId(): ?Uuid
    {
        return $this->id;
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

    public function getArena(): ?Habitat
    {
        return $this->arena;
    }

    public function setArena(?Habitat $arena): self
    {
        $this->arena = $arena;

        return $this;
    }

    public function getPlayerTeam(): ?BattleTeam
    {
        return $this->playerTeam;
    }

    public function setPlayerTeam(BattleTeam $playerTeam): self
    {
        $this->playerTeam = $playerTeam;

        return $this;
    }

    public function getOpponentTeam(): ?BattleTeam
    {
        return $this->opponentTeam;
    }

    public function setOpponentTeam(BattleTeam $opponentTeam): self
    {
        $this->opponentTeam = $opponentTeam;

        return $this;
    }
}
