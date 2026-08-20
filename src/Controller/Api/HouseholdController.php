<?php

namespace App\Controller\Api;

use App\Entity\Household;
use App\Repository\HouseholdRepository;
use App\Service\Api\HouseholdService;
use App\Service\Api\JsonRequestDecoder;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/households', name: 'api_households_')]
final class HouseholdController extends AbstractController
{
    #[Route('', name: 'index', methods: ['GET'])]
    public function index(HouseholdRepository $householdRepository, HouseholdService $householdService): JsonResponse
    {
        return $this->json($householdService->normalizeCollection($householdRepository->findAll()));
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(Household $household, HouseholdService $householdService): JsonResponse
    {
        return $this->json($householdService->normalize($household));
    }

    #[Route('', name: 'create', methods: ['POST'])]
    public function create(
        Request $request,
        JsonRequestDecoder $jsonRequestDecoder,
        HouseholdService $householdService
    ): JsonResponse {
        $household = $householdService->createFromPayload($jsonRequestDecoder->decode($request));

        return $this->json($householdService->normalize($household), 201);
    }
}
