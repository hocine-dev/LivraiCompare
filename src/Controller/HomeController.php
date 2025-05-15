<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'home')]
    public function index(): Response
    {
        $wilayasFile = $this->getParameter('kernel.project_dir') . '/public/data/wilayas.json';
        $communesFile = $this->getParameter('kernel.project_dir') . '/public/data/communes.json';


        if (!file_exists($wilayasFile)) {
            throw $this->createNotFoundException('Le fichier wilayas.json est introuvable.');
        }
        if (!file_exists($communesFile)) {
            throw $this->createNotFoundException('Le fichier communes.json est introuvable.');
        }

        $wilayasJson = file_get_contents($wilayasFile);
        $wilayas = json_decode($wilayasJson, true);

        $communesJson = file_get_contents($communesFile);
        $communes = json_decode($communesJson, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \RuntimeException('Erreur de décodage JSON : ' . json_last_error_msg());
        }

        // 2) Render Twig into a Response object (no 'token' parameter)
        $response = $this->render('home/index.html.twig', [
            'wilayas'  => $wilayas,
            'communes' => $communes,
            

        ]);

        return $response;
    }

    #[Route('/conditions', name: 'conditions')]
    public function conditions(): Response
    {
        return $this->render('pages/conditions.html.twig');
    }
    #[Route('/politique', name: 'politique')]
    public function politique(): Response
    {
        return $this->render('pages/politique.html.twig');
    }
}
