<?php

namespace App\Form\Command;

use App\Repository\PokemonRepository;
use App\Repository\BattleTeamRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Security\Core\Security;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class SelectPokemonForTournamentType extends AbstractType
{
    private $user;
    private $pokemonRepository;
    private $battleTeamRepository;
    
    public function __construct(Security $security, PokemonRepository $pokemonRepository, BattleTeamRepository $battleTeamRepository)
    {
        $this->user = $security->getUser();
        $this->pokemonRepository = $pokemonRepository;
        $this->battleTeamRepository = $battleTeamRepository;
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
            ->add('number', HiddenType::class, [
                'data' => $this->getPokemonsCount()
            ])
        ;
    }

    public function getPokemonsChoice() 
    {
        $pokemons = $this->pokemonRepository->findAllFullHPByTrainer($this->user);
        $selectedPokemons = $this->getAlreadySelectedPokemons();
        $pokemonsList = [];
        foreach($pokemons as $pokemon)
        {
            if(!$selectedPokemons->contains($pokemon)) {
                $pokemonsList[$pokemon->getName().' (level '.$pokemon->getLevel().')'] = $pokemon->getId();
            }
        }
        return $pokemonsList;
    }

    public function getAlreadySelectedPokemons()
    {
        $battleTeam = $this->battleTeamRepository->findOneBy(['trainer' => $this->user]);
        
        if(!$battleTeam) { return new ArrayCollection() ; }

        return $battleTeam->getPokemons();
    }

    public function getPokemonsCount() {
        $battleTeam = $this->battleTeamRepository->findOneBy(['trainer' => $this->user]);
        
        if(!$battleTeam) { return 0; }

        return $battleTeam->getPokemons()->count();
    }

    public function getParent()
    {
        return SelectPokemonType::class;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
}
