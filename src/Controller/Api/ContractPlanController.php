<?php

namespace App\Controller\Api;

use App\Entity\ContractPlan;
use App\Repository\ContractPlanRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/contract-plans', name: 'api_contract_plans_')]
final class ContractPlanController extends AbstractController
{
    #[Route('', name: 'index', methods: ['GET'])]
    public function index(ContractPlanRepository $contractPlanRepository): JsonResponse
    {
        $plans = $contractPlanRepository->findAll();

        $data = array_map(fn (ContractPlan $plan) => $this->normalizePlan($plan), $plans);

        return $this->json($data);
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(ContractPlan $contractPlan): JsonResponse
    {
        return $this->json($this->normalizePlan($contractPlan));
    }

    private function normalizePlan(ContractPlan $plan): array
    {
        return [
            'id' => $plan->getId(),
            'name' => $plan->getName(),
            'code' => $plan->getCode(),
            'annualPrice' => $plan->getAnnualPrice(),
            'freePreventiveVisitsPerYear' => $plan->getFreePreventiveVisitsPerYear(),
            'additionalVisitCost' => $plan->getAdditionalVisitCost(),
            'curativeInterventionCost' => $plan->getCurativeInterventionCost(),
            'consumableReplacementCost' => $plan->getConsumableReplacementCost(),
            'annualConsumableCoverageLimit' => $plan->getAnnualConsumableCoverageLimit(),
            'phoneSupportIncluded' => $plan->isPhoneSupportIncluded(),
            'status' => $plan->getStatus(),
            'createdAt' => $plan->getCreatedAt()?->format(DATE_ATOM),
            'updatedAt' => $plan->getUpdatedAt()?->format(DATE_ATOM),
        ];
    }
}