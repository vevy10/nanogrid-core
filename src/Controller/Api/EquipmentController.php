<?php

namespace App\Controller\Api;

use App\Entity\Equipment;
use App\Repository\EquipmentRepository;
use App\Repository\SiteRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Validator\ValidatorInterface;

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

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(Equipment $equipment): JsonResponse
    {
        return $this->json($this->normalizeEquipment($equipment));
    }

    #[Route('', name: 'create', methods: ['POST'])]
    public function create(
        Request $request,
        EntityManagerInterface $entityManager,
        ValidatorInterface $validator,
        SiteRepository $siteRepository
    ): JsonResponse {
        $payload = json_decode($request->getContent(), true);

        if (!is_array($payload)) {
            return $this->json(['error' => 'Invalid JSON payload.'], 400);
        }

        $siteId = $payload['siteId'] ?? null;
        if (!is_int($siteId) && !ctype_digit((string) $siteId)) {
            return $this->json([
                'errors' => [[
                    'field' => 'siteId',
                    'message' => 'A valid siteId is required.',
                ]],
            ], 422);
        }

        $site = $siteRepository->find((int) $siteId);
        if ($site === null) {
            return $this->json([
                'errors' => [[
                    'field' => 'siteId',
                    'message' => 'Site not found.',
                ]],
            ], 422);
        }

        $equipment = new Equipment();
        $equipment->setName($payload['name'] ?? '');
        $equipment->setSerialNumber($payload['serialNumber'] ?? '');
        $equipment->setType($payload['type'] ?? '');
        $equipment->setStatus($payload['status'] ?? '');
        $equipment->setSite($site);

        if (!empty($payload['installedAt'])) {
            try {
                $equipment->setInstalledAt(new \DateTimeImmutable($payload['installedAt']));
            } catch (\Exception) {
                return $this->json(['error' => 'Invalid installedAt datetime format.'], 400);
            }
        }

        if (!empty($payload['lastSeenAt'])) {
            try {
                $equipment->setLastSeenAt(new \DateTimeImmutable($payload['lastSeenAt']));
            } catch (\Exception) {
                return $this->json(['error' => 'Invalid lastSeenAt datetime format.'], 400);
            }
        }

        $errors = $validator->validate($equipment);
        if (count($errors) > 0) {
            $violations = [];
            foreach ($errors as $error) {
                $violations[] = [
                    'field' => $error->getPropertyPath(),
                    'message' => $error->getMessage(),
                ];
            }

            return $this->json(['errors' => $violations], 422);
        }

        $entityManager->persist($equipment);
        $entityManager->flush();

        return $this->json($this->normalizeEquipment($equipment), 201);
    }
}