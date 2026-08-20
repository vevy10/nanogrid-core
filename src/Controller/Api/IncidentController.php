<?php

namespace App\Controller\Api;

use App\Entity\Incident;
use App\Repository\IncidentRepository;
use App\Service\Api\IncidentService;
use App\Service\Api\JsonRequestDecoder;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/incidents', name: 'api_incidents_')]
final class IncidentController extends AbstractController
{
    #[Route('', name: 'index', methods: ['GET'])]
    public function index(IncidentRepository $incidentRepository, IncidentService $incidentService): JsonResponse
    {
        return $this->json($incidentService->normalizeCollection($incidentRepository->findAll()));
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(Incident $incident, IncidentService $incidentService): JsonResponse
    {
        return $this->json($incidentService->normalize($incident));
    }

    #[Route('', name: 'create', methods: ['POST'])]
    public function create(
        Request $request,
        JsonRequestDecoder $jsonRequestDecoder,
        IncidentService $incidentService
    ): JsonResponse {
        $incident = $incidentService->createFromPayload($jsonRequestDecoder->decode($request));

        return $this->json($incidentService->normalize($incident), 201);
    }
}
