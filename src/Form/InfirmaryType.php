<?php

namespace App\Form;

use App\Entity\User;
use App\Repository\PokemonRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class InfirmaryType extends AbstractType
{
    private array $userPokemons;

    public function __construct(Security $security, PokemonRepository $pokemonRepository)
    {
        /** @var User */
        $user = $security->getUser();
        $this->userPokemons = $pokemonRepository->findPokemonsByTrainer($user);
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('restorePokemon', SubmitType::class, [
                'label' => count($this->userPokemons) > 3 ? 'Pay 30 $' : 'Restore for free',
                'attr' => [
                    'class' => "btn btn-success"
                ]
            ])
            ->add('selectPokemon', ChoiceType::class, [
                'label' => false,
                'placeholder' => 'Choose a pokemon',
                'required' => false,
                'attr' => [
                    'class' => "select-custom form-control"
                ],
                'choices'  => $this->getPokemonsChoice(),
            ])
            ->add('donatePokemon', SubmitType::class, [
                'label' => 'Donate',
                'attr' => [
                    'class' => "btn btn-warning"
                ]
            ])
        ;
    }

    public function getPokemonsChoice(): array
    {
        $pokemonsList = [];

        foreach ($this->userPokemons as $pokemon) {
            $pokemonsList[$pokemon->getName() . ' (level ' . $pokemon->getLevel() . ')'] = $pokemon->getId();
        }

        return $pokemonsList;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
}
