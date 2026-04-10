<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Service Google Drive via API REST + Service Account JWT
 * N'utilise PAS google/apiclient (problème de chemins Windows trop longs).
 * Utilise uniquement symfony/http-client + firebase/php-jwt (déjà installés).
 */
class GoogleDriveService
{
    private const FOLDER_ID      = '1GG-jfhkEU7rrkFmP1RZnP-aKzlOKGDAR';
    private const TOKEN_URL      = 'https://oauth2.googleapis.com/token';
    private const UPLOAD_URL     = 'https://www.googleapis.com/upload/drive/v3/files?uploadType=multipart';
    private const SCOPE          = 'https://www.googleapis.com/auth/drive.file';

    private HttpClientInterface $http;
    private string              $credentialsPath;
    private ?string             $accessToken  = null;
    private int                 $tokenExpires = 0;

    public function __construct(HttpClientInterface $http, string $projectDir)
    {
        $this->http            = $http;
        $this->credentialsPath = $projectDir . '/config/google/credentials.json';
    }

    // ─────────────────────────────────────────────
    //  Upload d'un fichier vers Google Drive
    //  Retourne l'URL directe : https://drive.google.com/uc?id=FILE_ID
    // ─────────────────────────────────────────────
    public function uploadImage(string $filePath, string $fileName, string $mimeType = 'image/jpeg'): string
    {
        $token   = $this->getAccessToken();
        $content = file_get_contents($filePath);

        // Metadata JSON
        $metadata = json_encode([
            'name'    => $fileName,
            'parents' => [self::FOLDER_ID],
        ]);

        // Multipart boundary
        $boundary = '----DriveUpload' . uniqid();
        $body  = "--{$boundary}\r\n";
        $body .= "Content-Type: application/json; charset=UTF-8\r\n\r\n";
        $body .= $metadata . "\r\n";
        $body .= "--{$boundary}\r\n";
        $body .= "Content-Type: {$mimeType}\r\n\r\n";
        $body .= $content . "\r\n";
        $body .= "--{$boundary}--";

        $response = $this->http->request('POST', self::UPLOAD_URL, [
            'headers' => [
                'Authorization' => 'Bearer ' . $token,
                'Content-Type'  => "multipart/related; boundary={$boundary}",
            ],
            'body' => $body,
            'query' => ['fields' => 'id'],
        ]);

        $data   = $response->toArray();
        $fileId = $data['id'] ?? null;

        if (!$fileId) {
            throw new \RuntimeException('Google Drive upload failed: ' . json_encode($data));
        }

        // Rendre le fichier public (lecture pour tout le monde)
        $this->makePublic($fileId, $token);

        // URL directe pour affichage dans <img> (thumbnail drive, marche avec les fichiers publics)
        return 'https://drive.google.com/thumbnail?id=' . $fileId . '&sz=w1200';
    }

    // ─────────────────────────────────────────────
    //  Supprimer un fichier de Google Drive
    // ─────────────────────────────────────────────
    public function deleteFile(string $driveUrl): void
    {
        $fileId = $this->extractFileId($driveUrl);
        if (!$fileId) {
            return;
        }

        try {
            $token = $this->getAccessToken();
            $this->http->request('DELETE', "https://www.googleapis.com/drive/v3/files/{$fileId}", [
                'headers' => ['Authorization' => 'Bearer ' . $token],
            ])->getStatusCode(); // déclenche la requête
        } catch (\Throwable $e) {
            // On ignore si le fichier n'existe plus
        }
    }

    // ─────────────────────────────────────────────
    //  Extraire l'ID Drive depuis l'URL stockée
    // ─────────────────────────────────────────────
    public function extractFileId(string $url): ?string
    {
        // Format thumbnail : https://drive.google.com/thumbnail?id=XXX&sz=...
        if (preg_match('/[?&]id=([a-zA-Z0-9_\-]+)/', $url, $m)) {
            return $m[1];
        }
        // Format uc     : https://drive.google.com/uc?id=XXX
        if (preg_match('/uc\?.*id=([a-zA-Z0-9_\-]+)/', $url, $m)) {
            return $m[1];
        }
        // Format open   : https://drive.google.com/file/d/XXX/view
        if (preg_match('/\/d\/([a-zA-Z0-9_\-]+)/', $url, $m)) {
            return $m[1];
        }
        return null;
    }

    // ─────────────────────────────────────────────
    //  Obtenir un access token via JWT Service Account
    // ─────────────────────────────────────────────
    private function getAccessToken(): string
    {
        // Cache 55 min (token dure 60 min)
        if ($this->accessToken && time() < $this->tokenExpires) {
            return $this->accessToken;
        }

        $creds = json_decode(file_get_contents($this->credentialsPath), true);

        $now = time();
        $jwt = $this->buildJwt([
            'iss'   => $creds['client_email'],
            'scope' => self::SCOPE,
            'aud'   => self::TOKEN_URL,
            'iat'   => $now,
            'exp'   => $now + 3600,
        ], $creds['private_key']);

        $response = $this->http->request('POST', self::TOKEN_URL, [
            'body' => [
                'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                'assertion'  => $jwt,
            ],
        ]);

        $data = $response->toArray();

        if (empty($data['access_token'])) {
            throw new \RuntimeException('Impossible d\'obtenir le token Google: ' . json_encode($data));
        }

        $this->accessToken  = $data['access_token'];
        $this->tokenExpires = $now + 3300; // renouveler après 55 min

        return $this->accessToken;
    }

    // ─────────────────────────────────────────────
    //  Construire un JWT RS256 (sans dépendance externe)
    // ─────────────────────────────────────────────
    private function buildJwt(array $claims, string $privateKey): string
    {
        $header  = $this->base64UrlEncode(json_encode(['alg' => 'RS256', 'typ' => 'JWT']));
        $payload = $this->base64UrlEncode(json_encode($claims));

        $signature = '';
        openssl_sign("{$header}.{$payload}", $signature, $privateKey, OPENSSL_ALGO_SHA256);

        return "{$header}.{$payload}." . $this->base64UrlEncode($signature);
    }

    private function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    // ─────────────────────────────────────────────
    //  Rendre un fichier Drive public
    // ─────────────────────────────────────────────
    private function makePublic(string $fileId, string $token): void
    {
        $this->http->request(
            'POST',
            "https://www.googleapis.com/drive/v3/files/{$fileId}/permissions",
            [
                'headers' => [
                    'Authorization' => 'Bearer ' . $token,
                    'Content-Type'  => 'application/json',
                ],
                'body' => json_encode(['type' => 'anyone', 'role' => 'reader']),
            ]
        )->getStatusCode();
    }
}
