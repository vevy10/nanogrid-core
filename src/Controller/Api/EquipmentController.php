<?php

namespace App\Controller\Api;

use App\Entity\Equipment;
use App\Repository\EquipmentRepository;
use App\Service\Api\EquipmentService;
use App\Service\Api\JsonRequestDecoder;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/equipment', name: 'api_equipment_')]
final class EquipmentController extends AbstractController
{
    #[Route('', name: 'index', methods: ['GET'])]
    public function index(EquipmentRepository $equipmentRepository, EquipmentService $equipmentService): JsonResponse
    {
        return $this->json($equipmentService->normalizeCollection($equipmentRepository->findAll()));
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(Equipment $equipment, EquipmentService $equipmentService): JsonResponse
    {
        return $this->json($equipmentService->normalize($equipment));
    }

    #[Route('', name: 'create', methods: ['POST'])]
    public function create(
        Request $request,
        JsonRequestDecoder $jsonRequestDecoder,
        EquipmentService $equipmentService
    ): JsonResponse {
        $equipment = $equipmentService->createFromPayload($jsonRequestDecoder->decode($request));

        return $this->json($equipmentService->normalize($equipment), 201);
    }

    #[Route('/{id}', name: 'update', methods: ['PUT'])]
    public function update(
        Request $request,
        Equipment $equipment,
        JsonRequestDecoder $jsonRequestDecoder,
        EquipmentService $equipmentService
    ): JsonResponse {
        $equipmentService->updateFromPayload($equipment, $jsonRequestDecoder->decode($request));

        return $this->json($equipmentService->normalize($equipment));
    }
}
