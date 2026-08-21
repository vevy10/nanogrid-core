<?php

namespace App\Controller\Api;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

final class SecurityController extends AbstractController
{
    #[Route('/api/login', name: 'api_login_check', methods: ['POST'])]
    public function login(): JsonResponse
    {
        return $this->json([
            'message' => 'Login successful.',
        ]);
    }

    #[Route('/api/me', name: 'api_me', methods: ['GET'])]
    public function me(): JsonResponse
    {
        $user = $this->getUser();

        if (!$user instanceof User) {
            return $this->json([
                'error' => [
                    'status' => 401,
                    'message' => 'Unauthorized.',
                ],
            ], 401);
        }

        return $this->json([
            'id' => $user->getId(),
            'email' => $user->getEmail(),
            'roles' => $user->getRoles(),
            'fullName' => $user->getFullName(),
            'status' => $user->getStatus(),
        ]);
    }
}