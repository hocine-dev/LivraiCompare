<?php
// src/EventListener/ApiKeyAuthListener.php

namespace App\EventListener;

use App\Repository\ApiClientRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\RequestEvent;

class ApiKeyAuthListener
{
    private ApiClientRepository $clientRepo;
    private EntityManagerInterface $em;

    public function __construct(ApiClientRepository $clientRepo, EntityManagerInterface $em)
    {
        $this->clientRepo = $clientRepo;
        $this->em         = $em;
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();
        $path    = $request->getPathInfo();

        // 1) N’applique la vérification qu’aux routes commençant par /api, sauf /api
        if ($path === '/api' || 0 !== strpos($path, '/api/')) {
            return;
        }

        // 2) Récupère la clé depuis le header
        $apiKey = $request->headers->get('X-API-KEY');
        if (!$apiKey) {
            $event->setResponse(new JsonResponse(['error' => 'API key manquante'], 401));
            return;
        }

        // 3) Recherche du client
        $client = $this->clientRepo->findOneBy(['apiKey' => $apiKey]);
        if (!$client || !$client->isActive()) {
            $event->setResponse(new JsonResponse(['error' => 'Clé API invalide ou désactivée'], 403));
            return;
        }

        // 4) Vérifie la date d’expiration (1 mois)
        $now = new \DateTimeImmutable();
        if ($client->getSubscriptionEnd() < $now) {
            $event->setResponse(new JsonResponse(['error' => 'Abonnement expiré'], 403));
            return;
        }

        // 5) Vérifie le quota du plan
        $limits = [
            'starter'      => 1000,
            'professional' => 10000,
            'enterprise'   => null,
        ];
        $plan  = $client->getPlan();
        $limit = $limits[$plan] ?? null;

        if ($limit !== null && $client->getRequestCount() >= $limit) {
            $event->setResponse(new JsonResponse(['error' => 'Quota mensuel atteint'], 429));
            return;
        }

        // 6) Incrémente le compteur et persist
        $client->incrementRequestCount();
        $this->em->flush();

        // 7) (Optionnel) Attache l’objet client à la requête si besoin downstream
        $request->attributes->set('api_client', $client);
    }
}
