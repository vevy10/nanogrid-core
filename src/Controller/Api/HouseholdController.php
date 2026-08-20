<?php

namespace App\Controller\Api;

use App\Entity\Household;
use App\Repository\HouseholdRepository;
use App\Repository\SiteRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/households', name: 'api_households_')]
final class HouseholdController extends AbstractController
{
    #[Route('', name: 'index', methods: ['GET'])]
    public function index(HouseholdRepository $householdRepository): JsonResponse
    {
        $households = $householdRepository->findAll();

        $data = array_map(fn (Household $household) => $this->normalizeHousehold($household), $households);

        return $this->json($data);
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(Household $household): JsonResponse
    {
        return $this->json($this->normalizeHousehold($household));
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

        $household = new Household();
        $household->setReference($payload['reference'] ?? '');
        $household->setOwnerName($payload['ownerName'] ?? '');
        $household->setPhoneNumber($payload['phoneNumber'] ?? null);
        $household->setConnectionStatus($payload['connectionStatus'] ?? '');
        $household->setSite($site);

        if (!empty($payload['connectedAt'])) {
            try {
                $household->setConnectedAt(new \DateTimeImmutable($payload['connectedAt']));
            } catch (\Exception) {
                return $this->json(['error' => 'Invalid connectedAt datetime format.'], 400);
            }
        }

        $errors = $validator->validate($household);
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

        $entityManager->persist($household);
        $entityManager->flush();

        return $this->json($this->normalizeHousehold($household), 201);
    }

    private function normalizeHousehold(Household $household): array
    {
        return [
            'id' => $household->getId(),
            'reference' => $household->getReference(),
            'ownerName' => $household->getOwnerName(),
            'phoneNumber' => $household->getPhoneNumber(),
            'connectionStatus' => $household->getConnectionStatus(),
            'connectedAt' => $household->getConnectedAt()?->format(DATE_ATOM),
            'createdAt' => $household->getCreatedAt()->format(DATE_ATOM),
            'updatedAt' => $household->getUpdatedAt()->format(DATE_ATOM),
            'site' => [
                'id' => $household->getSite()->getId(),
                'code' => $household->getSite()->getCode(),
                'name' => $household->getSite()->getName(),
            ],
        ];
    }
}