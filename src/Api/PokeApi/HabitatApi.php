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
    public function getHabitatFromPokemonId($pokemonId)
    {
        $data = $this->fetch('pokemon-species/' . $pokemonId);
        $habitatId = $this->getIdFromUrl($data['habitat']['url']);

        return $this->getHabitat($habitatId);
    }

    public function getHabitat($id)
    {
        if ($habitat = $this->habitatRepository->findOneBy(['apiId' => $id])) {
            return $habitat;
        }

        $habitat = new Habitat();

        return $habitat->setApiId($id)
            ->setName($this->getHabitatName($id))
            ->setPokemonsId($this->getPokemonsIdFromHabitat($id));
    }

    public function getHabitatName($id)
    {
        $data = $this->fetch('pokemon-habitat/' . $id);

        return $data['name'];
    }

    public function getRandomHabitat()
    {
        $id = rand(1, 99);
        // To make the area "rare"(id = 5) less frequent
        if ((50 <= $id) && ($id <= 59)) {
            $id = substr($id, 1);
            if ($id == 0) {
                $id = 5;
            }
        } elseif ($id < 10) {
            // nothing to do
        } else {
            $id = substr($id, 0, -1);
        }

        return $this->getHabitat($id);
    }
}
