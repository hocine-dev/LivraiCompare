<?php
// src/Controller/TarifController.php

namespace App\Controller;

use App\Entity\Tarif;
use App\Repository\TarifRepository;
use App\Repository\CommuneRepository;
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
        CommuneRepository $communeRepo,
        WilayaRepository $wilayaRepo
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);

        $origineWilaya = $wilayaRepo->find($data['origin']['wilaya']);
        $origineCommune = $communeRepo->find($data['origin']['commune']);
        $destinationWilaya = $wilayaRepo->find($data['destination']['wilaya']);
        $destinationCommune = $communeRepo->find($data['destination']['commune']);

        // Extraire poids sous forme de nombre
        $poids = floatval(str_replace(['kg', ' '], '', strtolower($data['package']['weight'] ?? '0')));

        $mode = $data['delivery']['mode'] ?? 'domicile';
        $urgence = $data['delivery']['urgency'] ?? 'standard';

        $tarifs = $tarifRepo->createQueryBuilder('t')
            ->andWhere('t.origineWilaya = :ow')
            ->andWhere('t.destinationWilaya = :dw')
            ->andWhere('t.mode = :mode')
            ->andWhere('t.urgence = :urgence')
            ->andWhere('t.poidsMin <= :poids')
            ->andWhere('t.poidsMax >= :poids')
            ->setParameter('ow', $origineWilaya)
            ->setParameter('dw', $destinationWilaya)
            ->setParameter('mode', $mode)
            ->setParameter('urgence', $urgence)
            ->setParameter('poids', $poids)
            ->getQuery()
            ->getResult();

        $results = [];

        foreach ($tarifs as $tarif) {
            $results[] = [
                'tarif' => $tarif->getTarif(),
                'delai_heures' => $tarif->getDelaiHeures(),
                'societe' => $tarif->getSociete()->getNom(), 
                'siteWeb' => $tarif->getSociete()->getSiteWeb(), 
            ];
        }

        return $this->json($results);
    }
}
