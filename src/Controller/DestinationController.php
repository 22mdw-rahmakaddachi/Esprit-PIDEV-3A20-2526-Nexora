<?php

namespace App\Controller;

use App\Entity\Destination;
use App\Form\DestinationType;
use App\Repository\DestinationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/admin/destinations')]
class DestinationController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $em,
        private DestinationRepository  $repo,
        private SluggerInterface       $slugger,
    ) {}

    // ========================= LIST =========================
    #[Route('', name: 'admin_destination_index', methods: ['GET'])]
    public function index(Request $request): Response
    {
        $search = $request->query->get('search', '');

        $destinations = $search
            ? $this->repo->searchByLocalisation($search)
            : $this->repo->findAllOrdered();

        return $this->render('destination/admin/index.html.twig', [
            'destinations' => $destinations,
            'search'       => $search,
        ]);
    }

    // ========================= NEW =========================
    #[Route('/new', name: 'admin_destination_new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        $destination = new Destination();
        $form = $this->createForm(DestinationType::class, $destination);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $imageUrl = $this->handleImageUpload($form, $request);
            if ($imageUrl) {
                $destination->setImages($imageUrl);
            }

            $this->em->persist($destination);
            $this->em->flush();

            $this->addFlash('success', 'Destination ajoutée avec succès !');
            return $this->redirectToRoute('admin_destination_index');
        }

        return $this->render('destination/admin/form.html.twig', [
            'form'        => $form->createView(),
            'destination' => $destination,
            'isEdit'      => false,
        ]);
    }

    // ========================= EDIT =========================
    #[Route('/{id}/edit', name: 'admin_destination_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Destination $destination): Response
    {
        $form = $this->createForm(DestinationType::class, $destination);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $imageUrl = $this->handleImageUpload($form, $request);
            if ($imageUrl) {
                $destination->setImages($imageUrl);
            }

            $this->em->flush();

            $this->addFlash('success', 'Destination modifiée avec succès !');
            return $this->redirectToRoute('admin_destination_index');
        }

        return $this->render('destination/admin/form.html.twig', [
            'form'        => $form->createView(),
            'destination' => $destination,
            'isEdit'      => true,
        ]);
    }

    // ========================= DELETE =========================
    #[Route('/{id}/delete', name: 'admin_destination_delete', methods: ['POST'])]
    public function delete(Request $request, Destination $destination): Response
    {
        if ($this->isCsrfTokenValid('delete' . $destination->getId(), $request->request->get('_token'))) {
            // Supprimer le fichier image local si existant
            $images = $destination->getImagesList();
            foreach ($images as $img) {
                if (!str_starts_with($img, 'http')) {
                    $path = $this->getParameter('kernel.project_dir') . '/public/uploads/destinations/' . $img;
                    if (file_exists($path)) {
                        unlink($path);
                    }
                }
            }

            $this->em->remove($destination);
            $this->em->flush();
            $this->addFlash('success', 'Destination supprimée.');
        }

        return $this->redirectToRoute('admin_destination_index');
    }

    // ========================= SHOW (AJAX) =========================
    #[Route('/{id}', name: 'admin_destination_show', methods: ['GET'])]
    public function show(Destination $destination): Response
    {
        return $this->render('destination/admin/show.html.twig', [
            'destination' => $destination,
        ]);
    }

    // ========================= AUTOCOMPLETE LOCALISATION =========================
    #[Route('/api/autocomplete-location', name: 'destination_autocomplete_location', methods: ['GET'])]
    public function autocompleteLocation(Request $request): JsonResponse
    {
        $query = $request->query->get('q', '');
        if (strlen($query) < 3) {
            return $this->json([]);
        }

        $url = 'https://nominatim.openstreetmap.org/search?q=' . urlencode($query) . '&format=json&limit=5';

        $context = stream_context_create([
            'http' => [
                'header' => "User-Agent: SymfonyApp/1.0\r\n",
                'timeout' => 3,
            ],
        ]);

        $response = @file_get_contents($url, false, $context);
        if (!$response) {
            return $this->json([]);
        }

        $data = json_decode($response, true);
        $results = array_map(fn($item) => $item['display_name'], $data ?? []);

        return $this->json($results);
    }

    // ========================= HELPER IMAGE =========================
    private function handleImageUpload($form, Request $request): ?string
    {
        $imageFile = $form->get('imageFile')->getData();
        if (!$imageFile) return null;

        $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
        $safeFilename = $this->slugger->slug($originalFilename);
        $newFilename = $safeFilename . '-' . uniqid() . '.' . $imageFile->guessExtension();

        $uploadDir = $this->getParameter('kernel.project_dir') . '/public/uploads/destinations/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0775, true);
        }

        $imageFile->move($uploadDir, $newFilename);

        return '/uploads/destinations/' . $newFilename;
    }
}
