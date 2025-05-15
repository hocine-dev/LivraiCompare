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
        $originId   = $data['origin']['wilaya']      ?? 1;
        $originZone = $data['origin']['zone']        ?? 'A';
        $destId     = $data['destination']['wilaya'] ?? 1;
        $destZone   = $data['destination']['zone']   ?? 'A';

        $weightRaw  = $data['package']['weight']     ?? '1 kg';
        $valueRaw   = $data['package']['value']      ?? '1 DZD';
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
            'A' => ['A' => 1.0, 'B' => 1.1, 'C' => 1.2, 'D' => 1.3],
            'B' => ['A' => 1.1, 'B' => 1.0, 'C' => 1.15, 'D' => 1.25],
            'C' => ['A' => 1.2, 'B' => 1.15, 'C' => 1.0, 'D' => 1.25],
            'D' => ['A' => 1.3, 'B' => 1.25, 'C' => 1.25, 'D' => 1.3],
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

            // délai
            $delaiInit = (int)$item->getDelaiLivraison();

            if ($express) {
                if ($delaiInit > 96) {
                    $delai = $delaiInit; // Ignore urgency if delaiLivraison > 96 hours
                } else {
                    $delai = match ($urgency) {
                        'same-day' => 12,
                        'next-day' => 24,
                        'two-day'  => 48,
                        default    => $delaiInit,
                    };
                }

                // Ajouter un surcoût selon le délai choisi
                if ($delai === 12) {
                    $tarif += 1000;
                } elseif ($delai === 24) {
                    $tarif += 500;
                } elseif ($delai === 48) {
                    $tarif += 300;
                }
            } else {
                $delai = $delaiInit;
            }

            // Si express, ne garder que les tarifs qui respectent le délai demandé
            if ($express) {
                $delaiAttendu = match ($urgency) {
                    'same-day' => 12,
                    'next-day' => 24,
                    'two-day'  => 48,
                    default    => $delaiInit,
                };
                if ($delai !== $delaiAttendu) {
                    continue;
                }
            }

            // on stocke le résultat final
            $results[] = [
                'societe'      => $item->getSociete()->getNom(),
                'tarif'        => round($tarif, 2),
                'delai_heures' => $delai,
                'siteWeb'      => $item->getSociete()->getSiteWeb(),
            ];
        }

        // 5. On renvoie uniquement le tableau de résultats
        return $this->json($results);
    }
}
