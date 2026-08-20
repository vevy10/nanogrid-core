<?php

namespace App\Controller\Api;

use App\Entity\Site;
use App\Repository\SiteRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/sites', name: 'api_sites_')]
final class SiteController extends AbstractController
{
    #[Route('', name: 'index', methods: ['GET'])]
    public function index(SiteRepository $siteRepository): JsonResponse
    {
        $sites = $siteRepository->findAll();

        $data = array_map(fn (Site $site) => $this->normalizeSite($site), $sites);

        return $this->json($data);
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(Site $site): JsonResponse
    {
        return $this->json($this->normalizeSite($site));
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

        $site = new Site();
        $site->setName($payload['name'] ?? '');
        $site->setCode($payload['code'] ?? '');
        $site->setRegion($payload['region'] ?? '');
        $site->setStatus($payload['status'] ?? '');

        if (!empty($payload['commissionedAt'])) {
            try {
                $site->setCommissionedAt(new \DateTimeImmutable($payload['commissionedAt']));
            } catch (\Exception) {
                return $this->json(['error' => 'Invalid commissionedAt datetime format.'], 400);
            }
        }

        $errors = $validator->validate($site);
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

        if ($siteRepository->findOneBy(['code' => $site->getCode()])) {
            return $this->json([
                'errors' => [[
                    'field' => 'code',
                    'message' => 'This code is already used.',
                ]],
            ], 422);
        }

        $entityManager->persist($site);
        $entityManager->flush();

        return $this->json($this->normalizeSite($site), 201);
    }

    private function normalizeSite(Site $site): array
    {
        return [
            'id' => $site->getId(),
            'name' => $site->getName(),
            'code' => $site->getCode(),
            'region' => $site->getRegion(),
            'status' => $site->getStatus(),
            'commissionedAt' => $site->getCommissionedAt()?->format(DATE_ATOM),
            'createdAt' => $site->getCreatedAt()->format(DATE_ATOM),
            'updatedAt' => $site->getUpdatedAt()->format(DATE_ATOM),
        ];
    }
}