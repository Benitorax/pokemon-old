<?php
namespace App\Serializer;

use Symfony\Component\Serializer\SerializerInterface;

class PokemonSerializer
{
    private $serializer;

    public function __construct(SerializerInterface $serializer) {
        $this->serializer = $serializer;
    }

    public function normalizeForBattle($object) {
        return $this->serializer->normalize($object, null, ['groups' => ['battle']]);
    }

    public function normalizeForSelection($object) {
        return $this->serializer->normalize($object, null, ['groups' => ['selection']]);
    }
}