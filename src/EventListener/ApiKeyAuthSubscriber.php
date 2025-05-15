<?php
// src/EventListener/ApiKeyAuthSubscriber.php

namespace App\EventListener;

use App\Repository\ApiClientRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpFoundation\JsonResponse;

class ApiKeyAuthSubscriber implements EventSubscriberInterface
{
    private ApiClientRepository $clientRepo;
    private EntityManagerInterface $em;

    public function __construct(ApiClientRepository $clientRepo, EntityManagerInterface $em)
    {
        $this->clientRepo = $clientRepo;
        $this->em         = $em;
    }

    public static function getSubscribedEvents(): array
    {
        // onKernelRequest sera appelé après le RouterListener (priorité 0)
        return [
            RequestEvent::class => ['onKernelRequest', 0],
        ];
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();
        $path    = $request->getPathInfo();

        // Exclure les routes proxy de la vérification de la clé API
        if (0 === strpos($path, '/api/proxy/')) {
            return;
        }

        if (0 !== strpos($path, '/api/')) {
            return;
        }

        $apiKey = $request->headers->get('X-API-KEY');
        if (!$apiKey) {
            $event->setResponse(new JsonResponse(['error' => 'API key manquante'], 401));
            return;
        }

        $client = $this->clientRepo->findOneBy(['apiKey' => $apiKey]);
        if (!$client || !$client->isActive()) {
            $event->setResponse(new JsonResponse(['error' => 'Clé API invalide ou désactivée'], 403));
            return;
        }

        $now = new \DateTimeImmutable();
        if ($client->getSubscriptionEnd() < $now) {
            $event->setResponse(new JsonResponse(['error' => 'Abonnement expiré'], 403));
            return;
        }

        $limits = [
            'starter'      => 1000,
            'professional' => 10000,
            'enterprise'   => null,
        ];
        $limit = $limits[$client->getPlan()] ?? null;

        if (null !== $limit && $client->getRequestCount() >= $limit) {
            $event->setResponse(new JsonResponse(['error' => 'Quota mensuel atteint'], 429));
            return;
        }

        $client->incrementRequestCount();
        $this->em->flush();

        // Optionnel : rendre l'objet client accessible plus loin
        $request->attributes->set('api_client', $client);
    }
}
