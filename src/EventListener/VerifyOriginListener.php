<?php
// src/EventListener/VerifyOriginListener.php
namespace App\EventListener;

use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class VerifyOriginListener
{
    public function onKernelRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();
        if ('/api/proxy/get-tarifs' === $request->getPathInfo()) {
            $origin  = $request->headers->get('Origin') ?: $request->headers->get('Referer');
            if (!$origin || !str_starts_with($origin, 'http://127.0.0.1')) {
                throw new AccessDeniedHttpException('Origine invalide');
            }
        }
    }
}

?>