<?php

namespace App\Entity;

use Symfony\Component\Uid\Uuid;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\Traits\EntityIdTrait;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

/**
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 */
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    use EntityIdTrait;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private string $username;

    /**
     * @ORM\Column(type="string", length=180, unique=true)
     */
    private string $email;

    /**
     * @ORM\Column(type="json")
     */
    private array $roles = [];

    /**
     * @ORM\Column(type="string")
     */
    private string $password;

    /**
     * @ORM\Column(type="integer")
     */
    private int $pokedollar = 500;

    /**
     * @ORM\Column(type="integer")
     */
    private int $pokeball = 5;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Pokemon", mappedBy="trainer", cascade={"persist"}, orphanRemoval=true)
     * @ORM\OrderBy({"name" = "ASC", "level" = "DESC"})
     * A list of pokemons that can be an ArrayCollection or PersistentCollection.
     */
    private $pokemons; /** @phpstan-ignore-line */

    /**
     * @ORM\Column(type="integer")
     */
    private int $healingPotion = 5;

    /**
     * @ORM\Column(type="boolean")
     */
    private bool $isActivated = false;

    /**
     * @ORM\Column(type="datetime")
     * @var \DateTime|\DateTimeImmutable
     */
    private \DateTimeInterface $createdAt;

    /**
     * @ORM\Column(type="uuid", nullable=true)
     */
    private ?Uuid $token;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @var \DateTime|\DateTimeImmutable|null
     */
    private ?\DateTimeInterface $tokenCreatedAt;

    /**
     * @ORM\Column(type="integer")
     */
    private int $consecutiveWin = 0;

    /**
     * @ORM\Column(type="integer")
     */
    private int $championCount = 0;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private ?string $currentGameId;

    public function __construct()
    {
        $this->pokemons = new ArrayCollection();
        $this->uuid = Uuid::v4();
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUsername(): string
    {
        return $this->username;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getSalt()
    {
        // not needed when using the "bcrypt" algorithm in security.yaml
        return null;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getPokedollar(): ?int
    {
        return $this->pokedollar;
    }

    public function setPokedollar(int $pokedollar): self
    {
        $this->pokedollar = $pokedollar;

        return $this;
    }

    public function increasePokedollar(int $pokedollar): self
    {
        $this->pokedollar += $pokedollar;

        return $this;
    }

    public function decreasePokedollar(int $pokedollar): self
    {
        $this->pokedollar -= $pokedollar;

        return $this;
    }

    public function getPokeball(): ?int
    {
        return $this->pokeball;
    }

    public function addPokeball(int $pokeball): self
    {
        $this->pokeball += $pokeball;

        return $this;
    }

    public function usePokeball(): self
    {
        $this->pokeball--;

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
            $pokemon->setTrainer($this);
        }

        return $this;
    }

    public function removePokemon(Pokemon $pokemon): self
    {
        if ($this->pokemons->contains($pokemon)) {
            $this->pokemons->removeElement($pokemon);
            // set the owning side to null (unless already changed)
            if ($pokemon->getTrainer() === $this) {
                $pokemon->setTrainer(null);
            }
        }

        return $this;
    }

    public function getHealingPotion(): ?int
    {
        return $this->healingPotion;
    }

    public function setHealingPotion(int $healingPotion): self
    {
        $this->healingPotion = $healingPotion;

        return $this;
    }

    public function useHealingPotion(): self
    {
        if ($this->healingPotion >= 1) {
            $this->healingPotion -= 1;
        }

        return $this;
    }

    public function addHealingPotion(int $healingPotion): self
    {
        $this->healingPotion += $healingPotion;

        return $this;
    }

    public function getIsActivated(): ?bool
    {
        return $this->isActivated;
    }

    public function setIsActivated(bool $isActivated): self
    {
        $this->isActivated = $isActivated;

        return $this;
    }

    /**
     * @return \DateTime|\DateTimeImmutable
     */
    public function getCreatedAt(): \DateTimeInterface
    {
        return $this->createdAt;
    }

    /**
     * @param \DateTime|\DateTimeImmutable $createdAt
     */
    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getToken(): ?Uuid
    {
        return $this->token;
    }

    public function setToken(?Uuid $token): self
    {
        $this->token = $token;

        return $this;
    }

    /**
     * @return \DateTime|\DateTimeImmutable|null
     */
    public function getTokenCreatedAt(): ?\DateTimeInterface
    {
        return $this->tokenCreatedAt;
    }

    /**
     * @param \DateTime|\DateTimeImmutable|null $tokenCreatedAt
     */
    public function setTokenCreatedAt(?\DateTimeInterface $tokenCreatedAt): self
    {
        $this->tokenCreatedAt = $tokenCreatedAt;

        return $this;
    }

    public function getConsecutiveWin(): ?int
    {
        return $this->consecutiveWin;
    }

    public function resetConsecutiveWin(): self
    {
        $this->consecutiveWin = 0;

        return $this;
    }

    public function increaseConsecutiveWin(): self
    {
        $this->consecutiveWin += 1;

        return $this;
    }

    public function getChampionCount(): int
    {
        return $this->championCount;
    }

    public function increaseChampionCount(): self
    {
        $this->championCount += 1;

        return $this;
    }

    public function getCurrentGameId(): string
    {
        return $this->currentGameId ?: '';
    }

    public function setCurrentGameId(?string $currentGameId): self
    {
        $this->currentGameId = $currentGameId;

        return $this;
    }

    public function getUserIdentifier(): string
    {
        return $this->email;
    }
}
