<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'tarif')]
#[ORM\UniqueConstraint(columns: ['origine_wilaya_id', 'destination_wilaya_id', 'societe_id'])]
class Tarif
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private int $id;

    #[ORM\ManyToOne(targetEntity: Wilaya::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'RESTRICT')]
    private Wilaya $origineWilaya;

    #[ORM\ManyToOne(targetEntity: Wilaya::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'RESTRICT')]
    private Wilaya $destinationWilaya;

    #[ORM\ManyToOne(targetEntity: Societe::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private Societe $societe;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2)]
    private float $tarif;

    #[ORM\Column(type: 'integer')]
private int $delaiLivraison;

    public function getId(): int
    {
        return $this->id;
    }

    public function getOrigineWilaya(): Wilaya
    {
        return $this->origineWilaya;
    }

    public function setOrigineWilaya(Wilaya $wilaya): self
    {
        $this->origineWilaya = $wilaya;
        return $this;
    }

    public function getDestinationWilaya(): Wilaya
    {
        return $this->destinationWilaya;
    }

    public function setDestinationWilaya(Wilaya $wilaya): self
    {
        $this->destinationWilaya = $wilaya;
        return $this;
    }

    public function getSociete(): Societe
    {
        return $this->societe;
    }

    public function setSociete(Societe $societe): self
    {
        $this->societe = $societe;
        return $this;
    }

    public function getTarif(): float
    {
        return $this->tarif;
    }

    public function setTarif(float $tarif): self
    {
        $this->tarif = $tarif;
        return $this;
    }

    public function getDelaiLivraison(): int
{
    return $this->delaiLivraison;
}

public function setDelaiLivraison(int $delaiLivraison): self
{
    $this->delaiLivraison = $delaiLivraison;
    return $this;
}
}
