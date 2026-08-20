<?php

namespace App\Controller\Api;

use App\Entity\Incident;
use App\Repository\EquipmentRepository;
use App\Repository\IncidentRepository;
use App\Repository\SiteRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/incidents', name: 'api_incidents_')]
final class IncidentController extends AbstractController
{
    #[Route('', name: 'index', methods: ['GET'])]
    public function index(IncidentRepository $incidentRepository): JsonResponse
    {
        $incidents = $incidentRepository->findAll();

        $data = array_map(fn (Incident $incident) => $this->normalizeIncident($incident), $incidents);

        return $this->json($data);
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(Incident $incident): JsonResponse
    {
        return $this->json($this->normalizeIncident($incident));
    }

    #[Route('', name: 'create', methods: ['POST'])]
    public function create(
        Request $request,
        EntityManagerInterface $entityManager,
        ValidatorInterface $validator,
        SiteRepository $siteRepository,
        EquipmentRepository $equipmentRepository
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

        $incident = new Incident();
        $incident->setTitle($payload['title'] ?? '');
        $incident->setDescription($payload['description'] ?? '');
        $incident->setSeverity($payload['severity'] ?? '');
        $incident->setStatus($payload['status'] ?? '');
        $incident->setSite($site);

        if (!empty($payload['reportedAt'])) {
            try {
                $incident->setReportedAt(new \DateTimeImmutable($payload['reportedAt']));
            } catch (\Exception) {
                return $this->json(['error' => 'Invalid reportedAt datetime format.'], 400);
            }
        }

        if (array_key_exists('resolvedAt', $payload)) {
            if ($payload['resolvedAt'] === null || $payload['resolvedAt'] === '') {
                $incident->setResolvedAt(null);
            } else {
                try {
                    $incident->setResolvedAt(new \DateTimeImmutable($payload['resolvedAt']));
                } catch (\Exception) {
                    return $this->json(['error' => 'Invalid resolvedAt datetime format.'], 400);
                }
            }
        }

        if (array_key_exists('equipmentId', $payload) && $payload['equipmentId'] !== null && $payload['equipmentId'] !== '') {
            $equipmentId = $payload['equipmentId'];

            if (!is_int($equipmentId) && !ctype_digit((string) $equipmentId)) {
                return $this->json([
                    'errors' => [[
                        'field' => 'equipmentId',
                        'message' => 'A valid equipmentId is required.',
                    ]],
                ], 422);
            }

            $equipment = $equipmentRepository->find((int) $equipmentId);
            if ($equipment === null) {
                return $this->json([
                    'errors' => [[
                        'field' => 'equipmentId',
                        'message' => 'Equipment not found.',
                    ]],
                ], 422);
            }

            $incident->setEquipment($equipment);
        }

        $errors = $validator->validate($incident);
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

        $entityManager->persist($incident);
        $entityManager->flush();

        return $this->json($this->normalizeIncident($incident), 201);
    }

    private function normalizeIncident(Incident $incident): array
    {
        return [
            'id' => $incident->getId(),
            'title' => $incident->getTitle(),
            'description' => $incident->getDescription(),
            'severity' => $incident->getSeverity(),
            'status' => $incident->getStatus(),
            'reportedAt' => $incident->getReportedAt()->format(DATE_ATOM),
            'resolvedAt' => $incident->getResolvedAt()?->format(DATE_ATOM),
            'createdAt' => $incident->getCreatedAt()->format(DATE_ATOM),
            'updatedAt' => $incident->getUpdatedAt()->format(DATE_ATOM),
            'site' => [
                'id' => $incident->getSite()->getId(),
                'code' => $incident->getSite()->getCode(),
                'name' => $incident->getSite()->getName(),
            ],
            'equipment' => $incident->getEquipment() ? [
                'id' => $incident->getEquipment()->getId(),
                'serialNumber' => $incident->getEquipment()->getSerialNumber(),
                'name' => $incident->getEquipment()->getName(),
            ] : null,
        ];
    }
}