<?php

namespace App\Service;

use App\Repository\ActiviteRepository;
use App\Repository\DestinationRepository;
use App\Repository\ProduitParentRepository;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class AIService
{
    public function __construct(
        private HttpClientInterface      $client,
        private string                   $huggingfaceApiKey,
        private ActiviteRepository       $activiteRepo,
        private ProduitParentRepository  $produitRepo,
        private DestinationRepository    $destinationRepo,
    ) {}

    public function askAI(string $question): array
    {
        $q = mb_strtolower(trim($question));

        // ── 1. Activités ──
        if ($this->detectsActivite($q)) {
            $lieu = $this->extractLieu($q);
            $type = $this->extractType($q);

            $params = [];
            if ($lieu) $params['lieu'] = $lieu;
            if ($type) $params['type'] = $type;

            $redirect = '/activites' . (!empty($params) ? '?' . http_build_query($params) : '');

            return [
                'type'     => 'activites',
                'redirect' => $redirect,
                'message'  => $lieu
                    ? 'Redirection vers les activités à ' . ucfirst($lieu) . '...'
                    : 'Redirection vers toutes les activités...',
            ];
        }

        // ── 2. Excursions / Destinations ──
        if ($this->detectsExcursion($q)) {
            $lieu = $this->extractLieu($q);
            $redirect = '/destinations/' . (!$lieu ? '' : '?q=' . urlencode($lieu));

            return [
                'type'     => 'excursions',
                'redirect' => $redirect,
                'message'  => $lieu
                    ? 'Redirection vers les excursions à ' . ucfirst($lieu) . '...'
                    : 'Redirection vers toutes les excursions...',
            ];
        }

        // ── 3. Avis & Commentaires ──
        if ($this->detectsAvis($q)) {
            return [
                'type'     => 'avis',
                'redirect' => '/avis',
                'message'  => 'Redirection vers les avis et commentaires...',
            ];
        }

        // ── 4. Produits / boutique ──
        if ($this->detectsProduit($q)) {
            return [
                'type'     => 'produits',
                'redirect' => '/boutique',
                'message'  => 'Redirection vers la boutique...',
            ];
        }

        // ── 5. Offres / voyages ──
        if ($this->detectsOffre($q)) {
            return [
                'type'     => 'text',
                'redirect' => '/offres',
                'message'  => 'Redirection vers les offres...',
            ];
        }

        // ── 6. Réservation ──
        if (str_contains($q, 'réserv') || str_contains($q, 'reserv') || str_contains($q, 'inscri')) {
            return [
                'type'     => 'text',
                'redirect' => '/activites',
                'message'  => 'Redirection vers les activités...',
            ];
        }

        // ── 7. Réclamation ──
        if (str_contains($q, 'réclamation') || str_contains($q, 'reclamation') || str_contains($q, 'problème')) {
            return [
                'type'     => 'text',
                'redirect' => '/reclamations/new',
                'message'  => 'Redirection vers le formulaire de réclamation...',
            ];
        }

        // ── 8. Hors sujet ──
        return [
            'type'    => 'hors_sujet',
            'message' => '❌ Ton besoin n\'est pas disponible dans notre site. Nexora propose des activités, excursions, produits outdoor et offres de voyages.',
        ];
    }

    // ── Détecteurs d'intention ──

    private function detectsActivite(string $q): bool
    {
        // Mots spécifiques aux activités — éviter les faux positifs
        $keywords = ['activit', 'sport', 'culture', 'gastronomie', 'aventure', 'bien-être', 'bienetre',
                     'participer', 'rejoindre', 'sortie', 'loisir', 'randonnée', 'randonnee',
                     'natation', 'yoga', 'danse', 'theatre', 'théâtre', 'concert'];
        foreach ($keywords as $k) {
            if (str_contains($q, $k)) return true;
        }
        // "faire une activité" ou "faire du sport" mais pas "faire un sandwich"
        if (preg_match('/faire\s+(une?\s+)?(activit|sport|randonnée|yoga|danse|natation)/i', $q)) {
            return true;
        }
        return false;
    }

    private function detectsExcursion(string $q): bool
    {
        $keywords = [
            'excursion', 'excursions', 'destination', 'destinations',
            'groupe', 'rejoindre groupe', 'trip', 'sortie groupe',
            'programme', 'itinéraire', 'itineraire', 'circuit',
            'participants', 'capacité', 'capacite', 'places',
            'lancement', 'date lancement', 'panorama',
        ];
        foreach ($keywords as $k) {
            if (str_contains($q, $k)) return true;
        }
        return false;
    }

    private function detectsAvis(string $q): bool
    {
        $keywords = [
            'avis', 'commentaire', 'commentaires', 'note', 'noter',
            'évaluation', 'evaluation', 'opinion', 'retour', 'feedback',
            'laisser un avis', 'donner un avis', 'rating', 'étoile', 'etoile',
            'recommande', 'recommander', 'critique', 'témoignage', 'temoignage',
        ];
        foreach ($keywords as $k) {
            if (str_contains($q, $k)) return true;
        }
        return false;
    }

    private function detectsProduit(string $q): bool
    {
        $keywords = ['produit', 'boutique', 'acheter', 'achat', 'équipement', 'materiel', 'matériel',
                     'sac', 'tente', 'chaussure', 'vêtement', 'vetement'];
        foreach ($keywords as $k) {
            if (str_contains($q, $k)) return true;
        }
        return false;
    }

    private function detectsOffre(string $q): bool
    {
        $keywords = ['offre', 'voyage', 'destination', 'séjour', 'sejour', 'circuit', 'istanbul',
                     'egypte', 'bali', 'thaïlande', 'thailande', 'brésil', 'bresil'];
        foreach ($keywords as $k) {
            if (str_contains($q, $k)) return true;
        }
        return false;
    }

    private function extractType(string $q): ?string
    {
        $types = ['Sport', 'Culture', 'Gastronomie', 'Aventure', 'Bien-être'];
        foreach ($types as $type) {
            if (str_contains($q, mb_strtolower($type))) return $type;
        }
        return null;
    }

    private function extractLieu(string $q): ?string
    {
        // Patterns : "à X", "a X", "dans X", "au X", "en X"
        $patterns = [
            '/\b(?:à|a|dans|au|en)\s+([a-zàâäéèêëîïôùûüç\s\-]+?)(?:\s*$|\s+(?:pour|et|avec|je|il|elle|on|nous|vous|ils))/i',
            '/\b(?:à|a|dans|au|en)\s+([a-zàâäéèêëîïôùûüç\s\-]{2,30})/i',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $q, $m)) {
                $lieu = trim($m[1]);
                if (strlen($lieu) >= 2) {
                    return $lieu;
                }
            }
        }
        return null;
    }
}
