<?php
$target = __DIR__ . '/src/Controller/Admin/AdminActiviteController.php';
$code = '<?php

namespace App\Controller\Admin;

use App\Entity\Activite;
use App\Entity\Partenaire;
use App\Repository\ActiviteRepository;
use App\Repository\CodePromoRepository;
use App\Repository\NotificationRepository;
use App\Repository\PartenaireRepository;
use App\Repository\ParticipationDemandeRepository;
use App\Repository\ProduitParentRepository;
use App\Repository\UsersRepository;
use App\Service\NotificationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route(\'/admin\')]
final class AdminActiviteController extends AbstractController
{
    private function getPartenaire(PartenaireRepository $repo): ?Partenaire
    {
        $user = $this->getUser();
        if (!$user) return null;
        return $repo->findOneBy([\'user\' => $user]);
    }

    private function getPartenaireId(PartenaireRepository $repo): int
    {
        return $this->getPartenaire($repo)?->getId() ?? 0;
    }

    #[Route(\'/dashboard\', name: \'admin_dashboard\')]
    public function dashboard(
        Request $request,
        ActiviteRepository $activiteRepo,
        ParticipationDemandeRepository $demandeRepo,
        UsersRepository $usersRepo,
        PartenaireRepository $partenaireRepo,
        ProduitParentRepository $produitRepo,
        CodePromoRepository $promoRepo
    ): Response {
        // Données activités
        if ($this->isGranted(\'ROLE_ADMIN\')) {
            $activites = $activiteRepo->findBy([], [\'dateCreation\' => \'DESC\']);
        } else {
            $partenaireId = $this->getPartenaireId($partenaireRepo);
            $activites = $partenaireId ? $activiteRepo->findByPartenaire($partenaireId) : [];
        }

        $totalPlaces       = array_sum(array_map(fn($a) => $a->getNombrePlaces(), $activites));
        $placesDisponibles = array_sum(array_map(fn($a) => $a->getPlacesDisponibles(), $activites));
        $demandes          = [];
        $demandesEnAttente = 0;
        foreach ($activites as $a) {
            $d = $demandeRepo->findByActivite($a->getId());
            $demandes = array_merge($demandes, $d);
            foreach ($d as $dem) {
                if ($dem->getStatut() === \'EN_ATTENTE\') $demandesEnAttente++;
            }
        }

        // Données commerce (produits + promos)
        $userId     = $request->getSession()->get(\'user_id\');
        $partenaire = $userId ? $partenaireRepo->findOneBy([\'user\' => $userId]) : null;
        if (!$partenaire) $partenaire = $partenaireRepo->findOneBy([], [\'id\' => \'ASC\']);
        $produits = $partenaire ? $produitRepo->findByPartenaire($partenaire->getId()) : [];
        $promos   = $partenaire ? $promoRepo->findByPartenaire($partenaire->getId()) : [];

        return $this->render(\'admin/dashboard.html.twig\', [
            \'totalActivites\'    => count($activites),
            \'totalReservations\' => $totalPlaces - $placesDisponibles,
            \'placesDisponibles\' => $placesDisponibles,
            \'demandesEnAttente\' => $demandesEnAttente,
            \'activites\'         => array_slice($activites, 0, 5),
            \'demandes\'          => array_slice($demandes, 0, 5),
            \'users\'             => $this->isGranted(\'ROLE_ADMIN\') ? $usersRepo->findBy([], [\'id\' => \'DESC\'], 5) : [],
            \'partenaire\'        => $partenaire,
            \'produits\'          => $produits,
            \'promos\'            => $promos,
            \'totalProduits\'     => count($produits),
            \'totalPromos\'       => count($promos),
        ]);
    }

    #[Route(\'/activites\', name: \'admin_activites\')]
    public function index(ActiviteRepository $repo, PartenaireRepository $partenaireRepo): Response
    {
        if ($this->isGranted(\'ROLE_ADMIN\')) {
            $activites = $repo->findAll();
        } else {
            $partenaireId = $this->getPartenaireId($partenaireRepo);
            $activites = $partenaireId ? $repo->findByPartenaire($partenaireId) : [];
        }
        return $this->render(\'admin/activite/index.html.twig\', [\'activites\' => $activites]);
    }

    #[Route(\'/activites/new\', name: \'admin_activite_new\')]
    public function new(Request $request, EntityManagerInterface $em, SluggerInterface $slugger, PartenaireRepository $partenaireRepo): Response
    {
        $errors = [];
        if ($request->isMethod(\'POST\')) {
            $data   = $request->request->all();
            $errors = $this->validateData($data);
            if (empty($errors)) {
                $activite = new Activite();
                $this->fillActivite($activite, $data, $request, $slugger);
                $partenaire = $this->getPartenaire($partenaireRepo);
                $activite->setPartenaire($partenaire);
                $activite->setPlacesDisponibles($activite->getNombrePlaces());
                $activite->setDateCreation(new \DateTime());
                $activite->setCreatedAt(new \DateTime());
                $em->persist($activite);
                $em->flush();
                $this->addFlash(\'success\', \'✅ Activité "\' . $activite->getNom() . \'" créée.\');
                return $this->redirectToRoute(\'admin_activites\');
            }
        }
        return $this->render(\'admin/activite/form.html.twig\', [
            \'titre\' => \'Nouvelle activité\', \'activite\' => null,
            \'errors\' => $errors, \'old\' => $request->request->all(),
        ]);
    }

    #[Route(\'/activites/{id}/edit\', name: \'admin_activite_edit\')]
    public function edit(int $id, Request $request, ActiviteRepository $repo, EntityManagerInterface $em, SluggerInterface $slugger, PartenaireRepository $partenaireRepo): Response
    {
        $activite = $repo->find($id);
        if (!$activite || $activite->getPartenaire()?->getId() !== $this->getPartenaireId($partenaireRepo)) {
            throw $this->createNotFoundException();
        }
        $errors = [];
        if ($request->isMethod(\'POST\')) {
            $data   = $request->request->all();
            $errors = $this->validateData($data);
            if (empty($errors)) {
                $this->fillActivite($activite, $data, $request, $slugger);
                $reservees = $activite->getNombrePlaces() - $activite->getPlacesDisponibles();
                $activite->setPlacesDisponibles(max(0, $activite->getNombrePlaces() - $reservees));
                $em->flush();
                $this->addFlash(\'success\', \'✅ Activité mise à jour.\');
                return $this->redirectToRoute(\'admin_activites\');
            }
        }
        return $this->render(\'admin/activite/form.html.twig\', [
            \'titre\' => \'Modifier : \' . $activite->getNom(),
            \'activite\' => $activite, \'errors\' => $errors, \'old\' => [],
        ]);
    }

    #[Route(\'/activites/{id}/delete\', name: \'admin_activite_delete\', methods: [\'POST\'])]
    public function delete(int $id, ActiviteRepository $repo, EntityManagerInterface $em, PartenaireRepository $partenaireRepo, ParticipationDemandeRepository $demandeRepo): Response
    {
        $activite = $repo->find($id);
        if ($activite && ($this->isGranted(\'ROLE_ADMIN\') || $activite->getPartenaire()?->getId() === $this->getPartenaireId($partenaireRepo))) {
            foreach ($demandeRepo->findByActivite($id) as $demande) { $em->remove($demande); }
            $em->flush();
            $em->remove($activite);
            $em->flush();
            $this->addFlash(\'success\', \'🗑️ Activité supprimée.\');
        }
        return $this->redirectToRoute(\'admin_activites\');
    }

    #[Route(\'/activites/{id}/show\', name: \'admin_activite_show\')]
    public function show(int $id, ActiviteRepository $repo, ParticipationDemandeRepository $demandeRepo): Response
    {
        $activite = $repo->find($id);
        if (!$activite) throw $this->createNotFoundException();
        return $this->render(\'admin/activite/show.html.twig\', [
            \'activite\' => $activite, \'demandes\' => $demandeRepo->findByActivite($id),
        ]);
    }

    #[Route(\'/demandes\', name: \'admin_demandes\')]
    public function demandes(ActiviteRepository $activiteRepo, ParticipationDemandeRepository $demandeRepo, NotificationRepository $notifRepo, PartenaireRepository $partenaireRepo): Response
    {
        if ($this->isGranted(\'ROLE_ADMIN\')) {
            $activites = $activiteRepo->findAll();
            $partenaireUserId = 0;
        } else {
            $partenaire = $this->getPartenaire($partenaireRepo);
            $activites = $partenaire ? $activiteRepo->findByPartenaire($partenaire->getId()) : [];
            $partenaireUserId = $partenaire?->getUser()?->getId() ?? 0;
        }
        $demandes = [];
        foreach ($activites as $a) {
            $demandes = array_merge($demandes, $demandeRepo->findByActivite($a->getId()));
        }
        return $this->render(\'admin/demandes.html.twig\', [
            \'demandes\'      => $demandes,
            \'notifications\' => $partenaireUserId ? $notifRepo->findByUser($partenaireUserId, \'PARTENAIRE\') : [],
        ]);
    }

    #[Route(\'/demandes/{id}/accepter\', name: \'admin_demande_accepter\', methods: [\'POST\'])]
    public function accepter(int $id, ParticipationDemandeRepository $repo, EntityManagerInterface $em, NotificationService $notif): Response
    {
        $demande = $repo->find($id);
        if (!$demande) throw $this->createNotFoundException();
        $demande->setStatut(\App\Entity\ParticipationDemande::STATUT_ACCEPTEE);
        $em->flush();
        $notif->notifyAcceptation($demande);
        $this->addFlash(\'success\', \'✅ Demande acceptée.\');
        return $this->redirectToRoute(\'admin_demandes\');
    }

    #[Route(\'/demandes/{id}/refuser\', name: \'admin_demande_refuser\', methods: [\'POST\'])]
    public function refuser(int $id, ParticipationDemandeRepository $repo, EntityManagerInterface $em, NotificationService $notif): Response
    {
        $demande = $repo->find($id);
        if (!$demande) throw $this->createNotFoundException();
        $demande->setStatut(\App\Entity\ParticipationDemande::STATUT_REFUSEE);
        $em->flush();
        $notif->notifyRefus($demande);
        $this->addFlash(\'info\', \'❌ Demande refusée.\');
        return $this->redirectToRoute(\'admin_demandes\');
    }

    #[Route(\'/notif/lire/{id}\', name: \'admin_notif_lire\')]
    public function lireNotif(int $id, NotificationRepository $repo, EntityManagerInterface $em): Response
    {
        $notif = $repo->find($id);
        if ($notif) { $notif->setLue(true); $em->flush(); }
        return $this->redirectToRoute(\'admin_demandes\');
    }

    private function validateData(array $data): array
    {
        $errors = [];
        if (empty(trim($data[\'nom\'] ?? \'\')))         $errors[\'nom\']         = \'Le nom est obligatoire.\';
        if (empty($data[\'type\'] ?? \'\'))              $errors[\'type\']        = \'Le type est obligatoire.\';
        if (empty($data[\'genreCible\'] ?? \'\'))        $errors[\'genreCible\']  = \'Le genre cible est obligatoire.\';
        if (empty($data[\'lieu\'] ?? \'\'))              $errors[\'lieu\']        = \'Le gouvernorat est obligatoire.\';
        if (empty(trim($data[\'description\'] ?? \'\'))) $errors[\'description\'] = \'La description est obligatoire.\';
        if (!isset($data[\'prix\']) || (float)$data[\'prix\'] <= 0)
            $errors[\'prix\'] = \'Le prix doit être supérieur à 0.\';
        if (!isset($data[\'nombrePlaces\']) || (int)$data[\'nombrePlaces\'] <= 0)
            $errors[\'nombrePlaces\'] = \'Le nombre de places doit être supérieur à 0.\';
        $avecDate = !empty($data[\'avecDate\']);
        if ($avecDate) {
            if (empty($data[\'dateActivite\'])) {
                $errors[\'dateActivite\'] = \'La date est obligatoire.\';
            } elseif (new \DateTime($data[\'dateActivite\']) <= new \DateTime()) {
                $errors[\'dateActivite\'] = \'La date doit être dans le futur.\';
            }
        }
        return $errors;
    }

    private function fillActivite(Activite $activite, array $data, Request $request, SluggerInterface $slugger): void
    {
        $activite->setNom(trim($data[\'nom\'] ?? \'\'));
        $activite->setType($data[\'type\'] ?? \'\');
        $activite->setGenreCible($data[\'genreCible\'] ?? \'MIXTE\');
        $activite->setLieu($data[\'lieu\'] ?? \'\');
        $activite->setDescription(trim($data[\'description\'] ?? \'\'));
        $activite->setPrix((float)($data[\'prix\'] ?? 0));
        $activite->setNombrePlaces((int)($data[\'nombrePlaces\'] ?? 0));
        $avecDate = !empty($data[\'avecDate\']);
        $activite->setAvecDate($avecDate);
        if ($avecDate && !empty($data[\'dateActivite\'])) {
            $activite->setDateActivite(new \DateTime($data[\'dateActivite\']));
        } else {
            $activite->setDateActivite(new \DateTime(\'2099-01-01\'));
        }
        $imageFile = $request->files->get(\'imageFile\');
        if ($imageFile) {
            $safe        = $slugger->slug(pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME));
            $newFilename = $safe . \'-\' . uniqid() . \'.\' . $imageFile->guessExtension();
            $uploadDir   = $this->getParameter(\'kernel.project_dir\') . \'/public/uploads/activites\';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
            $imageFile->move($uploadDir, $newFilename);
            $activite->setImages($newFilename);
        }
    }
}
';
file_put_contents($target, $code);
echo "OK: " . strlen($code) . " bytes, hex: " . bin2hex(substr($code, 0, 5)) . "\n";
