<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class WeatherService
{
    private string $apiKey;

    public function __construct(
        private HttpClientInterface $httpClient,
        string $openWeatherApiKey
    ) {
        $this->apiKey = $openWeatherApiKey;
    }

    /**
     * Récupère la météo simplifiée pour une ville
     */
    public function getWeather(string $city): ?array
    {
        try {
            $response = $this->httpClient->request('GET', 'https://api.openweathermap.org/data/2.5/weather', [
                'query' => [
                    'q' => $city,
                    'appid' => $this->apiKey,
                    'units' => 'metric',
                    'lang' => 'fr'
                ]
            ]);

            $data = $response->toArray();

            return [
                'temp' => round($data['main']['temp']),
                'temp_max' => round($data['main']['temp_max']),
                'temp_min' => round($data['main']['temp_min']),
                'condition' => $data['weather'][0]['main'], // Rain, Clear, Clouds, etc.
                'description' => ucfirst($data['weather'][0]['description']),
                'icon' => $data['weather'][0]['icon']
            ];
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * Génère des conseils de bagages basés sur les données météo
     */
    public function getPackingTips(?array $weather): array
    {
        $tips = [];

        if (!$weather) {
            $tips[] = "Prévoyez des vêtements confortables adaptés à la marche.";
            return $tips;
        }

        // Basé sur la condition
        if ($weather['condition'] === 'Rain' || str_contains(strtolower($weather['description']), 'pluie')) {
            $tips[] = "☔ N'oubliez pas votre parapluie ou un imperméable (pluie prévue).";
        }

        // Basé sur la température
        if ($weather['temp_max'] > 25) {
            $tips[] = "☀️ Il va faire chaud : prévoyez de la crème solaire, des lunettes de soleil et de l'eau.";
        } elseif ($weather['temp_min'] < 15) {
            $tips[] = "🧥 Il fera frais : prévoyez une veste ou un pull pour les moments plus froids.";
        } else {
            $tips[] = "👟 Des vêtements légers et des chaussures de marche seront parfaits.";
        }

        if (in_array($weather['condition'], ['Clear', 'Clouds']) && $weather['temp_max'] <= 25) {
            $tips[] = "🌤️ Temps idéal pour les photos, n'oubliez pas votre appareil !";
        }

        return $tips;
    }
}
