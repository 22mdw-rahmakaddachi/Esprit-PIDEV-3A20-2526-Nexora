<?php

namespace App\Controller\Admin;

use App\Entity\CodePromo;
use App\Entity\ProduitParent;
use App\Entity\ProduitVariant;
use App\Entity\VariantOption;
use App\Form\CodePromoType;
use App\Form\ProduitParentType;
use App\Repository\AttributVariationRepository;
use App\Repository\CodePromoRepository;
use App\Repository\OptionVariationRepository;
use App\Repository\PartenaireRepository;
use App\Repository\ProduitParentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/admin')]
final class AdminProduitController extends AbstractController
{
    private function getPartenaire(Request $request, PartenaireRepository $repo): ?\App\Entity\Partenaire
    {
        $userId = $request->getSession()->get('user_id');
        if ($userId) {
            $p = $repo->findOneBy(['user' => $userId]);
            if ($p) return $p;
        }
        return $repo->findOneBy([], ['id' => 'ASC']);
    }

    #[Route('/produits', name: 'admin_produits')]
    public function produits(Request $request, PartenaireRepository $pr, ProduitParentRepository $repo): Response
    {
        $partenaire = $this->getPartenaire($request, $pr);
        return $this->render('admin/produits/index.html.twig', [
            'produits'   => $partenaire ? $repo->findByPartenaire($partenaire->getId()) : [],
            'partenaire' => $partenaire,
        ]);
    }

    #[Route('/produits/new', name: 'admin_produit_new', methods: ['GET', 'POST'])]
    public function newProduit(Request $request, PartenaireRepository $pr, AttributVariationRepository $attrRepo, EntityManagerInterface $em, SluggerInterface $slugger): Response
    {
        $partenaire = $this->getPartenaire($request, $pr);
        $produit = new ProduitParent();
        $form = $this->createForm(ProduitParentType::class, $produit);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // Setter partenaireId APRES validation pour ne pas etre ecrase
            if ($partenaire) {
                $produit->setPartenaireId($partenaire->getId());
                $produit->setPartenaire($partenaire);
            }
            $imageFile = $form->get('imagePrincipale')->getData();
            if ($imageFile) {
                $filename = $slugger->slug(pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME)) . '-' . uniqid() . '.' . $imageFile->guessExtension();
                $imageFile->move($this->getParameter('kernel.project_dir') . '/public/uploads/produits', $filename);
                $produit->setImagePrincipale($filename);
            }
            $em->persist($produit); $em->flush();
            $this->saveVariants($request, $produit, $attrRepo, $em);
            $this->addFlash('success', 'Produit ajouté avec succès.');
            return $this->redirectToRoute('admin_produits');
        }
        return $this->render('admin/produits/form.html.twig', [
            'form'       => $form->createView(),
            'produit'    => $produit,
            'titre'      => 'Nouveau produit',
            'attributs'  => $attrRepo->findAll(),
            'partenaire' => $partenaire,
        ]);
    }

    #[Route('/produits/{id}/edit', name: 'admin_produit_edit', methods: ['GET', 'POST'])]
    public function editProduit(int $id, Request $request, PartenaireRepository $pr, ProduitParentRepository $produitRepo, AttributVariationRepository $attrRepo, EntityManagerInterface $em, SluggerInterface $slugger): Response
    {
        $partenaire = $this->getPartenaire($request, $pr);
        $produit = $produitRepo->find($id);
        if (!$produit) { $this->addFlash('error', 'Produit introuvable.'); return $this->redirectToRoute('admin_produits'); }
        $form = $this->createForm(ProduitParentType::class, $produit);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            if ($request->request->get('removeImage') === '1') {
                $this->deleteImageFile($produit->getImagePrincipale()); $produit->setImagePrincipale(null);
            }
            $imageFile = $form->get('imagePrincipale')->getData();
            if ($imageFile) {
                $filename = $slugger->slug(pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME)) . '-' . uniqid() . '.' . $imageFile->guessExtension();
                $imageFile->move($this->getParameter('kernel.project_dir') . '/public/uploads/produits', $filename);
                $produit->setImagePrincipale($filename);
            }
            foreach ($produit->getVariants() as $v) { $em->remove($v); }
            $em->flush();
            $this->saveVariants($request, $produit, $attrRepo, $em);
            $this->addFlash('success', 'Produit modifié avec succès.');
            return $this->redirectToRoute('admin_produits');
        }
        return $this->render('admin/produits/form.html.twig', [
            'form'       => $form->createView(),
            'produit'    => $produit,
            'titre'      => 'Modifier le produit',
            'attributs'  => $attrRepo->findAll(),
            'partenaire' => $partenaire,
        ]);
    }

    #[Route('/produits/{id}/delete', name: 'admin_produit_delete', methods: ['POST'])]
    public function deleteProduit(int $id, ProduitParentRepository $repo, EntityManagerInterface $em): Response
    {
        $produit = $repo->find($id);
        if ($produit) { $this->deleteImageFile($produit->getImagePrincipale()); $em->remove($produit); $em->flush(); $this->addFlash('success', 'Produit supprimé.'); }
        return $this->redirectToRoute('admin_produits');
    }

    #[Route('/api/attribut/{id}/options', name: 'admin_api_attribut_options')]
    public function apiAttributOptions(int $id, OptionVariationRepository $repo): JsonResponse
    {
        $options = $repo->findBy(['attributId' => $id], ['ordreAffichage' => 'ASC']);
        return $this->json(array_map(fn($o) => ['id' => $o->getId(), 'valeur' => $o->getValeur(), 'hex' => $o->getCodeHexadecimal()], $options));
    }

    #[Route('/promos', name: 'admin_promos')]
    public function promos(Request $request, PartenaireRepository $pr, CodePromoRepository $repo): Response
    {
        $partenaire = $this->getPartenaire($request, $pr);
        return $this->render('admin/promos/index.html.twig', [
            'promos'     => $partenaire ? $repo->findByPartenaire($partenaire->getId()) : [],
            'partenaire' => $partenaire,
        ]);
    }

    #[Route('/promos/new', name: 'admin_promo_new', methods: ['GET', 'POST'])]
    public function newPromo(Request $request, PartenaireRepository $pr, EntityManagerInterface $em): Response
    {
        $partenaire = $this->getPartenaire($request, $pr);
        $promo = new CodePromo();
        $form = $this->createForm(CodePromoType::class, $promo);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            if ($partenaire) { $promo->setPartenaireId($partenaire->getId()); }
            $promo->setNombreUtilisations(0); $promo->setActif(1); $promo->setDateCreation(new \DateTime());
            $em->persist($promo); $em->flush();
            $this->addFlash('success', 'Code promo créé.');
            return $this->redirectToRoute('admin_promos');
        }
        return $this->render('admin/promos/form.html.twig', ['form' => $form->createView(), 'promo' => $promo, 'titre' => 'Nouveau code promo', 'partenaire' => $partenaire]);
    }

    #[Route('/promos/{id}/edit', name: 'admin_promo_edit', methods: ['GET', 'POST'])]
    public function editPromo(int $id, Request $request, PartenaireRepository $pr, CodePromoRepository $repo, EntityManagerInterface $em): Response
    {
        $partenaire = $this->getPartenaire($request, $pr);
        $promo = $repo->find($id);
        if (!$promo) return $this->redirectToRoute('admin_promos');
        $form = $this->createForm(CodePromoType::class, $promo);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush(); $this->addFlash('success', 'Code promo modifié.');
            return $this->redirectToRoute('admin_promos');
        }
        return $this->render('admin/promos/form.html.twig', ['form' => $form->createView(), 'promo' => $promo, 'titre' => 'Modifier le code promo', 'partenaire' => $partenaire]);
    }

    #[Route('/promos/{id}/delete', name: 'admin_promo_delete', methods: ['POST'])]
    public function deletePromo(int $id, CodePromoRepository $repo, EntityManagerInterface $em): Response
    {
        $promo = $repo->find($id);
        if ($promo) { $em->remove($promo); $em->flush(); $this->addFlash('success', 'Code promo supprimé.'); }
        return $this->redirectToRoute('admin_promos');
    }

    private function deleteImageFile(?string $filename): void
    {
        if (!$filename) return;
        $path = $this->getParameter('kernel.project_dir') . '/public/uploads/produits/' . $filename;
        if (file_exists($path)) unlink($path);
    }

    private function saveVariants(Request $request, ProduitParent $produit, AttributVariationRepository $attrRepo, EntityManagerInterface $em): void
    {
        $skus = $request->request->all('variant_sku');
        $prixV = $request->request->all('variant_prix_vente');
        $prixP = $request->request->all('variant_prix_promo');
        $stocks = $request->request->all('variant_stock');
        $attribs = $request->request->all('variant_attribut');
        if (!is_array($skus)) return;
        foreach ($skus as $i => $sku) {
            if (empty(trim((string) $sku))) continue;
            $variant = new ProduitVariant();
            $variant->setSku(trim((string) $sku));
            $variant->setPrixVente((float) ($prixV[$i] ?? 0));
            $variant->setPrixPromo(!empty($prixP[$i]) ? (float) $prixP[$i] : null);
            $variant->setQuantiteStock((int) ($stocks[$i] ?? 0));
            $variant->setProduitParent($produit);
            $variant->setProduitParentId($produit->getId() ?? 0);
            if (isset($attribs[$i]) && is_array($attribs[$i])) {
                foreach ($attribs[$i] as $attrId => $optId) {
                    if (!$optId) continue;
                    $attr = $attrRepo->find($attrId);
                    $option = $em->find(\App\Entity\OptionVariation::class, $optId);
                    if ($attr && $option) {
                        $vo = new VariantOption();
                        $vo->setAttribut($attr); $vo->setOptionVariation($option); $vo->setVariant($variant);
                        $em->persist($vo); $variant->addOption($vo);
                    }
                }
            }
            $em->persist($variant);
        }
        $em->flush();
    }
}
