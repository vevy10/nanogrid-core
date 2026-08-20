<?php

namespace App\Controller\Api;

use App\Entity\Site;
use App\Repository\SiteRepository;
use App\Service\Api\JsonRequestDecoder;
use App\Service\Api\SiteService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/sites', name: 'api_sites_')]
final class SiteController extends AbstractController
{
    #[Route('', name: 'index', methods: ['GET'])]
    public function index(SiteRepository $siteRepository, SiteService $siteService): JsonResponse
    {
        return $this->json($siteService->normalizeCollection($siteRepository->findAll()));
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(Site $site, SiteService $siteService): JsonResponse
    {
        return $this->json($siteService->normalize($site));
    }

    #[Route('', name: 'create', methods: ['POST'])]
    public function create(
        Request $request,
        JsonRequestDecoder $jsonRequestDecoder,
        SiteService $siteService
    ): JsonResponse {
        $site = $siteService->createFromPayload($jsonRequestDecoder->decode($request));

        return $this->json($siteService->normalize($site), 201);
    }

    #[Route('/{id}', name: 'update', methods: ['PUT'])]
    public function update(
        Request $request,
        Site $site,
        JsonRequestDecoder $jsonRequestDecoder,
        SiteService $siteService
    ): JsonResponse {
        $siteService->updateFromPayload($site, $jsonRequestDecoder->decode($request));

        return $this->json($siteService->normalize($site));
    }
}
