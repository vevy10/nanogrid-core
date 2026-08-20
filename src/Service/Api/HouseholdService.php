<?php

namespace App\Service\Api;

use App\Entity\Household;
use App\Exception\ApiValidationException;
use App\Repository\SiteRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

final class HouseholdService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly SiteRepository $siteRepository,
        private readonly ValidationService $validationService
    ) {
    }

    /**
     * @param iterable<Household> $households
     * @return array<int, array<string, mixed>>
     */
    public function normalizeCollection(iterable $households): array
    {
        $data = [];
        foreach ($households as $household) {
            $data[] = $this->normalize($household);
        }

        return $data;
    }

    /**
     * @return array<string, mixed>
     */
    public function normalize(Household $household): array
    {
        return [
            'id' => $household->getId(),
            'reference' => $household->getReference(),
            'ownerName' => $household->getOwnerName(),
            'phoneNumber' => $household->getPhoneNumber(),
            'connectionStatus' => $household->getConnectionStatus(),
            'connectedAt' => $household->getConnectedAt()?->format(\DATE_ATOM),
            'createdAt' => $household->getCreatedAt()->format(\DATE_ATOM),
            'updatedAt' => $household->getUpdatedAt()->format(\DATE_ATOM),
            'site' => [
                'id' => $household->getSite()->getId(),
                'code' => $household->getSite()->getCode(),
                'name' => $household->getSite()->getName(),
            ],
        ];
    }

    /**
     * @param array<string, mixed> $payload
     */
    public function createFromPayload(array $payload): Household
    {
        $household = new Household();
        $household->setReference((string) ($payload['reference'] ?? ''));
        $household->setOwnerName((string) ($payload['ownerName'] ?? ''));
        $household->setPhoneNumber(isset($payload['phoneNumber']) ? (string) $payload['phoneNumber'] : null);
        $household->setConnectionStatus((string) ($payload['connectionStatus'] ?? ''));
        $household->setSite($this->resolveSite($payload['siteId'] ?? null));
        $household->setConnectedAt($this->parseOptionalDate($payload, 'connectedAt'));

        $this->validationService->validate($household);

        $this->entityManager->persist($household);
        $this->entityManager->flush();

        return $household;
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

        try {
            return new \DateTimeImmutable((string) $payload[$field]);
        } catch (\Exception) {
            throw new BadRequestHttpException(sprintf('Invalid %s datetime format.', $field));
        }
    }
}
