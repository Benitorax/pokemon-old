<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class RegisterUserDTO implements UserInterface
{
    /**
     * @Assert\NotBlank
     * @Assert\Length(
     *      min = 3,
     *      max = 40,
     *      minMessage = "Your username must be at least {{ limit }} characters long",
     *      maxMessage = "Your username cannot be longer than {{ limit }} characters"
     * )
     */
    private string $username;

    /**
     * @Assert\NotBlank
     * @Assert\Email(
     *     message = "The email '{{ value }}' is not a valid email.",
     * )
     */
    private string $email;

    /**
     * @var string The hashed password
     * @Assert\NotBlank
     * @Assert\Length(
     *      min = 6,
     *      max = 40,
     *      minMessage = "Your password must be at least {{ limit }} characters long",
     *      maxMessage = "Your password cannot be longer than {{ limit }} characters"
     * )

     */
    private string $password;

    /**
     * @Assert\NotBlank(
     *      message = "You must choose a pokemon"
     * )
     */
    private int $pokemonApiId;

    private UserRepository $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUsername(): string
    {
        return (string) $this->username;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getPassword(): string
    {
        return (string) $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getSalt()
    {
        // not needed when using the "bcrypt" algorithm in security.yaml
        return null;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getPokemonApiId(): int
    {
        return $this->pokemonApiId;
    }

    public function setPokemonApiId(int $pokemonApiId): self
    {
        $this->pokemonApiId = $pokemonApiId;

        return $this;
    }

    /**
     * @return string[]
     */
    public function getRoles()
    {
        return [];
    }

    public function getUserIdentifier(): string
    {
        return $this->email;
    }

    /**
     * @Assert\Callback
     * @param mixed $payload
     */
    public function validate(ExecutionContextInterface $context, $payload): void
    {
        $data = $this->userRepository->findAllEmailAndUsername();

        foreach ($data['email'] as $email) {
            if (strtolower((string) $email) === strtolower($this->getEmail())) {
                $context->buildViolation('This email is already used.')
                ->atPath('email')
                ->addViolation();
            }
        }

        foreach ($data['username'] as $username) {
            if (strtolower($username) === strtolower($this->getUsername())) {
                $context->buildViolation('This username is already used.')
                ->atPath('username')
                ->addViolation();
            }
        }
    }
}
