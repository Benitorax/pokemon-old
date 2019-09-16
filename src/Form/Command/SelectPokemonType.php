<?php

namespace App\Form\Command;

use App\Repository\PokemonRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class SelectPokemonType extends AbstractType
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
            ->add('choicePokemon', ChoiceType::class, [
                'label' => false,
                'attr' => [
                    'class' => "btn btn-outline-info"
                ],
                'choices'  => $this->getPokemonsChoice(),
            ])
            ->add('selectPokemon', SubmitType::class, [
                'label' => 'SELECT',
                'attr' => [
                    'class' => "btn btn-outline-success"
                ]
            ])
        ;
    }

    public function getPokemonsChoice() 
    {
        $pokemons = $this->pokemonRepository->findReadyPokemonsByTrainer($this->user);
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
