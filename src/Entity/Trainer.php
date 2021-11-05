<?php
namespace App\Entity;

class Trainer
{
    private $trainers = [
        [
            'username' => 'Red',
            'email' => 'https://cdn2.bulbagarden.net/upload/thumb/8/83/FireRed_LeafGreen_Red.png/111px-FireRed_LeafGreen_Red.png',
        ],
        [
            'username' => 'Ethan',
            'email' => 'https://cdn2.bulbagarden.net/upload/thumb/c/c0/HeartGold_SoulSilver_Ethan.png/121px-HeartGold_SoulSilver_Ethan.png',
        ],
        [
            'username' => 'Brendan',
            'email' => 'https://cdn2.bulbagarden.net/upload/thumb/f/f1/Omega_Ruby_Alpha_Sapphire_Brendan.png/93px-Omega_Ruby_Alpha_Sapphire_Brendan.png',
        ],
        [
            'username' => 'Lucas',
            'email' => 'https://cdn2.bulbagarden.net/upload/thumb/2/2f/Platinum_Lucas.png/120px-Platinum_Lucas.png',
        ],
        [
            'username' => 'Hilbert',
            'email' => 'https://cdn2.bulbagarden.net/upload/thumb/a/a4/Black_White_Hilbert.png/100px-Black_White_Hilbert.png',
        ],
        [
            'username' => 'Nate',
            'email' => 'https://cdn2.bulbagarden.net/upload/thumb/6/61/Black_2_White_2_Nate.png/110px-Black_2_White_2_Nate.png',
        ],
        [
            'username' => 'Calem',
            'email' => 'https://cdn2.bulbagarden.net/upload/thumb/4/48/XY_Calem.png/74px-XY_Calem.png',
        ],
        [
            'username' => 'Elio',
            'email' => 'https://cdn2.bulbagarden.net/upload/thumb/0/05/Ultra_Sun_Ultra_Moon_Protagonist_male.png/179px-Ultra_Sun_Ultra_Moon_Protagonist_male.png',
        ],
        [
            'username' => 'Chase',
            'email' => 'https://cdn2.bulbagarden.net/upload/thumb/6/62/Lets_Go_Pikachu_Eevee_Male_Trainer.png/159px-Lets_Go_Pikachu_Eevee_Male_Trainer.png',
        ],
        [
            'username' => 'Victor',
            'email' => 'https://cdn2.bulbagarden.net/upload/thumb/e/e5/Sword_Shield_Male_Trainer.png/90px-Sword_Shield_Male_Trainer.png',
        ],
        [
            'username' => 'Kris',
            'email' => 'https://cdn2.bulbagarden.net/upload/thumb/e/e1/Crystal_Kris.png/135px-Crystal_Kris.png',
        ],
        [
            'username' => 'May',
            'email' => 'https://cdn2.bulbagarden.net/upload/thumb/a/a0/Omega_Ruby_Alpha_Sapphire_May.png/89px-Omega_Ruby_Alpha_Sapphire_May.png',
        ],
        [
            'username' => 'Leaf',
            'email' => 'https://cdn2.bulbagarden.net/upload/thumb/4/48/FireRed_LeafGreen_Leaf.png/100px-FireRed_LeafGreen_Leaf.png',
        ],
        [
            'username' => 'Dawn',
            'email' => 'https://cdn2.bulbagarden.net/upload/thumb/6/6f/Platinum_Dawn.png/152px-Platinum_Dawn.png',
        ],
        [
            'username' => 'Lyra',
            'email' => 'https://cdn2.bulbagarden.net/upload/thumb/2/25/HeartGold_SoulSilver_Lyra.png/114px-HeartGold_SoulSilver_Lyra.png',
        ],
        [
            'username' => 'Hilda',
            'email' => 'https://cdn2.bulbagarden.net/upload/thumb/6/6f/Black_White_Hilda.png/111px-Black_White_Hilda.png',
        ],
        [
            'username' => 'Rosa',
            'email' => 'https://cdn2.bulbagarden.net/upload/thumb/f/f2/Black_2_White_2_Rosa.png/95px-Black_2_White_2_Rosa.png',
        ],
        [
            'username' => 'Serena',
            'email' => 'https://cdn2.bulbagarden.net/upload/thumb/e/e1/XY_Serena.png/127px-XY_Serena.png',
        ],
        [
            'username' => 'Selene',
            'email' => 'https://cdn2.bulbagarden.net/upload/thumb/2/21/Ultra_Sun_Ultra_Moon_Protagonist_female.png/154px-Ultra_Sun_Ultra_Moon_Protagonist_female.png',
        ],
        [
            'username' => 'Elaine',
            'email' => 'https://cdn2.bulbagarden.net/upload/thumb/0/04/Lets_Go_Pikachu_Eevee_Female_Trainer.png/123px-Lets_Go_Pikachu_Eevee_Female_Trainer.png',
        ],
        [
            'username' => 'Gloria',
            'email' => 'https://cdn2.bulbagarden.net/upload/thumb/5/5e/Sword_Shield_Female_Trainer.png/117px-Sword_Shield_Female_Trainer.png',
        ],
    ];

    public function getRandomTrainer()
    {
        return $this->trainers[array_rand($this->trainers)];
    }
}