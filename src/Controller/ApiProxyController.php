<?php
// src/Controller/ApiProxyController.php
namespace App\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Routing\Annotation\Route;

class ApiProxyController
{
    private HttpClientInterface $client;
    private HttpKernelInterface $kernel;
    private string $apiKey;
    private string $google_api;

    public function __construct(HttpClientInterface $client, HttpKernelInterface $kernel, ParameterBagInterface $params)
    {
        $this->client = $client;
        $this->kernel = $kernel;
        $this->apiKey = $params->get('api_key'); // depuis config/services.yaml
        $this->google_api = $params->get('google_api'); // depuis config/services.yaml
    }

    #[Route('/api/proxy/get-communes/{wilayaId}', name: 'api_proxy_get_communes', methods: ['GET'])]
    public function getCommunes(string $wilayaId): JsonResponse
    {
        // 1) Crée une sous-requête interne vers /api/get-communes/{wilayaId}
        $subRequest = Request::create(
            sprintf('/api/get-communes/%s', $wilayaId),
            'GET',
            [],              // query
            [],              // cookies
            [],              // files
            $this->addApiKeyHeader() // server (en-têtes)
        );

        // 2) Exécute la sous-requête
        $response = $this->kernel->handle($subRequest, HttpKernelInterface::SUB_REQUEST);

        // 3) Retourne directement son contenu JSON
        return new JsonResponse(
            json_decode($response->getContent(), true),
            $response->getStatusCode(),
            $response->headers->all()
        );
    }

    #[Route('/api/proxy/get-tarifs', name: 'api_proxy_get_tarifs', methods: ['POST'])]
    public function getTarifsCalculesProxy(Request $request): JsonResponse
    {


        $data = json_decode($request->getContent(), true);
        $recaptchaToken = $data['recaptchaToken'] ?? null;

        if (!$recaptchaToken) {
            return new JsonResponse(['error' => 'Missing reCAPTCHA token.'], 400);
        }

        // Vérification reCAPTCHA avec Google
        $secret = $this->google_api; 
        $verifyUrl = 'https://www.google.com/recaptcha/api/siteverify';

        $response = file_get_contents($verifyUrl . '?secret=' . urlencode($secret) . '&response=' . urlencode($recaptchaToken));
        $result = json_decode($response, true);

        // Si le token est invalide ou score faible
        if (!$result['success'] || ($result['score'] ?? 0) < 0.5) {
            return new JsonResponse([
                'error' => 'Échec de la vérification reCAPTCHA.',
                'details' => $result
            ], 403);
        }



        // 1) Crée une sous-requête interne vers /api/get-tarifs
        $subRequest = Request::create(
            '/api/get-tarifs',
            'POST',
            [],
            [],
            [],
            $this->addApiKeyHeader(), // server (en-têtes)
            $request->getContent()    // contenu JSON brut
        );

        // 2) Exécute la sous-requête
        $response = $this->kernel->handle($subRequest, HttpKernelInterface::SUB_REQUEST);
        // 3) Retourne directement son contenu JSON
        return new JsonResponse(
            json_decode($response->getContent(), true),
            $response->getStatusCode(),
            $response->headers->all()
        );
    }



    private function addApiKeyHeader(): array
    {
        // Symfony attend les en-têtes dans le serveur en-prefixé HTTP_
        return [
            'HTTP_X_API_KEY' => $this->apiKey,
        ];
    }
}
