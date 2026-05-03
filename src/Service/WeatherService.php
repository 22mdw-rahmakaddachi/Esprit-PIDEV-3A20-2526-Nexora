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
     * Récupère la prévision météo pour une ville à une date/heure précise (max 5 jours).
     * Utilise l'API forecast OpenWeatherMap (gratuite).
     */
    public function getForecast(string $city, string $datetime): ?array
    {
        if (!$this->apiKey) return null;

        try {
            // Mapping des noms tunisiens → noms reconnus par OpenWeatherMap + coordonnées GPS
            $cityMap = [
                'Ariana'       => ['q' => 'Ariana,TN'],
                'Béja'         => ['q' => 'Beja,TN'],
                'Ben Arous'    => ['q' => 'Ben Arous,TN'],
                'Bizerte'      => ['q' => 'Bizerte,TN'],
                'Gabès'        => ['q' => 'Gabes,TN'],
                'Gafsa'        => ['q' => 'Gafsa,TN'],
                'Jendouba'     => ['q' => 'Jendouba,TN'],
                'Kairouan'     => ['q' => 'Kairouan,TN'],
                'Kasserine'    => ['q' => 'Kasserine,TN'],
                'Kébili'       => ['lat' => 33.7, 'lon' => 8.97],
                'Le Kef'       => ['lat' => 36.18, 'lon' => 8.71],
                'Mahdia'       => ['q' => 'Mahdia,TN'],
                'La Manouba'   => ['lat' => 36.81, 'lon' => 10.1],
                'Médenine'     => ['lat' => 33.35, 'lon' => 10.5],
                'Monastir'     => ['q' => 'Monastir,TN'],
                'Nabeul'       => ['q' => 'Nabeul,TN'],
                'Sfax'         => ['q' => 'Sfax,TN'],
                'Sidi Bouzid'  => ['lat' => 35.03, 'lon' => 9.48],
                'Siliana'      => ['lat' => 36.08, 'lon' => 9.37],
                'Sousse'       => ['q' => 'Sousse,TN'],
                'Tataouine'    => ['lat' => 32.93, 'lon' => 10.45],
                'Tozeur'       => ['q' => 'Tozeur,TN'],
                'Tunis'        => ['q' => 'Tunis,TN'],
                'Zaghouan'     => ['lat' => 36.4, 'lon' => 10.14],
            ];

            // Construire les paramètres de requête
            $queryParams = [
                'appid' => $this->apiKey,
                'units' => 'metric',
                'lang'  => 'fr',
                'cnt'   => 40,
            ];

            if (isset($cityMap[$city])) {
                $queryParams = array_merge($queryParams, $cityMap[$city]);
            } else {
                // Fallback : essayer avec le nom brut + TN
                $queryParams['q'] = $city . ',TN';
            }

            $response = $this->httpClient->request('GET',
                'https://api.openweathermap.org/data/2.5/forecast',
                ['query' => $queryParams]
            );

            if ($response->getStatusCode() !== 200) return null;

            $data = $response->toArray();
            $list = $data['list'] ?? [];

            if (empty($list)) return null;

            // Trouver le créneau le plus proche de la date/heure choisie
            $targetTs = strtotime($datetime);
            $best     = null;
            $bestDiff = PHP_INT_MAX;

            foreach ($list as $slot) {
                $diff = abs($slot['dt'] - $targetTs);
                if ($diff < $bestDiff) {
                    $bestDiff = $diff;
                    $best     = $slot;
                }
            }

            if (!$best) return null;

            $condition = $best['weather'][0]['main'];
            $desc      = ucfirst($best['weather'][0]['description']);
            $icon      = $best['weather'][0]['icon'];
            $temp      = round($best['main']['temp']);
            $tempMin   = round($best['main']['temp_min']);
            $tempMax   = round($best['main']['temp_max']);
            $humidity  = $best['main']['humidity'];
            $wind      = round($best['wind']['speed'] * 3.6);
            $rain      = isset($best['rain']['3h']) ? round($best['rain']['3h'], 1) : 0;

            $emoji = match(true) {
                str_contains($condition, 'Rain')         => '🌧️',
                str_contains($condition, 'Drizzle')      => '🌦️',
                str_contains($condition, 'Thunderstorm') => '⛈️',
                str_contains($condition, 'Snow')         => '❄️',
                str_contains($condition, 'Clear')        => '☀️',
                str_contains($condition, 'Clouds')       => '☁️',
                str_contains($condition, 'Mist'),
                str_contains($condition, 'Fog')          => '🌫️',
                default                                  => '🌤️',
            };

            $color = match(true) {
                str_contains($condition, 'Rain'),
                str_contains($condition, 'Thunderstorm') => '#3498db',
                str_contains($condition, 'Clear')        => '#f39c12',
                str_contains($condition, 'Snow')         => '#85c1e9',
                default                                  => '#7f8c8d',
            };

            return [
                'temp'        => $temp,
                'temp_min'    => $tempMin,
                'temp_max'    => $tempMax,
                'humidity'    => $humidity,
                'wind'        => $wind,
                'rain'        => $rain,
                'condition'   => $condition,
                'description' => $desc,
                'icon'        => $icon,
                'emoji'       => $emoji,
                'color'       => $color,
                'conseil'     => $this->getAdvice($condition, $temp, $wind, $rain),
                'city'        => $data['city']['name'] ?? $city,
                'slot_time'   => date('d/m/Y H:i', $best['dt']),
            ];

        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * Génère un conseil personnalisé selon les conditions météo.
     */
    private function getAdvice(string $condition, int $temp, int $wind, float $rain): string
    {
        if (str_contains($condition, 'Thunderstorm')) {
            return '⛈️ Orage prévu — activité en extérieur déconseillée. Prévoyez un plan B.';
        }
        if (str_contains($condition, 'Rain') || $rain > 0) {
            return '🌧️ Pluie prévue — emportez un imperméable et des chaussures imperméables.';
        }
        if (str_contains($condition, 'Drizzle')) {
            return '🌦️ Bruine légère — un coupe-vent suffira.';
        }
        if ($temp >= 35) {
            return '🥵 Forte chaleur — hydratez-vous bien, évitez les heures de midi (12h-15h).';
        }
        if ($temp >= 28) {
            return '☀️ Temps chaud — prévoyez de l\'eau, de la crème solaire et un chapeau.';
        }
        if ($wind > 50) {
            return '💨 Vent très fort — attention aux activités en hauteur ou en mer.';
        }
        if ($wind > 35) {
            return '💨 Vent fort — prévoyez des vêtements adaptés.';
        }
        if ($temp >= 20 && $temp < 28) {
            return '✅ Température agréable — conditions idéales pour cette activité !';
        }
        if ($temp >= 15 && $temp < 20) {
            return '🌤️ Temps doux — une veste légère peut être utile le matin ou le soir.';
        }
        if ($temp >= 10 && $temp < 15) {
            return '🧥 Temps frais — prévoyez une veste.';
        }
        if ($temp < 10) {
            return '🧥 Froid prévu — habillez-vous chaudement en couches.';
        }

        return '✅ Conditions correctes pour cette activité.';
    }
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
