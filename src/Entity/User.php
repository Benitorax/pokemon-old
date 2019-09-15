<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use Ramsey\Uuid\Uuid;

/**
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 */
class User implements UserInterface
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
    private $username;

    /**
     * @ORM\Column(type="string", length=180, unique=true)
     */
    private $email;

    /**
     * @ORM\Column(type="json")
     */
    private $roles = [];

    /**
     * @var string The hashed password
     * @ORM\Column(type="string")
     */
    private $password;

    /**
     * @ORM\Column(type="integer")
     */
    private $pokedollar = 500;

    /**
     * @ORM\Column(type="integer")
     */
    private $pokeball = 5;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Pokemon", mappedBy="trainer", cascade={"persist"}, orphanRemoval=true)
     */
    private $pokemons;

    /**
     * @ORM\Column(type="integer")
     */
    private $healthPotion = 2;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isActivated = false;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @ORM\Column(type="uuid", nullable=true)
     */
    private $token;

    public function __construct()
    {
        $this->pokemons = new ArrayCollection();
    }

    public function getId(): ?Uuid
    {
        return $this->id;
    }

    public function getEmail(): ?string
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
        return (string) $this->username;
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
        return (string) $this->password;
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
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
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

    public function getHealthPotion(): ?int
    {
        return $this->healthPotion;
    }

    public function setHealthPotion(int $healthPotion): self
    {
        $this->healthPotion = $healthPotion;

        return $this;
    }

    public function addHealthPotion(int $healthPotion): self
    {
        $this->healthPotion += $healthPotion;

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

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getToken()
    {
        return $this->token;
    }

    public function setToken($token): self
    {
        $this->token = $token;

        return $this;
    }
}
