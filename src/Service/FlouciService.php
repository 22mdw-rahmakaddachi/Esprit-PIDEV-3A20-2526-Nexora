<?php

namespace App\Service;

use Symfony\Component\HttpClient\HttpClient;

class FlouciService
{
    private string $publicKey;
    private string $privateKey;
    private string $apiUrl;
    private bool   $modeTest;

    public function __construct(
        string $publicKey  = '',
        string $privateKey = '',
        string $apiUrl     = 'https://developers.flouci.com/api/v2',
        string $modeTest   = 'false'
    ) {
        $this->publicKey  = $publicKey;
        $this->privateKey = $privateKey;
        $this->apiUrl     = $apiUrl;
        $this->modeTest   = in_array(strtolower(trim($modeTest)), ['true', '1', 'yes']);
    }

    public function initierPaiement(float $montant, string $successUrl, string $failUrl, string $trackingId): array
    {
        // MODE TEST : aucune requête envoyée à Konnect
        if ($this->modeTest) {
            $timestamp = time();
            $simulationId = 'SIMULATION_' . $trackingId . '_' . $timestamp;
            return [
                'success'    => true,
                'payment_id' => $simulationId,
                'link'       => $successUrl . '?payment_id=' . $simulationId,
                'mode_test'  => true,
            ];
        }

        try {
            $client = HttpClient::create(['timeout' => 10]);
            $response = $client->request('POST', $this->apiUrl . '/generate_payment', [
                'headers' => [
                    'Content-Type'  => 'application/json',
                    'Authorization' => 'Bearer ' . $this->publicKey . ':' . $this->privateKey,
                ],
                'json' => [
                    'amount'                => (int) round($montant * 1000),
                    'success_link'          => $successUrl,
                    'fail_link'             => $failUrl,
                    'developer_tracking_id' => $trackingId,
                    'accept_card'           => true,
                ],
            ]);

            $data = $response->toArray(false);

            if (isset($data['result']['payment_id'], $data['result']['link'])) {
                return [
                    'success'    => true,
                    'payment_id' => $data['result']['payment_id'],
                    'link'       => $data['result']['link'],
                ];
            }

            return ['success' => false, 'error' => 'Réponse inattendue de Flouci.'];

        } catch (\Throwable $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function verifierPaiement(string $paymentId): array
    {
        // MODE TEST : paiement toujours validé
        if ($this->modeTest || str_starts_with($paymentId, 'SIMULATION_')) {
            return ['success' => true, 'statut' => 'SUCCESS', 'data' => ['mode_test' => true]];
        }

        try {
            $client = HttpClient::create(['timeout' => 10]);
            $response = $client->request('GET', $this->apiUrl . '/verify_payment/' . $paymentId, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->publicKey . ':' . $this->privateKey,
                ],
            ]);

            $data   = $response->toArray(false);
            $statut = $data['result']['status'] ?? 'INCONNU';

            return ['success' => true, 'statut' => $statut, 'data' => $data];

        } catch (\Throwable $e) {
            return ['success' => false, 'statut' => 'ERREUR', 'error' => $e->getMessage()];
        }
    }

    public function isModeTest(): bool
    {
        return $this->modeTest;
    }
}
