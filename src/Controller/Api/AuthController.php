<?php

namespace App\Controller\Api;

use App\Service\Api\JsonRequestDecoder;
use App\Service\Api\UserService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api', name: 'api_auth_')]
final class AuthController extends AbstractController
{
    #[Route('/register', name: 'register', methods: ['POST'])]
    public function register(
        Request $request,
        JsonRequestDecoder $jsonRequestDecoder,
        UserService $userService
    ): JsonResponse {
        $user = $userService->register($jsonRequestDecoder->decode($request));

        return $this->json($userService->normalize($user), 201);
    }
}