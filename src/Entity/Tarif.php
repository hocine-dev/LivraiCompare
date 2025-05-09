<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'tarif')]
#[ORM\UniqueConstraint(columns: [
    'origine_wilaya_id', 'origine_commune_id',
    'destination_wilaya_id', 'destination_commune_id',
    'mode', 'urgence', 'poids_min', 'poids_max', 'societe_id'
])]
class Tarif
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private int $id;

    #[ORM\ManyToOne(targetEntity: Wilaya::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'RESTRICT')]
    private Wilaya $origineWilaya;

    #[ORM\ManyToOne(targetEntity: Commune::class)]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?Commune $origineCommune = null;

    #[ORM\ManyToOne(targetEntity: Wilaya::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'RESTRICT')]
    private Wilaya $destinationWilaya;

    #[ORM\ManyToOne(targetEntity: Commune::class)]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?Commune $destinationCommune = null;

    #[ORM\Column(type: 'string', options: ['default' => 'domicile'])]
    private string $mode;

    #[ORM\Column(type: 'string', options: ['default' => 'standard'])]
    private string $urgence;

    #[ORM\Column(type: 'decimal', precision: 6, scale: 2)]
    private float $poidsMin;

    #[ORM\Column(type: 'decimal', precision: 6, scale: 2)]
    private float $poidsMax;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2)]
    private float $prixColisMin;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2)]
    private float $prixColisMax;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2)]
    private float $tarif;

    #[ORM\ManyToOne(targetEntity: Societe::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private Societe $societe;

    // Ajoute les getters & setters si nécessaire
}
