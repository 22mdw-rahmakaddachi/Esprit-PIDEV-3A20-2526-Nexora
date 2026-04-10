<?php

namespace App\Controller\Admin;

use App\Entity\AttributVariation;
use App\Entity\OptionVariation;
use App\Repository\AttributVariationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/attributs')]
final class AdminAttributController extends AbstractController
{
    // ── LISTE ────────────────────────────────────────────────────────────────

    #[Route('', name: 'admin_attributs')]
    public function index(AttributVariationRepository $repo): Response
    {
        return $this->render('admin/attributs/index.html.twig', [
            'attributs' => $repo->findAll(),
        ]);
    }

    // ── NOUVEAU ATTRIBUT ─────────────────────────────────────────────────────

    #[Route('/new', name: 'admin_attribut_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        if ($request->isMethod('POST')) {
            $attribut = new AttributVariation();
            $attribut->setNom($request->request->get('nom', ''));
            $attribut->setTypeAffichage($request->request->get('typeAffichage', 'select'));

            // Options soumises en même temps
            $optionsValeurs = $request->request->all('option_valeur');
            $optionsHex     = $request->request->all('option_hex');

            foreach ($optionsValeurs as $i => $valeur) {
                if (empty(trim($valeur))) continue;
                $opt = new OptionVariation();
                $opt->setValeur(trim($valeur));
                $opt->setCodeHexadecimal(!empty($optionsHex[$i]) ? $optionsHex[$i] : null);
                $opt->setOrdreAffichage($i);
                $opt->setAttribut($attribut);
                $attribut->addOption($opt);
                $em->persist($opt);
            }

            $em->persist($attribut);
            $em->flush();

            $this->addFlash('success', 'Attribut créé avec succès.');
            return $this->redirectToRoute('admin_attributs');
        }

        return $this->render('admin/attributs/form.html.twig', [
            'attribut' => null,
            'titre'    => 'Nouvel attribut',
        ]);
    }

    // ── MODIFIER ATTRIBUT ────────────────────────────────────────────────────

    #[Route('/{id}/edit', name: 'admin_attribut_edit', methods: ['GET', 'POST'])]
    public function edit(int $id, Request $request, AttributVariationRepository $repo, EntityManagerInterface $em): Response
    {
        $attribut = $repo->find($id);
        if (!$attribut) {
            $this->addFlash('error', 'Attribut introuvable.');
            return $this->redirectToRoute('admin_attributs');
        }

        if ($request->isMethod('POST')) {
            $attribut->setNom($request->request->get('nom', ''));
            $attribut->setTypeAffichage($request->request->get('typeAffichage', 'select'));

            // Supprimer les anciennes options
            foreach ($attribut->getOptions() as $opt) {
                $em->remove($opt);
            }
            $em->flush();

            // Recréer les options
            $optionsValeurs = $request->request->all('option_valeur');
            $optionsHex     = $request->request->all('option_hex');

            foreach ($optionsValeurs as $i => $valeur) {
                if (empty(trim($valeur))) continue;
                $opt = new OptionVariation();
                $opt->setValeur(trim($valeur));
                $opt->setCodeHexadecimal(!empty($optionsHex[$i]) ? $optionsHex[$i] : null);
                $opt->setOrdreAffichage($i);
                $opt->setAttribut($attribut);
                $attribut->addOption($opt);
                $em->persist($opt);
            }

            $em->flush();
            $this->addFlash('success', 'Attribut modifié.');
            return $this->redirectToRoute('admin_attributs');
        }

        return $this->render('admin/attributs/form.html.twig', [
            'attribut' => $attribut,
            'titre'    => 'Modifier l\'attribut',
        ]);
    }

    // ── SUPPRIMER ATTRIBUT ───────────────────────────────────────────────────

    #[Route('/{id}/delete', name: 'admin_attribut_delete', methods: ['POST'])]
    public function delete(int $id, AttributVariationRepository $repo, EntityManagerInterface $em): Response
    {
        $attribut = $repo->find($id);
        if ($attribut) {
            $em->remove($attribut);
            $em->flush();
            $this->addFlash('success', 'Attribut supprimé.');
        }
        return $this->redirectToRoute('admin_attributs');
    }
}
