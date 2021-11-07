<?php

namespace App\Form\DataTransformer;

use App\Repository\UserRepository;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class UserToIdTransformer implements DataTransformerInterface
{
    private $userRepository;
    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function transform($user)
    {
        if ($user === null) {
            return '';
        }

        if (!$user instanceof UserInterface) {
            return '';
        }

        return $user->getId();
    }

    public function reverseTransform($id)
    {
        if (!$id) {
            return;
        }

        $user = $this->userRepository->find($id);
        if ($user === null) {
            throw new TransformationFailedException(sprintf('An User with id "%s" does not exist!', $id));
        }

        return $user;
    }
}
