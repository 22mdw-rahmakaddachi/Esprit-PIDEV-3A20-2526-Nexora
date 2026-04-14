<?php

namespace App\Controller;

use App\Entity\Users;
use App\Repository\ActiviteRepository;
use App\Repository\ParticipationDemandeRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ActiviteController extends AbstractController
{
    private function getCurrentClientId(): int
    {
        $user = $this->getUser();
        return $user instanceof Users ? ($user->getId() ?? 0) : 0;
    }

    #[Route('/activites', name: 'app_activites')]
    public function index(ActiviteRepository $repo, Request $request): Response
    {
        $lieu = $request->query->get('lieu');
        $type = $request->query->get('type');

        return $this->render('activite/index.html.twig', [
            'activites' => $repo->findWithFilters($type, $lieu),
            'types'     => $repo->findTypesVisibles(),
            'lieux'     => ['Ariana','Béja','Ben Arous','Bizerte','Gabès','Gafsa','Jendouba','Kairouan','Kasserine','Kébili','Le Kef','Mahdia','La Manouba','Médenine','Monastir','Nabeul','Sfax','Sidi Bouzid','Siliana','Sousse','Tataouine','Tozeur','Tunis','Zaghouan'],
            'typeActif' => $type,
            'lieuActif' => $lieu,
        ]);
    }

    #[Route('/activites/{id}', name: 'app_activite_show')]
    public function show(int $id, ActiviteRepository $repo, ParticipationDemandeRepository $demandeRepo): Response
    {
        $activite = $repo->find($id);
        if (!$activite) throw $this->createNotFoundException();

        return $this->render('activite/show.html.twig', [
            'activite'         => $activite,
            'demandeExistante' => $demandeRepo->findExisting($id, $this->getCurrentClientId()),
        ]);
    }
}
