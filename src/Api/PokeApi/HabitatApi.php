<?php

namespace App\Api\PokeApi;

use App\Entity\Habitat;
use App\Api\PokeApi\PokeApi;
use App\Repository\HabitatRepository;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class HabitatApi extends PokeApi
{
    private HabitatRepository $habitatRepository;

    public function __construct(HttpClientInterface $client, HabitatRepository $habitatRepository)
    {
        parent::__construct($client);
        $this->habitatRepository = $habitatRepository;
    }

    // Useful only at the creation of user, otherwise we fetch habitat before pokemon
    public function getHabitatFromPokemonId(int $pokemonId): Habitat
    {
        $data = $this->fetch('pokemon-species/' . $pokemonId);
        $habitatId = $this->getIdFromUrl($data['habitat']['url']);

        return $this->getHabitat($habitatId);
    }

    public function getHabitat(int $id): Habitat
    {
        $habitat = $this->habitatRepository->findOneBy(['apiId' => $id]);

        if (null !== $habitat) {
            return $habitat;
        }

        $habitat = new Habitat();

        return $habitat->setApiId($id)
            ->setName($this->getHabitatName($id))
            ->setPokemonsId($this->getPokemonIdsFromHabitat($id));
    }

    public function getHabitatName(int $id): string
    {
        $data = $this->fetch('pokemon-habitat/' . $id);

        return $data['name'];
    }

    public function getRandomHabitat(): Habitat
    {
        $id = rand(1, 99);
        // To make the area "rare"(id = 5) less frequent
        if ((50 <= $id) && ($id <= 59)) {
            $id = substr((string) $id, 1);
            if ($id == 0) {
                $id = 5;
            }
        } elseif ($id < 10) {
            // nothing to do
        } else {
            $id = substr((string) $id, 0, -1);
        }

        return $this->getHabitat((int) $id);
    }
}
