<?php

namespace App\Controller\Admin;

use App\Entity\Categorie;
use App\Entity\SousCategorie;
use App\Repository\CategorieRepository;
use App\Repository\SousCategorieRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/admin/categories')]
final class AdminCategorieController extends AbstractController
{
    // ── CATEGORIES ───────────────────────────────────────────────────────────

    #[Route('', name: 'admin_categories')]
    public function index(CategorieRepository $repo, SousCategorieRepository $scRepo): Response
    {
        $categories = $repo->findBy([], ['ordreAffichage' => 'ASC', 'nom' => 'ASC']);
        $sousCats   = $scRepo->findAll();
        $map = [];
        foreach ($sousCats as $sc) { $map[$sc->getCategorieId()][] = $sc; }
        return $this->render('admin/categories/index.html.twig', ['categories' => $categories, 'sousCatsMap' => $map]);
    }

    #[Route('/new', name: 'admin_categorie_new', methods: ['GET', 'POST'])]
    public function newCategorie(Request $request, EntityManagerInterface $em, SluggerInterface $slugger): Response
    {
        if ($request->isMethod('POST')) {
            $cat = new Categorie();
            $cat->setNom($request->request->get('nom', ''));
            $cat->setDescription($request->request->get('description'));
            $cat->setOrdreAffichage($request->request->get('ordreAffichage') ? (int)$request->request->get('ordreAffichage') : null);
            $this->handleCatImage($request, $cat, $slugger, 'categorie');
            $em->persist($cat); $em->flush();
            $this->addFlash('success', 'Catégorie créée.');
            return $this->redirectToRoute('admin_categories');
        }
        return $this->render('admin/categories/form_cat.html.twig', ['categorie' => null, 'titre' => 'Nouvelle catégorie']);
    }

    #[Route('/{id}/edit', name: 'admin_categorie_edit', methods: ['GET', 'POST'])]
    public function editCategorie(int $id, Request $request, CategorieRepository $repo, EntityManagerInterface $em, SluggerInterface $slugger): Response
    {
        $cat = $repo->find($id);
        if (!$cat) { $this->addFlash('error', 'Catégorie introuvable.'); return $this->redirectToRoute('admin_categories'); }
        if ($request->isMethod('POST')) {
            $cat->setNom($request->request->get('nom', ''));
            $cat->setDescription($request->request->get('description'));
            $cat->setOrdreAffichage($request->request->get('ordreAffichage') ? (int)$request->request->get('ordreAffichage') : null);
            if ($request->request->get('removeImage') === '1') { $this->deleteFile($cat->getImage(), 'categorie'); $cat->setImage(null); }
            $this->handleCatImage($request, $cat, $slugger, 'categorie');
            $em->flush();
            $this->addFlash('success', 'Catégorie modifiée.');
            return $this->redirectToRoute('admin_categories');
        }
        return $this->render('admin/categories/form_cat.html.twig', ['categorie' => $cat, 'titre' => 'Modifier la catégorie']);
    }

    #[Route('/{id}/delete', name: 'admin_categorie_delete', methods: ['POST'])]
    public function deleteCategorie(int $id, CategorieRepository $repo, EntityManagerInterface $em): Response
    {
        $cat = $repo->find($id);
        if ($cat) { $this->deleteFile($cat->getImage(), 'categorie'); $em->remove($cat); $em->flush(); $this->addFlash('success', 'Catégorie supprimée.'); }
        return $this->redirectToRoute('admin_categories');
    }

    // ── SOUS-CATEGORIES ───────────────────────────────────────────────────────

    #[Route('/sous/new', name: 'admin_sous_categorie_new', methods: ['GET', 'POST'])]
    public function newSousCategorie(Request $request, CategorieRepository $catRepo, EntityManagerInterface $em, SluggerInterface $slugger): Response
    {
        if ($request->isMethod('POST')) {
            $sc = new SousCategorie();
            $sc->setNom($request->request->get('nom', ''));
            $sc->setDescription($request->request->get('description'));
            $catId = (int) $request->request->get('categorieId', 0);
            if ($catId && ($cat = $catRepo->find($catId))) { $sc->setCategorie($cat); $sc->setCategorieId($catId); }
            $this->handleCatImage($request, $sc, $slugger, 'sous_categorie');
            $em->persist($sc); $em->flush();
            $this->addFlash('success', 'Sous-catégorie créée.');
            return $this->redirectToRoute('admin_categories');
        }
        return $this->render('admin/categories/form_souscat.html.twig', ['sousCategorie' => null, 'titre' => 'Nouvelle sous-catégorie', 'categories' => $catRepo->findBy([], ['nom' => 'ASC'])]);
    }

    #[Route('/sous/{id}/edit', name: 'admin_sous_categorie_edit', methods: ['GET', 'POST'])]
    public function editSousCategorie(int $id, Request $request, SousCategorieRepository $repo, CategorieRepository $catRepo, EntityManagerInterface $em, SluggerInterface $slugger): Response
    {
        $sc = $repo->find($id);
        if (!$sc) { $this->addFlash('error', 'Sous-catégorie introuvable.'); return $this->redirectToRoute('admin_categories'); }
        if ($request->isMethod('POST')) {
            $sc->setNom($request->request->get('nom', ''));
            $sc->setDescription($request->request->get('description'));
            $catId = (int) $request->request->get('categorieId', 0);
            if ($catId && ($cat = $catRepo->find($catId))) { $sc->setCategorie($cat); $sc->setCategorieId($catId); }
            if ($request->request->get('removeImage') === '1') { $this->deleteFile($sc->getImage(), 'sous_categorie'); $sc->setImage(null); }
            $this->handleCatImage($request, $sc, $slugger, 'sous_categorie');
            $em->flush();
            $this->addFlash('success', 'Sous-catégorie modifiée.');
            return $this->redirectToRoute('admin_categories');
        }
        return $this->render('admin/categories/form_souscat.html.twig', ['sousCategorie' => $sc, 'titre' => 'Modifier la sous-catégorie', 'categories' => $catRepo->findBy([], ['nom' => 'ASC'])]);
    }

    #[Route('/sous/{id}/delete', name: 'admin_sous_categorie_delete', methods: ['POST'])]
    public function deleteSousCategorie(int $id, SousCategorieRepository $repo, EntityManagerInterface $em): Response
    {
        $sc = $repo->find($id);
        if ($sc) { $this->deleteFile($sc->getImage(), 'sous_categorie'); $em->remove($sc); $em->flush(); $this->addFlash('success', 'Sous-catégorie supprimée.'); }
        return $this->redirectToRoute('admin_categories');
    }

    // ── HELPERS ───────────────────────────────────────────────────────────────

    private function handleCatImage(Request $request, object $entity, SluggerInterface $slugger, string $folder): void
    {
        $file = $request->files->get('image');
        if (!$file) return;
        $dir = $this->getParameter('kernel.project_dir') . '/public/uploads/' . $folder;
        if (!is_dir($dir)) mkdir($dir, 0777, true);
        $filename = $slugger->slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '-' . uniqid() . '.' . $file->guessExtension();
        $file->move($dir, $filename);
        $entity->setImage($filename);
    }

    private function deleteFile(?string $filename, string $folder): void
    {
        if (!$filename) return;
        $path = $this->getParameter('kernel.project_dir') . '/public/uploads/' . $folder . '/' . $filename;
        if (file_exists($path)) unlink($path);
    }
}
