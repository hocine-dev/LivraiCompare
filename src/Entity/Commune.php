<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'commune')]
class Commune
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private int $id;

    #[ORM\Column(type: 'string', length: 100)]
    private string $nom;

    #[ORM\ManyToOne(targetEntity: Wilaya::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private Wilaya $wilaya;

    #[ORM\Column(type: 'string', length: 1, nullable: true)]
    private ?string $zone = null;

    public function getId(): int
    {
        return $this->id;
    }

    public function getNom(): string
    {
        return $this->nom;
    }

    public function setNom(string $nom): void
    {
        $this->nom = $nom;
    }

    public function getWilaya(): Wilaya
    {
        return $this->wilaya;
    }

    public function setWilaya(Wilaya $wilaya): void
    {
        $this->wilaya = $wilaya;
    }

    public function getZone(): ?string
    {
        return $this->zone;
    }

    public function setZone(?string $zone): void
    {
        $this->zone = $zone;
    }
}
