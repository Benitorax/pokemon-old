<?php

namespace App\Entity;

class Trainer
{
    private array $trainers = [
        [
            'username' => 'Red',
            'email' => '/images/trainers/Red.png',
        ],
        [
            'username' => 'Ethan',
            'email' => '/images/trainers/Ethan.png',
        ],
        [
            'username' => 'Brendan',
            'email' => '/images/trainers/Brendan.png',
        ],
        [
            'username' => 'Lucas',
            'email' => '/images/trainers/Lucas.png',
        ],
        [
            'username' => 'Hilbert',
            'email' => '/images/trainers/Hilbert.png',
        ],
        [
            'username' => 'Nate',
            'email' => '/images/trainers/Nate.png',
        ],
        [
            'username' => 'Calem',
            'email' => '/images/trainers/Calem.png',
        ],
        [
            'username' => 'Elio',
            'email' => '/images/trainers/Elio.png',
        ],
        [
            'username' => 'Chase',
            'email' => '/images/trainers/Chase.png',
        ],
        [
            'username' => 'Victor',
            'email' => '/images/trainers/Victor.png'
        ],
        [
            'username' => 'Kris',
            'email' => '/images/trainers/Kris.png',
        ],
        [
            'username' => 'May',
            'email' => '/images/trainers/May.png',
        ],
        [
            'username' => 'Leaf',
            'email' => '/images/trainers/Leaf.png',
        ],
        [
            'username' => 'Dawn',
            'email' => '/images/trainers/Dawn.png',
        ],
        [
            'username' => 'Lyra',
            'email' => '/images/trainers/Lyra.png',
        ],
        [
            'username' => 'Hilda',
            'email' => '/images/trainers/Hilda.png',
        ],
        [
            'username' => 'Rosa',
            'email' => '/images/trainers/Rosa.png',
        ],
        [
            'username' => 'Serena',
            'email' => '/images/trainers/Serena.png',
        ],
        [
            'username' => 'Selene',
            'email' => '/images/trainers/Selene.png',
        ],
        [
            'username' => 'Elaine',
            'email' => '/images/trainers/Elaine.png',
        ],
        [
            'username' => 'Gloria',
            'email' => '/images/trainers/Gloria.png',
        ],
    ];

    public function getRandomTrainer(): array
    {
        return $this->trainers[array_rand($this->trainers)];
    }
}
