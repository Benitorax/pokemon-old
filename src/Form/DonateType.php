<?php

namespace App\Form;

use App\Repository\PokemonRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class DonateType extends AbstractType
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
            ->add('selectPokemon', ChoiceType::class, [
                'label' => false,
                'placeholder' => 'Choose a pokemon',
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
