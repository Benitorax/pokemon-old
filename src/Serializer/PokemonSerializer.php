<?php

namespace App\Serializer;

use App\Entity\Pokemon;
use Symfony\Component\Serializer\SerializerInterface;

class PokemonSerializer
{
    private SerializerInterface $serializer;

    public function __construct(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }

    public function normalizeForBattle(Pokemon $object): array
    {
        return $this->serializer->normalize($object, null, ['groups' => ['battle']]); /** @phpstan-ignore-line */
    }

    public function normalizeForSelection(Pokemon $object): array
    {
        return $this->serializer->normalize($object, null, ['groups' => ['selection']]); /** @phpstan-ignore-line */
    }
}
