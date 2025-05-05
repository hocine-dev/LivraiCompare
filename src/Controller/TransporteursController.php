<?php
// src/Controller/TransporteursController.php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TransporteursController extends AbstractController
{
    #[Route('/transporteurs', name: 'transporteurs')]
    public function index(): Response
    {
        // Vous pouvez ajouter ici la logique pour récupérer la liste des transporteurs
        return $this->render('transporteurs/index.html.twig');
    }
    
    #[Route('/transporteurs/{slug}', name: 'transporteur_details')]
    public function details(string $slug): Response
    {
        // Logique pour récupérer les détails d'un transporteur spécifique
        return $this->render('transporteurs/details.html.twig', [
            'slug' => $slug
        ]);
    }
}
?>