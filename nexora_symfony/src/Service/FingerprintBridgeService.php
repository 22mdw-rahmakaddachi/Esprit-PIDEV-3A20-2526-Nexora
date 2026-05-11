<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class FingerprintBridgeService
{
    public function __construct(
        private HttpClientInterface $client,
        private string $bridgeUrl = 'http://localhost:5000'
    ) {}

    /** Enrôler une empreinte pour un utilisateur */
    public function enrollFingerprint(int $userId): array
    {
        try {
            $response = $this->client->request('POST', $this->bridgeUrl . '/enroll', [
                'json'    => ['user_id' => $userId],
                'timeout' => 120,  // 2 minutes
                'max_duration' => 120,
            ]);
            return $response->toArray();
        } catch (\Exception $e) {
            return ['success' => false, 'message' => 'Bridge non disponible: ' . $e->getMessage()];
        }
    }

    /** Vérifier une empreinte (le lecteur scanne et retourne le finger_id) */
    public function verifyFingerprint(): array
    {
        try {
            $response = $this->client->request('POST', $this->bridgeUrl . '/verify', [
                'timeout' => 15,
            ]);
            return $response->toArray();
        } catch (\Exception $e) {
            return ['authenticated' => false, 'message' => 'Bridge non disponible: ' . $e->getMessage()];
        }
    }

    /** Statut du lecteur */
    public function getStatus(): array
    {
        try {
            $response = $this->client->request('GET', $this->bridgeUrl . '/status', ['timeout' => 5]);
            return $response->toArray();
        } catch (\Exception $e) {
            return ['connected' => false, 'message' => 'Lecteur non connecté'];
        }
    }

    public function getEnrollStatus(): array
    {
        try {
            $response = $this->client->request('GET', $this->bridgeUrl . '/enroll/status', ['timeout' => 3]);
            return $response->toArray();
        } catch (\Exception $e) {
            return ['step' => 0];
        }
    }
}
