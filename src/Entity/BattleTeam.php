<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;

/**
 * @ORM\Entity(repositoryClass="App\Repository\BattleTeamRepository")
 */
class BattleTeam
{
    /**
     * @ORM\Id()
     * @ORM\Column(type="uuid", unique=true)
     * @ORM\GeneratedValue(strategy="CUSTOM")
     * @ORM\CustomIdGenerator(class="Ramsey\Uuid\Doctrine\UuidGenerator")
     */
    private $id;

    /**
     * @ORM\Column(type="boolean")
     */
    private $hasNoMoreFighter = false;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $isVictorious;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Pokemon", cascade={"persist"})
     */
    private $currentFighter;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\User", cascade={"persist"})
     */
    private $trainer;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Pokemon", mappedBy="battleTeam")
     */
    private $pokemons;

    /**
     * @ORM\Column(type="integer")
     */
    private $healCount = 0;

    public function __construct()
    {
        $this->pokemons = new ArrayCollection();
    }

    public function getId(): ?Uuid
    {
        return $this->id;
    }

    public function getHasNoMoreFighter(): ?bool
    {
        return $this->hasNoMoreFighter;
    }

    public function setHasNoMoreFighter(bool $hasNoMoreFighter): self
    {
        $this->hasNoMoreFighter = $hasNoMoreFighter;

        return $this;
    }

    public function getIsVictorious(): ?bool
    {
        return $this->isVictorious;
    }

    public function setIsVictorious(?bool $isVictorious): self
    {
        $this->isVictorious = $isVictorious;

        return $this;
    }

    public function getCurrentFighter(): ?Pokemon
    {
        return $this->currentFighter;
    }

    public function setCurrentFighter(?Pokemon $currentFighter): self
    {
        if ($this->pokemons->contains($currentFighter)) {
            $this->currentFighter = $currentFighter;
        }
        return $this;
    }

    public function getTrainer(): ?User
    {
        return $this->trainer;
    }

    public function setTrainer(?User $trainer): self
    {
        $this->trainer = $trainer;

        return $this;
    }

    /**
     * @return Collection|Pokemon[]
     */
    public function getPokemons(): Collection
    {
        return $this->pokemons;
    }

    public function addPokemon(Pokemon $pokemon): self
    {
        if (!$this->pokemons->contains($pokemon)) {
            $this->pokemons[] = $pokemon;
            $pokemon->setBattleTeam($this);
        }

        return $this;
    }

    public function removePokemon(Pokemon $pokemon): self
    {
        if ($this->pokemons->contains($pokemon)) {
            $this->pokemons->removeElement($pokemon);
            // set the owning side to null (unless already changed)
            if ($pokemon->getBattleTeam() === $this) {
                $pokemon->setBattleTeam(null);
            }
        }

        return $this;
    }

    public function removePokemons(): self
    {
        foreach($this->pokemons as $pokemon) {
            if ($pokemon->getBattleTeam() === $this) {
                $pokemon->setBattleTeam(null);
            }
        }
        $this->pokemons->clear();

        return $this;
    }

    public function getHealCount(): ?int
    {
        return $this->healCount;
    }

    public function increaseHealCount(): self
    {
        $this->healCount += 1;

        return $this;
    }
}
