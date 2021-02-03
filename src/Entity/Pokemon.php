<?php

namespace App\Entity;

use Ramsey\Uuid\Uuid;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass="App\Repository\PokemonRepository")
 */
class Pokemon
{
    /**
     * @ORM\Id()
     * @ORM\Column(type="uuid", unique=true)
     * @ORM\GeneratedValue(strategy="CUSTOM")
     * @ORM\CustomIdGenerator(class="Ramsey\Uuid\Doctrine\UuidGenerator")
     * @Groups("selection")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"battle", "selection"})
     */
    private $name;

    /**
     * @ORM\Column(type="integer")
     * @Groups("selection")
     */
    private $level;

    /**
     * @ORM\Column(type="integer")
     */
    private $apiId;

    /**
     * @ORM\Column(type="integer")
     */
    private $evolutionChainId;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups("battle")
     */
    private $spriteFrontUrl;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups("battle")
     */
    private $spriteBackUrl;

    /**
     * @ORM\Column(type="integer")
     */
    private $captureRate;

    /**
     * @ORM\Column(type="integer")
     * @Groups("battle")
     */
    private $healthPoint = 100;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $SleeptAt;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isSleep = false;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="pokemons")
     */
    private $trainer;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Habitat", cascade={"persist"})
     * @ORM\JoinColumn(nullable=false)
     */
    private $habitat;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\BattleTeam", inversedBy="pokemons")
     */
    private $battleTeam;

    public function getId(): ?\Ramsey\Uuid\Lazy\LazyUuidFromString
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = ucfirst($name);

        return $this;
    }

    public function getLevel(): ?int
    {
        return $this->level;
    }

    public function setLevel(int $level): self
    {
        $this->level = $level;

        return $this;
    }

    public function increaseLevel(int $level): self
    {
        $this->level += $level;
        if($this->level > 100) { $this->level = 100; }

        return $this;
    }

    public function getApiId(): ?int
    {
        return $this->apiId;
    }

    public function setApiId(int $apiId): self
    {
        $this->apiId = $apiId;

        return $this;
    }

    public function getEvolutionChainId(): ?int
    {
        return $this->evolutionChainId;
    }

    public function setEvolutionChainId(int $evolutionChainId): self
    {
        $this->evolutionChainId = $evolutionChainId;

        return $this;
    }

    public function getSpriteFrontUrl(): ?string
    {
        return $this->spriteFrontUrl;
    }

    public function setSpriteFrontUrl(string $spriteFrontUrl): self
    {
        $this->spriteFrontUrl = $spriteFrontUrl;

        return $this;
    }

    public function getSpriteBackUrl(): ?string
    {
        return $this->spriteBackUrl;
    }

    public function setSpriteBackUrl(string $spriteBackUrl): self
    {
        $this->spriteBackUrl = $spriteBackUrl;

        return $this;
    }

    public function getCaptureRate(): ?int
    {
        return $this->captureRate;
    }

    public function setCaptureRate(int $captureRate): self
    {
        $this->captureRate = $captureRate;

        return $this;
    }

    public function getSleeptAt(): ?\DateTimeInterface
    {
        return $this->SleeptAt;
    }

    public function setSleeptAt(?\DateTimeInterface $SleeptAt): self
    {
        $this->SleeptAt = $SleeptAt;

        return $this;
    }

    public function getIsSleep(): ?bool
    {
        return $this->isSleep;
    }

    public function setIsSleep(bool $isSleep): self
    {
        $this->isSleep = $isSleep;

        return $this;
    }

    public function getTrainer(): ?User
    {
        return $this->trainer;
    }

    public function setTrainer(?UserInterface $trainer): self
    {
        $this->trainer = $trainer;

        return $this;
    }

    public function getHabitat(): ?Habitat
    {
        return $this->habitat;
    }

    public function setHabitat(?Habitat $habitat): self
    {
        $this->habitat = $habitat;

        return $this;
    }

    public function getHealthPoint(): ?int
    {
        return $this->healthPoint;
    }

    public function setHealthPoint(int $healthPoint): self
    {
        $this->healthPoint = $healthPoint;

        return $this;
    }

    public function decreaseHealthPoint(int $healthPoint): self
    {
        $this->healthPoint -= $healthPoint;
        if($this->healthPoint <= 0) {
            $this->healthPoint = 0;
            $this->setIsSleep(true); 
        }

        return $this;
    }

    public function increaseHealthPoint(int $healthPoint): self
    {
        $this->healthPoint += $healthPoint;
        if($this->healthPoint > 100) {
            $this->healthPoint = 100;
        }

        return $this;
    }

    public function getBattleTeam(): ?BattleTeam
    {
        return $this->battleTeam;
    }

    public function setBattleTeam(?BattleTeam $battleTeam): self
    {
        $this->battleTeam = $battleTeam;

        return $this;
    }
}
