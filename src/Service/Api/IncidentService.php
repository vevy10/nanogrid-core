<?php

namespace App\Service\Api;

use App\Entity\Incident;
use App\Exception\ApiValidationException;
use App\Repository\EquipmentRepository;
use App\Repository\SiteRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

final class IncidentService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly SiteRepository $siteRepository,
        private readonly EquipmentRepository $equipmentRepository,
        private readonly ValidationService $validationService
    ) {
    }

    /**
     * @param iterable<Incident> $incidents
     * @return array<int, array<string, mixed>>
     */
    public function normalizeCollection(iterable $incidents): array
    {
        $data = [];
        foreach ($incidents as $incident) {
            $data[] = $this->normalize($incident);
        }

        return $data;
    }

    /**
     * @return array<string, mixed>
     */
    public function normalize(Incident $incident): array
    {
        return [
            'id' => $incident->getId(),
            'title' => $incident->getTitle(),
            'description' => $incident->getDescription(),
            'severity' => $incident->getSeverity(),
            'status' => $incident->getStatus(),
            'reportedAt' => $incident->getReportedAt()->format(\DATE_ATOM),
            'resolvedAt' => $incident->getResolvedAt()?->format(\DATE_ATOM),
            'createdAt' => $incident->getCreatedAt()->format(\DATE_ATOM),
            'updatedAt' => $incident->getUpdatedAt()->format(\DATE_ATOM),
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

    /**
     * @param array<string, mixed> $payload
     */
    public function createFromPayload(array $payload): Incident
    {
        $incident = new Incident();
        $incident->setTitle((string) ($payload['title'] ?? ''));
        $incident->setDescription((string) ($payload['description'] ?? ''));
        $incident->setSeverity((string) ($payload['severity'] ?? ''));
        $incident->setStatus((string) ($payload['status'] ?? ''));
        $incident->setSite($this->resolveSite($payload['siteId'] ?? null));

        if (!empty($payload['reportedAt'])) {
            $incident->setReportedAt($this->createDate((string) $payload['reportedAt'], 'reportedAt'));
        }

        if (array_key_exists('resolvedAt', $payload)) {
            $incident->setResolvedAt($this->parseNullableDate($payload['resolvedAt'], 'resolvedAt'));
        }

        if (array_key_exists('equipmentId', $payload) && $payload['equipmentId'] !== null && $payload['equipmentId'] !== '') {
            $incident->setEquipment($this->resolveEquipment($payload['equipmentId']));
        }

        $this->validationService->validate($incident);

        $this->entityManager->persist($incident);
        $this->entityManager->flush();

        return $incident;
    }

    private function resolveSite(mixed $siteId)
    {
        if (!is_int($siteId) && !ctype_digit((string) $siteId)) {
            throw new ApiValidationException([[
                'field' => 'siteId',
                'message' => 'A valid siteId is required.',
            ]]);
        }

        $site = $this->siteRepository->find((int) $siteId);
        if ($site === null) {
            throw new ApiValidationException([[
                'field' => 'siteId',
                'message' => 'Site not found.',
            ]]);
        }

        return $site;
    }

    private function resolveEquipment(mixed $equipmentId)
    {
        if (!is_int($equipmentId) && !ctype_digit((string) $equipmentId)) {
            throw new ApiValidationException([[
                'field' => 'equipmentId',
                'message' => 'A valid equipmentId is required.',
            ]]);
        }

        $equipment = $this->equipmentRepository->find((int) $equipmentId);
        if ($equipment === null) {
            throw new ApiValidationException([[
                'field' => 'equipmentId',
                'message' => 'Equipment not found.',
            ]]);
        }

        return $equipment;
    }

    private function parseNullableDate(mixed $value, string $field): ?\DateTimeImmutable
    {
        if ($value === null || $value === '') {
            return null;
        }

        return $this->createDate((string) $value, $field);
    }

    private function createDate(string $value, string $field): \DateTimeImmutable
    {
        try {
            return new \DateTimeImmutable($value);
        } catch (\Exception) {
            throw new BadRequestHttpException(sprintf('Invalid %s datetime format.', $field));
        }
    }
}
