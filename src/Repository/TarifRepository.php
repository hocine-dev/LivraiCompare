<?php
// src/Repository/TarifRepository.php

namespace App\Repository;

use App\Entity\Tarif;
use App\Entity\Wilaya;
use App\Entity\Commune;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class TarifRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Tarif::class);
    }

    public function findMatchingTarifs(
        Wilaya $origineWilaya,
        ?Commune $origineCommune,
        Wilaya $destinationWilaya,
        ?Commune $destinationCommune,
        string $mode,
        string $urgence,
        float $poids
    ): array {
        $qb = $this->createQueryBuilder('t')
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
            ->setParameter('poids', $poids);

        if ($origineCommune) {
            $qb->andWhere('t.origineCommune = :oc')->setParameter('oc', $origineCommune);
        }

        if ($destinationCommune) {
            $qb->andWhere('t.destinationCommune = :dc')->setParameter('dc', $destinationCommune);
        }

        return $qb->getQuery()->getResult();
    }
}

?>