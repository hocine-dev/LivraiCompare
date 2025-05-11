<?php
// src/Controller/TarifController.php

namespace App\Controller;

use App\Repository\TarifRepository;
use App\Repository\WilayaRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class TarifController extends AbstractController
{
    #[Route('/api/tarifs', name: 'get_tarifs', methods: ['POST'])]
    public function getTarifs(
        Request $request,
        TarifRepository $tarifRepo,
        WilayaRepository $wilayaRepo
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);

        // Récupération des entités Wilaya
        $origineWilaya      = $wilayaRepo->find($data['origin']['wilaya'] ?? 0);
        $destinationWilaya  = $wilayaRepo->find($data['destination']['wilaya'] ?? 0);

        if (!$origineWilaya || !$destinationWilaya) {
            return $this->json(
                ['error' => 'Wilaya d’origine ou de destination invalide.'],
                JsonResponse::HTTP_BAD_REQUEST
            );
        }

        // Recherche des tarifs uniquement par wilayas
        $tarifs = $tarifRepo->createQueryBuilder('t')
            ->andWhere('t.origineWilaya = :ow')
            ->andWhere('t.destinationWilaya = :dw')
            ->setParameter('ow', $origineWilaya)
            ->setParameter('dw', $destinationWilaya)
            ->orderBy('t.tarif', 'ASC')
            ->getQuery()
            ->getResult();

        // Format de la réponse
        $results = array_map(function($tarif) {
            return [
                'societe' => $tarif->getSociete()->getNom(),
                'siteWeb' => $tarif->getSociete()->getSiteWeb(),
                'tarif'    => $tarif->getTarif(),
            ];
        }, $tarifs);

        return $this->json($results);
    }
}
