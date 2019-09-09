<?php

namespace App\Form\Command;

use App\Repository\PokemonRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class AdventureCommandType extends AbstractType
{
    private $user;

    private $pokemonRepository;
    
    public function __construct(Security $security, PokemonRepository $pokemonRepository)
    {
        $this->user = $security->getUser();
        $this->pokemonRepository = $pokemonRepository;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('travel', SubmitType::class, [
                'label' => 'Travel around the world',
                'attr' => [
                    'class' => "btn btn-secondary"
                ]
            ])
            ->add('attack', SubmitType::class, [
                'label' => 'Attack',
                'attr' => [
                    'class' => "btn btn-success"
                ]
            ])
            ->add('throwPokeball', SubmitType::class, [
                'label' => 'Throw pokeball',
                'attr' => [
                    'class' => "btn btn-info"
                ]
            ])
            ->add('selectPokemon', ChoiceType::class, [
                'label' => 'Select your pokemon',
                'attr' => [
                    'class' => "btn btn-info"
                ],
                'choices'  => $this->getPokemonsChoice(),
            ])
            ->add('submitPokemon', SubmitType::class, [
                'label' => 'SELECT',
                'attr' => [
                    'class' => "btn btn-success"
                ]
            ]);
    }

    public function getPokemonsChoice() 
    {
        $pokemons = $this->pokemonRepository->findPokemonsByTrainer($this->user);
        $pokemonsList = [];
        foreach($pokemons as $pokemon)
        {
            $pokemonsList[$pokemon->getName().' (level '.$pokemon->getLevel().')'] = $pokemon->getId();
        }
        return $pokemonsList;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
}
