<?php

namespace App\Service\Api;

use App\Entity\Equipment;
use App\Exception\ApiValidationException;
use App\Repository\SiteRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

final class EquipmentService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly SiteRepository $siteRepository,
        private readonly ValidationService $validationService
    ) {
    }

    /**
     * @param iterable<Equipment> $equipmentItems
     * @return array<int, array<string, mixed>>
     */
    public function normalizeCollection(iterable $equipmentItems): array
    {
        $data = [];
        foreach ($equipmentItems as $equipment) {
            $data[] = $this->normalize($equipment);
        }

        return $data;
    }

    /**
     * @return array<string, mixed>
     */
    public function normalize(Equipment $equipment): array
    {
        return [
            'id' => $equipment->getId(),
            'name' => $equipment->getName(),
            'serialNumber' => $equipment->getSerialNumber(),
            'type' => $equipment->getType(),
            'status' => $equipment->getStatus(),
            'installedAt' => $equipment->getInstalledAt()?->format(\DATE_ATOM),
            'lastSeenAt' => $equipment->getLastSeenAt()?->format(\DATE_ATOM),
            'site' => [
                'id' => $equipment->getSite()->getId(),
                'code' => $equipment->getSite()->getCode(),
                'name' => $equipment->getSite()->getName(),
                'region' => $equipment->getSite()->getRegion(),
                'status' => $equipment->getSite()->getStatus(),
            ],
        ];
    }

    /**
     * @param array<string, mixed> $payload
     */
    public function createFromPayload(array $payload): Equipment
    {
        $equipment = new Equipment();
        $equipment->setName((string) ($payload['name'] ?? ''));
        $equipment->setSerialNumber((string) ($payload['serialNumber'] ?? ''));
        $equipment->setType((string) ($payload['type'] ?? ''));
        $equipment->setStatus((string) ($payload['status'] ?? ''));
        $equipment->setSite($this->resolveSite($payload['siteId'] ?? null));
        $equipment->setInstalledAt($this->parseOptionalDate($payload, 'installedAt'));
        $equipment->setLastSeenAt($this->parseOptionalDate($payload, 'lastSeenAt'));

        $this->validationService->validate($equipment);

        $this->entityManager->persist($equipment);
        $this->entityManager->flush();

        return $equipment;
    }

    /**
     * @param array<string, mixed> $payload
     */
    public function updateFromPayload(Equipment $equipment, array $payload): Equipment
    {
        $equipment->setName((string) ($payload['name'] ?? $equipment->getName()));
        $equipment->setSerialNumber((string) ($payload['serialNumber'] ?? $equipment->getSerialNumber()));
        $equipment->setType((string) ($payload['type'] ?? $equipment->getType()));
        $equipment->setStatus((string) ($payload['status'] ?? $equipment->getStatus()));

        if (array_key_exists('installedAt', $payload)) {
            $equipment->setInstalledAt($this->parseNullableDate($payload['installedAt'], 'installedAt'));
        }

        if (array_key_exists('lastSeenAt', $payload)) {
            $equipment->setLastSeenAt($this->parseNullableDate($payload['lastSeenAt'], 'lastSeenAt'));
        }

        if (array_key_exists('siteId', $payload)) {
            $equipment->setSite($this->resolveSite($payload['siteId']));
        }

        $this->validationService->validate($equipment);

        $this->entityManager->flush();

        return $equipment;
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

    /**
     * @param array<string, mixed> $payload
     */
    private function parseOptionalDate(array $payload, string $field): ?\DateTimeImmutable
    {
        if (empty($payload[$field])) {
            return null;
        }

        return $this->createDate((string) $payload[$field], $field);
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
