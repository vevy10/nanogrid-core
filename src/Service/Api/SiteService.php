<?php

namespace App\Service\Api;

use App\Entity\Site;
use App\Exception\ApiValidationException;
use App\Repository\SiteRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

final class SiteService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly SiteRepository $siteRepository,
        private readonly ValidationService $validationService
    ) {
    }

    /**
     * @param iterable<Site> $sites
     * @return array<int, array<string, mixed>>
     */
    public function normalizeCollection(iterable $sites): array
    {
        $data = [];
        foreach ($sites as $site) {
            $data[] = $this->normalize($site);
        }

        return $data;
    }

    /**
     * @return array<string, mixed>
     */
    public function normalize(Site $site): array
    {
        return [
            'id' => $site->getId(),
            'name' => $site->getName(),
            'code' => $site->getCode(),
            'region' => $site->getRegion(),
            'status' => $site->getStatus(),
            'commissionedAt' => $site->getCommissionedAt()?->format(\DATE_ATOM),
            'createdAt' => $site->getCreatedAt()->format(\DATE_ATOM),
            'updatedAt' => $site->getUpdatedAt()->format(\DATE_ATOM),
        ];
    }

    /**
     * @param array<string, mixed> $payload
     */
    public function createFromPayload(array $payload): Site
    {
        $site = new Site();
        $site->setName((string) ($payload['name'] ?? ''));
        $site->setCode((string) ($payload['code'] ?? ''));
        $site->setRegion((string) ($payload['region'] ?? ''));
        $site->setStatus((string) ($payload['status'] ?? ''));
        $site->setCommissionedAt($this->parseOptionalDate($payload, 'commissionedAt'));

        $this->validationService->validate($site);
        $this->assertUniqueCode($site);

        $this->entityManager->persist($site);
        $this->entityManager->flush();

        return $site;
    }

    /**
     * @param array<string, mixed> $payload
     */
    public function updateFromPayload(Site $site, array $payload): Site
    {
        $site->setName((string) ($payload['name'] ?? $site->getName()));
        $site->setCode((string) ($payload['code'] ?? $site->getCode()));
        $site->setRegion((string) ($payload['region'] ?? $site->getRegion()));
        $site->setStatus((string) ($payload['status'] ?? $site->getStatus()));

        if (array_key_exists('commissionedAt', $payload)) {
            $site->setCommissionedAt($this->parseNullableDate($payload['commissionedAt'], 'commissionedAt'));
        }

        $site->setUpdatedAt(new \DateTimeImmutable());

        $this->validationService->validate($site);
        $this->assertUniqueCode($site);

        $this->entityManager->flush();

        return $site;
    }

    private function assertUniqueCode(Site $site): void
    {
        $existingSite = $this->siteRepository->findOneBy(['code' => $site->getCode()]);
        if ($existingSite !== null && $existingSite->getId() !== $site->getId()) {
            throw new ApiValidationException([[
                'field' => 'code',
                'message' => 'This code is already used.',
            ]]);
        }
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
