<?php

namespace App\Form\DataTransformer;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

class UserToIdTransformer implements DataTransformerInterface
{
    private UserRepository $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function transform($user)
    {
        if (!$user instanceof \App\Entity\User) {
            return '';
        }

        return $user->getId();
    }

    public function reverseTransform($id)
    {
        if (null === $id) {
            return;
        }

        $user = $this->userRepository->find($id);
        if (!$user instanceof \App\Entity\User) {
            throw new TransformationFailedException(sprintf('An User with id "%s" does not exist!', $id));
        }

        return $user;
    }
}
