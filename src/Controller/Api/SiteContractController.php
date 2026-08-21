<?php

namespace App\Controller\Api;

use App\Entity\SiteContract;
use App\Repository\SiteContractRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

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