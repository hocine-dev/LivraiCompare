<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[ORM\Table(name: 'wilaya')]
class Wilaya
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private int $id;

    #[ORM\Column(type: 'string', length: 100)]
    private string $nom;

    // Nouveau champ zone (A, B, C ou D)
    #[Assert\Choice(
        choices: ['A','B','C','D'],
        message: 'Zone invalide, choisissez parmi A, B, C ou D.'
    )]
    #[ORM\Column(type: 'string', length: 1, nullable: false)]
    private string $zone;

    public function getId(): int
    {
        return $this->id;
    }

    public function getNom(): string
    {
        return $this->nom;
    }

    public function setNom(string $nom): self
    {
        $this->nom = $nom;
        return $this;
    }

    public function getZone(): string
    {
        return $this->zone;
    }

    public function setZone(string $zone): self
    {
        $this->zone = $zone;
        return $this;
    }
}
