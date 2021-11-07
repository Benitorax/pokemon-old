<?php

namespace App\Entity;

use Symfony\Component\Uid\Uuid;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\Traits\EntityIdTrait;

/**
 * @ORM\Entity(repositoryClass="App\Repository\PokemonExchangeRepository")
 */
class PokemonExchange
{
    use EntityIdTrait;

    public const STATUS_WAITING_FOR_RESPONSE = 'Waiting for response';
    public const STATUS_MODIFIED = 'Modified, waiting for response';
    public const USER_ACCEPT_CONTRACT = 'Accepted';
    public const USER_REFUSE_CONTRACT = 'Refused';
    public const USER_NO_ANSWER_CONTRACT = 'None';

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User")
     * @ORM\JoinColumn(nullable=false)
     */
    private $trainer1;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Pokemon")
     * @ORM\JoinColumn(nullable=false)
     */
    private $pokemon1;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $answer1;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User")
     * @ORM\JoinColumn(nullable=false)
     */
    private $trainer2;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Pokemon")
     * @ORM\JoinColumn(nullable=false)
     */
    private $pokemon2;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $answer2;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $status = self::STATUS_WAITING_FOR_RESPONSE;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $updatedAt;

    public function __construct()
    {
        $this->uuid = Uuid::v4();
    }

    public function getTrainer1(): ?User
    {
        return $this->trainer1;
    }

    public function setTrainer1(?User $trainer1): self
    {
        $this->trainer1 = $trainer1;

        return $this;
    }

    public function getPokemon1(): ?Pokemon
    {
        return $this->pokemon1;
    }

    public function setPokemon1(?Pokemon $pokemon1): self
    {
        $this->pokemon1 = $pokemon1;

        return $this;
    }

    public function getAnswer1(): ?string
    {
        return $this->answer1;
    }

    public function setAnswer1(string $answer1): self
    {
        $this->answer1 = $answer1;

        return $this;
    }

    public function getTrainer2(): ?User
    {
        return $this->trainer2;
    }

    public function setTrainer2(?User $trainer2): self
    {
        $this->trainer2 = $trainer2;

        return $this;
    }

    public function getPokemon2(): ?Pokemon
    {
        return $this->pokemon2;
    }

    public function setPokemon2(?Pokemon $pokemon2): self
    {
        $this->pokemon2 = $pokemon2;

        return $this;
    }

    public function getAnswer2(): ?string
    {
        return $this->answer2;
    }

    public function setAnswer2(string $answer2): self
    {
        $this->answer2 = $answer2;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeInterface $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }
}
