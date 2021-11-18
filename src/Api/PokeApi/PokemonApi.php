<?php

namespace App\Api\PokeApi;

use App\Entity\Habitat;
use App\Entity\Pokemon;
use App\Api\PokeApi\PokeApi;
use App\Api\PokeApi\HabitatApi;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class PokemonApi extends PokeApi
{
    private HabitatApi $habitatApi;

    public function __construct(HttpClientInterface $client, HabitatApi $habitatApi)
    {
        parent::__construct($client);
        $this->habitatApi = $habitatApi;
    }

    public function getNewPokemon(int $id): Pokemon
    {
        $pokemon = new Pokemon();

        return $pokemon->setApiId($id)
            ->setName($this->getName($id))
            ->setLevel(1)
            ->setHabitat($this->habitatApi->getHabitatFromPokemonId($id))
            ->setSpriteFrontUrl($this->getSpriteFrontUrl($id))
            ->setSpriteBackUrl($this->getSpriteBackUrl($id))
            ->setCaptureRate($this->getCaptureRate($id))
            ->setEvolutionChainId($this->getEvolutionChainId($id));
    }

    public function hydrateEvolvedPokemon(Pokemon $pokemon, int $id, int $level): Pokemon
    {
        $pokemon->setApiId($id)
            ->setName($this->getName($id))
            ->setLevel($level)
            ->setHabitat($this->habitatApi->getHabitatFromPokemonId($id))
            ->setSpriteFrontUrl($this->getSpriteFrontUrl($id))
            ->setSpriteBackUrl($this->getSpriteBackUrl($id));

        return $pokemon;
    }

    public function checkNextEvolution(Pokemon $pokemon): ?Pokemon
    {
        $data = $this->fetch('evolution-chain/' . $pokemon->getEvolutionChainId());
        $data = $data['chain'];
        $data = $this->lookForEvolution($pokemon, $data);

        if ($data && $data['level'] <= $pokemon->getLevel()) {
            if (!$data['level']) {
                $data['level'] = $pokemon->getLevel();
            }
            return $pokemon = $this->hydrateEvolvedPokemon($pokemon, $data['idNext'], $data['level']);
        }

        return null;
    }

    /**
     * @return false|array
     *
     * Array format: [
     *      'level' => 22
     *      'idNext' => 54
     * ]
     */
    public function lookForEvolution(Pokemon $pokemon, array $data)
    {
        if ($data['evolves_to']) {
            $data = $data['evolves_to'][rand(
                0,
                count($data['evolves_to']) - 1
            )];
            $level = $data['evolution_details'][0]['min_level'];
            $idNext = $this->getIdFromUrl($data['species']['url']);

            if ($idNext > 151) {
                return false;
            }

            if (
                $idNext < $pokemon->getApiId()
                || $idNext == $pokemon->getApiId()
            ) {
                return $this->lookForEvolution($pokemon, $data);
            }

            return [
                'level' => $level,
                'idNext' => $idNext,
            ];
        }

        return false;
    }

    public function getName(int $id): string
    {
        $data = $this->fetch('pokemon/' . $id);

        return $data['name'];
    }

    public function getCaptureRate(int $id): int
    {
        $data = $this->fetch('pokemon-species/' . $id);

        return $data['capture_rate'];
    }

    public function getSpriteFrontUrl(int $id): string
    {
        $data = $this->fetch('pokemon-form/' . $id);

        return $data['sprites']['front_default'];
    }

    public function getSpriteBackUrl(int $id): string
    {
        $data = $this->fetch('pokemon-form/' . $id);

        return $data['sprites']['back_default'];
    }

    public function getEvolutionChainId(int $id): int
    {
        $data = $this->fetch('pokemon-species/' . $id);
        $url = $data['evolution_chain']['url'];

        return $this->getIdFromUrl($url);
    }

    public function getRandomPokemonFromHabitat(Habitat $habitat): Pokemon
    {
        $listId = $this->getPokemonIdsFromHabitat($habitat->getApiId());
        $id = $listId[array_rand($listId)];

        // To make Mew and Mewtwo less common, we have to get it twice
        if (in_array($id, [150, 151])) {
            $id = $listId[array_rand($listId)];
        }

        return $this->getNewPokemon($id);
    }
}
