<?php

namespace App\Form\Command;

use App\Entity\Pokemon;
use App\Repository\PokemonRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class SelectPokemonType extends AbstractType
{
    private $user;

    public function __construct(Security $security)
    {
        $this->user = $security->getUser();
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('choicePokemon', EntityType::class, [
                'class' => Pokemon::class,
                'query_builder' => function(PokemonRepository $pokemonRepository) {
                    return $pokemonRepository->findReadyPokemonsByTrainerQueryBuilder($this->user);
                },
                'label' => false,
                'attr' => [
                    'class' => "btn btn-outline-info"
                ],
                'choice_label' => function($pokemon) {
                    return $pokemon->getName().' (level '.$pokemon->getLevel().')';
                }
            ])
            ->add('selectPokemon', SubmitType::class, [
                'label' => 'SELECT',
                'attr' => [
                    'class' => "btn btn-outline-success"
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
}
