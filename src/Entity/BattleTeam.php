<?php

namespace App\Entity;

use Symfony\Component\Uid\Uuid;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\Traits\EntityIdTrait;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity(repositoryClass="App\Repository\BattleTeamRepository")
 */
class BattleTeam
{
    use EntityIdTrait;

    /**
     * @ORM\Column(type="boolean")
     */
    private bool $hasNoMoreFighter = false;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private bool $isVictorious = false;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Pokemon", cascade={"persist"})
     */
    private ?Pokemon $currentFighter;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\User", cascade={"persist"})
     */
    private ?User $trainer;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Pokemon", mappedBy="battleTeam")
     * A list of pokemons that can be an ArrayCollection or PersistentCollection.
     */
    private $pokemons; /** @phpstan-ignore-line */

    /**
     * @ORM\Column(type="integer")
     */
    private int $healCount = 0;

    public function __construct()
    {
        $this->pokemons = new ArrayCollection();
        $this->uuid = Uuid::v4();
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

    public function getIsVictorious(): bool
    {
        return $this->isVictorious;
    }

    public function setIsVictorious(bool $isVictorious): self
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
        $this->pokemons->first();
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
        foreach ($this->pokemons as $pokemon) {
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
