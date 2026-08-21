<?php

namespace App\Service\Api;

use App\Entity\User;
use App\Exception\ApiValidationException;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final class UserService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly UserRepository $userRepository,
        private readonly ValidationService $validationService,
        private readonly UserPasswordHasherInterface $passwordHasher
    ) {
    }

    /**
     * @param array<string, mixed> $payload
     */
    public function register(array $payload): User
    {
        $email = strtolower(trim((string) ($payload['email'] ?? '')));
        $password = (string) ($payload['password'] ?? '');
        $fullName = trim((string) ($payload['fullName'] ?? ''));
        $status = trim((string) ($payload['status'] ?? 'active'));

        if ($password === '') {
            throw new ApiValidationException([[
                'field' => 'password',
                'message' => 'This value should not be blank.',
            ]]);
        }

        if (mb_strlen($password) < 8) {
            throw new ApiValidationException([[
                'field' => 'password',
                'message' => 'Password must be at least 8 characters long.',
            ]]);
        }

        if ($this->userRepository->findOneBy(['email' => $email]) !== null) {
            throw new ApiValidationException([[
                'field' => 'email',
                'message' => 'This email is already used.',
            ]]);
        }

        $user = new User();
        $user->setEmail($email);
        $user->setFullName($fullName);
        $user->setStatus($status);
        $user->setRoles(['ROLE_USER']);
        $user->setPassword($this->passwordHasher->hashPassword($user, $password));

        $this->validationService->validate($user);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $user;
    }

    /**
     * @return array<string, mixed>
     */
    public function normalize(User $user): array
    {
        return [
            'id' => $user->getId(),
            'email' => $user->getEmail(),
            'roles' => $user->getRoles(),
            'fullName' => $user->getFullName(),
            'status' => $user->getStatus(),
            'createdAt' => $user->getCreatedAt()->format(\DATE_ATOM),
            'updatedAt' => $user->getUpdatedAt()->format(\DATE_ATOM),
        ];
    }
}