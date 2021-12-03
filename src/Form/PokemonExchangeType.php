<?php

namespace App\Form;

use App\Entity\User;
use App\Entity\Pokemon;
use Doctrine\ORM\QueryBuilder;
use App\Entity\PokemonExchange;
use App\Repository\PokemonRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use App\Form\DataTransformer\UserToIdTransformer;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class PokemonExchangeType extends AbstractType
{
    private PokemonRepository $pokemonRepository;
    private UserToIdTransformer $userToIdTransformer;

    public function __construct(PokemonRepository $pokemonRepository, UserToIdTransformer $userToIdTransformer)
    {
        $this->pokemonRepository = $pokemonRepository;
        $this->userToIdTransformer = $userToIdTransformer;
    }
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('trainer1Show', EntityType::class, [
                'class' => User::class,
                'label' => 'Offerer',
                'mapped' => false,
                'choices' => [$options['user']],
                'choice_label' => 'username',
                'attr' => [
                    'disabled' => true
                ]
            ])
            ->add('trainer1', HiddenType::class)
            ->add('pokemon1', EntityType::class, [
                'class' => Pokemon::class,
                'label' => 'Proposed pokemon',
                'query_builder' => $this->getQueryBuilderForPokemonField($options['user']),
                'choice_label' => function ($pokemon) {
                    return $pokemon->getName() . ' (level ' . $pokemon->getLevel() . ')';
                },
            ])
            ->add('trainer2Show', EntityType::class, [
                'class' => User::class,
                'label' => 'Recipient',
                'mapped' => false,
                'choices' => [$options['trader']],
                'choice_label' => 'username',
                'attr' => [
                    'disabled' => true
                ]
            ])
            //->add('trainer2', HiddenType::class)
            ->add('pokemon2', EntityType::class, [
                'class' => Pokemon::class,
                'label' => 'Requested pokemon',
                'query_builder' => $this->getQueryBuilderForPokemonField($options['trader']),
                'choice_label' => function ($pokemon) {
                    return $pokemon->getName() . ' (level ' . $pokemon->getLevel() . ')';
                },
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Submit',
                'attr' => [
                    'class' => 'btn btn-primary'
                ]
            ])
            ->get('trainer1')->addModelTransformer($this->userToIdTransformer);
            //->get('trainer2')->addModelTransformer($this->userToIdTransformer);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => PokemonExchange::class,
            'trader' => null,
            'user' => null
        ]);
    }

    public function getQueryBuilderForPokemonField(UserInterface $user): QueryBuilder
    {
        return $this->pokemonRepository->findPokemonsByTrainerQueryBuilder($user);
    }
}
