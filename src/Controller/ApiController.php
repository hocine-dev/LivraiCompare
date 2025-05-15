<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Repository\CommuneRepository;
use App\Repository\WilayaRepository;


class ApiController extends AbstractController
{
    #[Route('/api', name: 'api')]
    public function index(): Response
    {
        return $this->render('api/api.html.twig');
    }

    #[Route('api/get-communes/{wilayaId}', name: 'get_communes', methods: ['GET'])]
    public function getCommunes($wilayaId, CommuneRepository $communeRepository): JsonResponse
    {
        $communes = $communeRepository->findBy(['wilaya' => $wilayaId]);

        $data = array_map(fn($commune) => [
            'id' => $commune->getId(),
            'name' => $commune->getNom(),
            'zone' => $commune->getZone()
        ], $communes);

        return new JsonResponse($data);
    }

    #[Route('api/get-wilayas', name: 'get_wilayas', methods: ['GET'])]
    public function getWilayas(WilayaRepository $wilayaRepository): JsonResponse
    {
        $wilayas = $wilayaRepository->findAll();

        $data = array_map(fn($wilaya) => [
            'id' => $wilaya->getId(),
            'name' => $wilaya->getNom(),
            'zone' => $wilaya->getZone()
        ], $wilayas);

        return new JsonResponse($data);
    }
}