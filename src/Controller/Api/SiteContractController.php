<?php

namespace App\Controller\Api;

use App\Entity\SiteContract;
use App\Repository\ContractPlanRepository;
use App\Repository\SiteContractRepository;
use App\Repository\SiteRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/site-contracts', name: 'api_site_contracts_')]
final class SiteContractController extends AbstractController
{
    #[Route('', name: 'index', methods: ['GET'])]
    public function index(SiteContractRepository $siteContractRepository): JsonResponse
    {
        $contracts = $siteContractRepository->findAll();

        $data = array_map(fn (SiteContract $contract) => $this->normalizeContract($contract), $contracts);

        return $this->json($data);
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(SiteContract $siteContract): JsonResponse
    {
        return $this->json($this->normalizeContract($siteContract));
    }

    #[Route('', name: 'create', methods: ['POST'])]
    public function create(
        Request $request,
        EntityManagerInterface $entityManager,
        ValidatorInterface $validator,
        SiteRepository $siteRepository,
        ContractPlanRepository $contractPlanRepository
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

        $contractPlanId = $payload['contractPlanId'] ?? null;
        if (!is_int($contractPlanId) && !ctype_digit((string) $contractPlanId)) {
            return $this->json([
                'errors' => [[
                    'field' => 'contractPlanId',
                    'message' => 'A valid contractPlanId is required.',
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

        $contractPlan = $contractPlanRepository->find((int) $contractPlanId);
        if ($contractPlan === null) {
            return $this->json([
                'errors' => [[
                    'field' => 'contractPlanId',
                    'message' => 'Contract plan not found.',
                ]],
            ], 422);
        }

        if (empty($payload['startDate'])) {
            return $this->json([
                'errors' => [[
                    'field' => 'startDate',
                    'message' => 'startDate is required.',
                ]],
            ], 422);
        }

        if (empty($payload['status'])) {
            return $this->json([
                'errors' => [[
                    'field' => 'status',
                    'message' => 'status is required.',
                ]],
            ], 422);
        }

        $siteContract = new SiteContract();
        $siteContract->setSite($site);
        $siteContract->setContractPlan($contractPlan);
        $siteContract->setStatus($payload['status']);

        try {
            $siteContract->setStartDate(new \DateTimeImmutable($payload['startDate']));
        } catch (\Exception) {
            return $this->json(['error' => 'Invalid startDate datetime format.'], 400);
        }

        if (!empty($payload['endDate'])) {
            try {
                $siteContract->setEndDate(new \DateTimeImmutable($payload['endDate']));
            } catch (\Exception) {
                return $this->json(['error' => 'Invalid endDate datetime format.'], 400);
            }
        }

        $errors = $validator->validate($siteContract);
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

        $entityManager->persist($siteContract);
        $entityManager->flush();

        return $this->json($this->normalizeContract($siteContract), 201);
    }

    #[Route('/{id}', name: 'update', methods: ['PUT'])]
    public function update(
        SiteContract $siteContract,
        Request $request,
        EntityManagerInterface $entityManager,
        ValidatorInterface $validator,
        SiteRepository $siteRepository,
        ContractPlanRepository $contractPlanRepository
    ): JsonResponse {
        $payload = json_decode($request->getContent(), true);

        if (!is_array($payload)) {
            return $this->json(['error' => 'Invalid JSON payload.'], 400);
        }

        if (array_key_exists('siteId', $payload)) {
            $siteId = $payload['siteId'];

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

            $siteContract->setSite($site);
        }

        if (array_key_exists('contractPlanId', $payload)) {
            $contractPlanId = $payload['contractPlanId'];

            if (!is_int($contractPlanId) && !ctype_digit((string) $contractPlanId)) {
                return $this->json([
                    'errors' => [[
                        'field' => 'contractPlanId',
                        'message' => 'A valid contractPlanId is required.',
                    ]],
                ], 422);
            }

            $contractPlan = $contractPlanRepository->find((int) $contractPlanId);
            if ($contractPlan === null) {
                return $this->json([
                    'errors' => [[
                        'field' => 'contractPlanId',
                        'message' => 'Contract plan not found.',
                    ]],
                ], 422);
            }

            $siteContract->setContractPlan($contractPlan);
        }

        if (array_key_exists('status', $payload)) {
            $siteContract->setStatus((string) $payload['status']);
        }

        if (array_key_exists('startDate', $payload)) {
            try {
                $siteContract->setStartDate(new \DateTimeImmutable($payload['startDate']));
            } catch (\Exception) {
                return $this->json(['error' => 'Invalid startDate datetime format.'], 400);
            }
        }

        if (array_key_exists('endDate', $payload)) {
            if ($payload['endDate'] === null || $payload['endDate'] === '') {
                $siteContract->setEndDate(null);
            } else {
                try {
                    $siteContract->setEndDate(new \DateTimeImmutable($payload['endDate']));
                } catch (\Exception) {
                    return $this->json(['error' => 'Invalid endDate datetime format.'], 400);
                }
            }
        }

        $siteContract->setUpdatedAt(new \DateTimeImmutable());

        $errors = $validator->validate($siteContract);
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

        $entityManager->flush();

        return $this->json($this->normalizeContract($siteContract));
    }

    private function normalizeContract(SiteContract $contract): array
    {
        return [
            'id' => $contract->getId(),
            'startDate' => $contract->getStartDate()->format(DATE_ATOM),
            'endDate' => $contract->getEndDate()?->format(DATE_ATOM),
            'status' => $contract->getStatus(),
            'createdAt' => $contract->getCreatedAt()->format(DATE_ATOM),
            'updatedAt' => $contract->getUpdatedAt()->format(DATE_ATOM),
            'site' => [
                'id' => $contract->getSite()->getId(),
                'code' => $contract->getSite()->getCode(),
                'name' => $contract->getSite()->getName(),
            ],
            'contractPlan' => [
                'id' => $contract->getContractPlan()->getId(),
                'code' => $contract->getContractPlan()->getCode(),
                'name' => $contract->getContractPlan()->getName(),
                'annualPrice' => $contract->getContractPlan()->getAnnualPrice(),
            ],
        ];
    }
}