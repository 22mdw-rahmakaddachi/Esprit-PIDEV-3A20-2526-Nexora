<?php

namespace App\Controller;

use App\Entity\Commande;
use App\Entity\CommandeItem;
use App\Entity\UtilisationCodePromo;
use App\Repository\CodePromoRepository;
use App\Repository\ProduitParentRepository;
use App\Repository\ProduitVariantRepository;
use App\Service\CommandeNotificationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/panier')]
final class PanierController extends AbstractController
{
    // ─── AFFICHER LE PANIER ──────────────────────────────────────────────────

    #[Route('', name: 'app_panier')]
    public function index(Request $request): Response
    {
        $panier = $request->getSession()->get('panier', []);
        $total  = array_sum(array_map(fn($item) => $item['prix'] * $item['quantite'], $panier));

        return $this->render('panier/index.html.twig', [
            'panier'     => $panier,
            'total'      => $total,
            'codePromo'  => $request->getSession()->get('code_promo'),
            'reduction'  => $request->getSession()->get('reduction', 0),
        ]);
    }

    // ─── AJOUTER AU PANIER ───────────────────────────────────────────────────

    #[Route('/ajouter/{produitId}/{variantId}', name: 'app_panier_ajouter', methods: ['POST'])]
    public function ajouter(
        int $produitId,
        int $variantId,
        Request $request,
        ProduitParentRepository $produitRepo,
        ProduitVariantRepository $variantRepo
    ): Response {
        $produit = $produitRepo->find($produitId);
        $variant = $variantRepo->find($variantId);

        if (!$produit || !$variant) {
            $this->addFlash('error', 'Produit introuvable.');
            return $this->redirectToRoute('app_produits');
        }

        if ($variant->getQuantiteStock() <= 0) {
            $this->addFlash('error', 'Ce produit est en rupture de stock.');
            return $this->redirectToRoute('app_produit_show', ['id' => $produitId]);
        }

        $quantite = max(1, (int) $request->request->get('quantite', 1));
        $session  = $request->getSession();
        $panier   = $session->get('panier', []);

        $key = $produitId . '_' . $variantId;
        if (isset($panier[$key])) {
            $panier[$key]['quantite'] += $quantite;
        } else {
            $panier[$key] = [
                'produitId'   => $produitId,
                'variantId'   => $variantId,
                'nom'         => $produit->getNom(),
                'variantLabel'=> $variant->getOptionsLabel(),
                'sku'         => $variant->getSku(),
                'prix'        => $variant->getPrixEffectif(),
                'image'       => $produit->getImagePrincipale(),
                'quantite'    => $quantite,
            ];
        }

        $session->set('panier', $panier);
        $this->addFlash('success', 'Produit ajouté au panier.');
        return $this->redirectToRoute('app_panier');
    }

    // ─── MODIFIER QUANTITÉ ───────────────────────────────────────────────────

    #[Route('/modifier/{key}', name: 'app_panier_modifier', methods: ['POST'])]
    public function modifier(string $key, Request $request): Response
    {
        $session  = $request->getSession();
        $panier   = $session->get('panier', []);
        $quantite = (int) $request->request->get('quantite', 1);

        if (isset($panier[$key])) {
            if ($quantite <= 0) {
                unset($panier[$key]);
            } else {
                $panier[$key]['quantite'] = $quantite;
            }
        }

        $session->set('panier', $panier);
        return $this->redirectToRoute('app_panier');
    }

    // ─── SUPPRIMER UN ARTICLE ────────────────────────────────────────────────

    #[Route('/supprimer/{key}', name: 'app_panier_supprimer', methods: ['POST'])]
    public function supprimer(string $key, Request $request): Response
    {
        $session = $request->getSession();
        $panier  = $session->get('panier', []);
        unset($panier[$key]);
        $session->set('panier', $panier);
        $this->addFlash('success', 'Article retiré du panier.');
        return $this->redirectToRoute('app_panier');
    }

    // ─── APPLIQUER CODE PROMO ────────────────────────────────────────────────

    #[Route('/promo/appliquer', name: 'app_panier_promo', methods: ['POST'])]
    public function appliquerPromo(Request $request, CodePromoRepository $promoRepo): Response
    {
        $code    = strtoupper(trim($request->request->get('code', '')));
        $session = $request->getSession();
        $panier  = $session->get('panier', []);
        $total   = array_sum(array_map(fn($item) => $item['prix'] * $item['quantite'], $panier));

        $promo = $promoRepo->findValidCode($code);

        if (!$promo) {
            $this->addFlash('error', 'Code promo invalide ou expiré.');
            return $this->redirectToRoute('app_panier');
        }

        if ($promo->getMontantMinimum() && $total < $promo->getMontantMinimum()) {
            $this->addFlash('error', sprintf('Montant minimum requis : %.2f TND.', $promo->getMontantMinimum()));
            return $this->redirectToRoute('app_panier');
        }

        if ($promo->getLimiteUtilisation() && $promo->getNombreUtilisations() >= $promo->getLimiteUtilisation()) {
            $this->addFlash('error', 'Ce code promo a atteint sa limite d\'utilisation.');
            return $this->redirectToRoute('app_panier');
        }

        $reduction = $promo->getTypeReduction() === 'pourcentage'
            ? round($total * $promo->getValeurReduction() / 100, 2)
            : min($promo->getValeurReduction(), $total);

        $session->set('code_promo', ['id' => $promo->getId(), 'code' => $promo->getCode(), 'type' => $promo->getTypeReduction(), 'valeur' => $promo->getValeurReduction()]);
        $session->set('reduction', $reduction);

        $this->addFlash('success', sprintf('Code promo appliqué ! Réduction : %.2f TND', $reduction));
        return $this->redirectToRoute('app_panier');
    }

    #[Route('/promo/retirer', name: 'app_panier_promo_retirer', methods: ['POST'])]
    public function retirerPromo(Request $request): Response
    {
        $request->getSession()->remove('code_promo');
        $request->getSession()->remove('reduction');
        return $this->redirectToRoute('app_panier');
    }

    // ─── VIDER ───────────────────────────────────────────────────────────────

    #[Route('/vider', name: 'app_panier_vider', methods: ['POST'])]
    public function vider(Request $request): Response
    {
        $request->getSession()->remove('panier');
        $request->getSession()->remove('code_promo');
        $request->getSession()->remove('reduction');
        return $this->redirectToRoute('app_panier');
    }

    // ─── COMMANDER ───────────────────────────────────────────────────────────

    #[Route('/commander', name: 'app_commander', methods: ['GET', 'POST'])]
    public function commander(Request $request, EntityManagerInterface $em, CodePromoRepository $promoRepo, CommandeNotificationService $notifService): Response
    {
        $session = $request->getSession();
        $panier  = $session->get('panier', []);

        if (empty($panier)) {
            $this->addFlash('error', 'Votre panier est vide.');
            return $this->redirectToRoute('app_panier');
        }

        if ($request->isMethod('POST')) {
            $total     = array_sum(array_map(fn($item) => $item['prix'] * $item['quantite'], $panier));
            $reduction = (float) $session->get('reduction', 0);
            $totalFinal = max(0, $total - $reduction);

            $commande = new Commande();
            $commande->setClientNom($request->request->get('nom', 'Client'));
            $commande->setClientEmail($request->request->get('email', ''));
            $commande->setDateCommande(new \DateTime());
            $commande->setTotal($totalFinal);
            $commande->setStatut('en_attente');
            $em->persist($commande);
            $em->flush();

            foreach ($panier as $item) {
                $ci = new CommandeItem();
                $ci->setCommandeId($commande->getId());
                $ci->setProduitNom($item['nom'] . ' (' . $item['variantLabel'] . ')');
                $ci->setQuantite($item['quantite']);
                $ci->setPrixUnitaire($item['prix']);
                $ci->setSousTotal($item['prix'] * $item['quantite']);
                $em->persist($ci);
            }

            // Enregistrer utilisation code promo
            $codePromoData = $session->get('code_promo');
            if ($codePromoData && $reduction > 0) {
                $promo = $promoRepo->find($codePromoData['id']);
                if ($promo) {
                    $promo->setNombreUtilisations(($promo->getNombreUtilisations() ?? 0) + 1);
                    $util = new UtilisationCodePromo();
                    $util->setCodePromoId($promo->getId());
                    $util->setClientId($session->get('user_id', 0));
                    $util->setCommandeId($commande->getId());
                    $util->setMontantReduction($reduction);
                    $util->setDateUtilisation(new \DateTime());
                    $em->persist($util);
                }
            }

            $em->flush();

            // ── Notifier les partenaires concernés en temps réel ──
            $notifService->notifierPartenaires(
                $panier,
                $commande->getId(),
                $commande->getClientNom(),
                $commande->getClientEmail(),
                $totalFinal
            );

            // Vider le panier
            $session->remove('panier');
            $session->remove('code_promo');
            $session->remove('reduction');

            $this->addFlash('success', 'Commande passée avec succès ! Numéro : #' . $commande->getId());
            return $this->redirectToRoute('app_commande_confirmation', ['id' => $commande->getId()]);
        }

        $total     = array_sum(array_map(fn($item) => $item['prix'] * $item['quantite'], $panier));
        $reduction = (float) $session->get('reduction', 0);

        return $this->render('panier/checkout.html.twig', [
            'panier'    => $panier,
            'total'     => $total,
            'reduction' => $reduction,
            'totalFinal'=> max(0, $total - $reduction),
            'codePromo' => $session->get('code_promo'),
        ]);
    }

    #[Route('/confirmation/{id}', name: 'app_commande_confirmation')]
    public function confirmation(int $id, EntityManagerInterface $em): Response
    {
        $commande = $em->find(Commande::class, $id);
        return $this->render('panier/confirmation.html.twig', ['commande' => $commande]);
    }

    // ─── API BADGE PANIER ────────────────────────────────────────────────────

    #[Route('/api/count', name: 'api_panier_count')]
    public function apiCount(Request $request): JsonResponse
    {
        $panier = $request->getSession()->get('panier', []);
        $count  = array_sum(array_map(fn($item) => $item['quantite'], $panier));
        return $this->json(['count' => $count]);
    }
}
