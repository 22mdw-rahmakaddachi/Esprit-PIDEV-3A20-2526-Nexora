<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class TravelInfoService
{
    private array $plugMap = [
        'France' => 'Type E / C (230V)',
        'Belgique' => 'Type E / C (230V)',
        'Allemagne' => 'Type F / C (230V)',
        'Italie' => 'Type L / C / F (230V)',
        'Espagne' => 'Type F / C (230V)',
        'Suisse' => 'Type J / C (230V)',
        'Royaume-Uni' => 'Type G (230V)',
        'Irlande' => 'Type G (230V)',
        'États-Unis' => 'Type A / B (120V)',
        'Canada' => 'Type A / B (120V)',
        'Mexique' => 'Type A / B (127V)',
        'Japon' => 'Type A / B (100V)',
        'Australie' => 'Type I (230V)',
        'Nouvelle-Zélande' => 'Type I (230V)',
        'Tunisie' => 'Type C / E (230V)',
        'Maroc' => 'Type C / E (230V)',
        'Algérie' => 'Type C / E (230V)',
        'Égypte' => 'Type C / F (220V)',
        'Turquie' => 'Type F / C (230V)',
        'Grèce' => 'Type F / C (230V)',
        'Portugal' => 'Type F / C (230V)',
        'Brésil' => 'Type N / C (127V / 220V)',
        'Argentine' => 'Type I / C (220V)',
        'Chine' => 'Type A / C / I (220V)',
        'Inde' => 'Type C / D / M (230V)',
        'Thaïlande' => 'Type A / B / C / O (220V)',
        'Vietnam' => 'Type A / C / G (220V)',
        'Indonésie' => 'Type C / F (230V)',
        'Afrique du Sud' => 'Type D / M / N (230V)',
        'Sénégal' => 'Type C / D / E / K (230V)',
    ];

    private array $phrasesMap = [
        'fra' => 'Bonjour, Merci, S\'il vous plaît',
        'eng' => 'Hello, Thank you, Please',
        'spa' => 'Hola, Gracias, Por favor',
        'deu' => 'Hallo, Danke, Bitte',
        'ita' => 'Buongiorno, Grazie, Per favore',
        'ara' => 'Marhaba (مرحبا), Shukran (شكرا), Min fadlak (من فضلك)',
        'jpn' => 'Konnichiwa (こんにちは), Arigatou (ありがとう)',
        'zho' => 'Ni hao (你好), Xièxie (谢谢)',
        'por' => 'Olá, Obrigado, Por favor',
        'rus' => 'Privyet (Привет), Spasibo (Спасибо)',
        'tur' => 'Merhaba, Teşekkür ederim, Lütfen',
        'ell' => 'Geia sas (Γεια σας), Efcharistó (Ευχαριστώ)',
        'tha' => 'Sawadee (สวัสดี), Khop khun (ขอบคุณ)',
    ];

    public function __construct(
        private HttpClientInterface $httpClient
    ) {}

    public function getEssentials(string $countryName): array
    {
        $countryName = trim($countryName);
        if (!$countryName) return [];

        try {
            // 1. Fetch from REST Countries
            $response = $this->httpClient->request('GET', "https://restcountries.com/v3.1/name/" . urlencode($countryName) . "?fullText=true");
            $data = $response->toArray();

            if (empty($data)) return [];

            $country = $data[0];
            $commonName = $country['name']['common'] ?? $countryName;
            
            // Currency
            $currencies = $country['currencies'] ?? [];
            $currencyStr = "Non défini";
            if (!empty($currencies)) {
                $code = array_key_first($currencies);
                $curr = $currencies[$code];
                $currencyStr = ($curr['name'] ?? $code) . " (" . ($curr['symbol'] ?? $code) . ")";
            }

            // Plug Type (Static mapping)
            $plugInfo = $this->plugMap[$commonName] 
                     ?? $this->plugMap[$country['name']['nativeName']['fra']['common'] ?? ''] 
                     ?? "Type C / E (Standard européen)";

            // Survival Phrases (Based on first language)
            $langs = $country['languages'] ?? [];
            $phrases = "Non défini";
            if (!empty($langs)) {
                $langCode = array_key_first($langs);
                $phrases = $this->phrasesMap[$langCode] ?? "Bonjour, Merci";
            }

            return [
                'currency' => $currencyStr,
                'plugType' => $plugInfo,
                'survivalPhrases' => $phrases,
                'country' => $commonName
            ];

        } catch (\Throwable $e) {
            return [];
        }
    }
}
