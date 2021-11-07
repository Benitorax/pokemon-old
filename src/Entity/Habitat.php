<?php

namespace App\Entity;

use Symfony\Component\Uid\Uuid;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\Traits\EntityIdTrait;

/**
 * @ORM\Entity(repositoryClass="App\Repository\HabitatRepository")
 */
class Habitat
{
    use EntityIdTrait;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\Column(type="array")
     */
    private $pokemonsId = [];

    /**
     * @ORM\Column(type="integer")
     */
    private $apiId;

    public function __construct()
    {
        $this->uuid = Uuid::v4();
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getPokemonsId(): ?array
    {
        return $this->pokemonsId;
    }

    public function setPokemonsId(array $pokemonsId): self
    {
        $this->pokemonsId = $pokemonsId;

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
}
