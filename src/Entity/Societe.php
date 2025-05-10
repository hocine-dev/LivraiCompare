<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'societe')]
class Societe
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private int $id;

    #[ORM\Column(type: 'string', length: 100, unique: true)]
    private string $nom;

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

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
private ?string $siteWeb = null;

public function getSiteWeb(): ?string
{
    return $this->siteWeb;
}

public function setSiteWeb(?string $siteWeb): void
{
    $this->siteWeb = $siteWeb;
}

}
