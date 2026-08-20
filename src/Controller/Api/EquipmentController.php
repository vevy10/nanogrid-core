<?php

namespace App\Controller\Api;

use App\Entity\Equipment;
use App\Repository\EquipmentRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/equipment', name: 'api_equipment_')]
final class EquipmentController extends AbstractController
{
    #[Route('', name: 'index', methods: ['GET'])]
    public function index(EquipmentRepository $equipmentRepository): JsonResponse
    {
        $equipment = $equipmentRepository->findAll();

        $data = array_map(fn (Equipment $item) => $this->normalizeEquipment($item), $equipment);

        return $this->json($data);
    }

    private function normalizeEquipment(Equipment $equipment): array
    {
        return [
            'id' => $equipment->getId(),
            'name' => $equipment->getName(),
            'serialNumber' => $equipment->getSerialNumber(),
            'type' => $equipment->getType(),
            'status' => $equipment->getStatus(),
            'installedAt' => $equipment->getInstalledAt()?->format(DATE_ATOM),
            'lastSeenAt' => $equipment->getLastSeenAt()?->format(DATE_ATOM),
            'site' => [
                'id' => $equipment->getSite()->getId(),
                'code' => $equipment->getSite()->getCode(),
                'name' => $equipment->getSite()->getName(),
                'region' => $equipment->getSite()->getRegion(),
                'status' => $equipment->getSite()->getStatus(),
            ],
        ];
    }
}