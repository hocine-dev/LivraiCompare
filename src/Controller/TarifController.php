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
   #[Route('/api/get-tarifs', name: 'get_tarifs_calcules', methods: ['POST'])]
public function getTarifsCalcules(
    Request $request,
    TarifRepository $tarifRepo,
    WilayaRepository $wilayaRepo
): JsonResponse {
    // 1. Lire et décoder le JSON brut
    $data = json_decode($request->getContent(), true);

    // 2. Extraire et valider les paramètres
    $originId   = $data['origin']['wilaya']      ?? 0;
    $originZone = $data['origin']['zone']        ?? 'A';
    $destId     = $data['destination']['wilaya'] ?? 0;
    $destZone   = $data['destination']['zone']   ?? 'A';

    $weightRaw  = $data['package']['weight']     ?? '0 kg';
    $valueRaw   = $data['package']['value']      ?? '0 DZD';
    // nettoyer les chaînes "2 kg" → 2.0, "2000 DZD" → 2000.0
    $weight = (float) filter_var($weightRaw, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    $value  = (float) filter_var($valueRaw, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);

    $mode     = $data['delivery']['mode']    ?? 'standard';
    $express  = ($data['delivery']['express'] ?? 'Non') === 'Oui';
    $urgency  = $data['delivery']['urgency'] ?? 'standard';

    // Valider l’existence des wilayas
    $origineWilaya     = $wilayaRepo->find($originId);
    $destinationWilaya = $wilayaRepo->find($destId);
    if (!$origineWilaya || !$destinationWilaya) {
        return $this->json(
            ['error' => 'Wilaya d’origine ou de destination invalide.'],
            JsonResponse::HTTP_BAD_REQUEST
        );
    }

    // 3. Récupérer les tarifs de base (wilaya → wilaya)
    $tarifsBase = $tarifRepo->createQueryBuilder('t')
        ->andWhere('t.origineWilaya = :ow')
        ->andWhere('t.destinationWilaya = :dw')
        ->setParameter('ow', $origineWilaya)
        ->setParameter('dw', $destinationWilaya)
        ->getQuery()
        ->getResult();

    // 4. Matrice de zones et calcul
    $zoneMatrix = [
        'A'=>['A'=>1.0,'B'=>1.1,'C'=>1.2,'D'=>1.3],
        'B'=>['A'=>1.1,'B'=>1.0,'C'=>1.15,'D'=>1.25],
        'C'=>['A'=>1.2,'B'=>1.15,'C'=>1.0,'D'=>1.25],
        'D'=>['A'=>1.3,'B'=>1.25,'C'=>1.25,'D'=>1.3],
    ];

    $results = [];
    foreach ($tarifsBase as $item) {
        // tarif de base
        $tarif = (float)$item->getTarif()
               * ($zoneMatrix[$originZone][$destZone] ?? 1.0);

        // surcoût poids
        if ($weight > 5) {
            $tarif += ($weight - 5) * 10;
        }
        // surcoût valeur
        if ($value > 10000) {
            $tarif += ceil(($value - 10000) / 1000) * 10;
        }
        // domicile
        if ($mode === 'domicile') {
            $tarif += 200;
        }
        // express/urgence
        if ($express) {
            switch ($urgency) {
                case 'same-day': $tarif += 1000; break;
                case 'next-day': $tarif += 500;  break;
                case 'two-day':  $tarif += 300;  break;
            }
        }

        // délai
        $delaiInit = (int)$item->getDelaiLivraison();
        $delai = $express
            ? match($urgency) {
                'same-day' => 12,
                'next-day' => 24,
                'two-day'  => 48,
                default     => $delaiInit,
              }
            : $delaiInit;

        // on stocke le résultat final
        $results[] = [
            'societe'      => $item->getSociete()->getNom(),
            'tarif'        => round($tarif, 2),
            'delai_heures' => $delai,
            'siteWeb' =>  $item->getSociete()->getSiteWeb(),
        ];
    }

    // 5. On renvoie uniquement le tableau de résultats
    return $this->json($results);
}

}
