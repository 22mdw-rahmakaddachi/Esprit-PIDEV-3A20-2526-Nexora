<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class ModerationService
{
    // Liste de mots interdits en français et anglais (fallback si API indisponible)
    private const BAD_WORDS = [
        'merde','putain','connard','connasse','salope','enculé','enculer','fdp','fils de pute',
        'bâtard','batard','nique','niquer','ta gueule','va te faire','idiot','imbécile',
        'fuck','shit','bitch','asshole','bastard','cunt','dick','pussy','whore','nigger',
        'pute','con','conne','abruti','crétin','débile','mongol','attardé',
    ];

    public function __construct(
        private HttpClientInterface $client,
        private string $huggingfaceApiKey = ''
    ) {}

    /**
     * Returns ['toxic' => bool, 'score' => float, 'method' => string]
     */
    public function analyze(string $text): array
    {
        $textLower = mb_strtolower($text);

        // ── 1. Vérification locale (mots interdits) ──
        foreach (self::BAD_WORDS as $word) {
            if (str_contains($textLower, $word)) {
                return ['toxic' => true, 'score' => 1.0, 'method' => 'local', 'word' => $word];
            }
        }

        // ── 2. API HuggingFace (si clé disponible) ──
        if ($this->huggingfaceApiKey) {
            try {
                $response = $this->client->request('POST',
                    'https://api-inference.huggingface.co/models/unitary/toxic-bert',
                    [
                        'headers' => [
                            'Authorization' => 'Bearer ' . $this->huggingfaceApiKey,
                            'Content-Type'  => 'application/json',
                        ],
                        'json'    => ['inputs' => $text],
                        'timeout' => 8,
                    ]
                );

                $data = $response->toArray(false);

                // Response: [[{"label":"toxic","score":0.95},{"label":"non_toxic","score":0.05}]]
                if (isset($data[0]) && is_array($data[0])) {
                    foreach ($data[0] as $item) {
                        if (isset($item['label']) && strtolower($item['label']) === 'toxic') {
                            $score = (float)($item['score'] ?? 0);
                            return [
                                'toxic'  => $score > 0.75,
                                'score'  => $score,
                                'method' => 'huggingface',
                            ];
                        }
                    }
                }
            } catch (\Throwable) {
                // API unavailable — fallback to local only
            }
        }

        return ['toxic' => false, 'score' => 0.0, 'method' => 'local'];
    }
}
