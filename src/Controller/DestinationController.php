<?php

namespace App\Controller;

use App\Entity\Destination;
use App\Entity\DestinationImage;
use App\Form\DestinationType;
use App\Repository\DestinationRepository;
use App\Service\GoogleDriveService;
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
        private GoogleDriveService     $driveService,
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
        $destination->setStatut('Disponible'); // Par défaut, la destination est disponible
        $form = $this->createForm(DestinationType::class, $destination);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->handleMultipleImageUploads($form, $destination);

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
            $this->handleMultipleImageUploads($form, $destination);

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
            // Supprimer les images de Google Drive
            foreach ($destination->getDestinationImages() as $destImage) {
                $this->deleteImageFile($destImage->getChemin());
            }
            // Fallback ancien champ texte
            foreach ($destination->getImagesList() as $img) {
                $this->deleteImageFile($img);
            }

            $this->em->remove($destination);
            $this->em->flush();
            $this->addFlash('success', 'Destination supprimée.');
        }

        return $this->redirectToRoute('admin_destination_index');
    }

    // ========================= DELETE IMAGE (AJAX) =========================
    #[Route('/image/{id}/delete', name: 'admin_destination_image_delete', methods: ['POST'])]
    public function deleteImage(Request $request, DestinationImage $image): JsonResponse
    {
        if (!$this->isCsrfTokenValid('delete-image-' . $image->getId(), $request->request->get('_token'))) {
            return $this->json(['error' => 'Token CSRF invalide'], 403);
        }

        $destination = $image->getDestination();

        // Supprimer de Google Drive (ou local selon le chemin)
        $this->deleteImageFile($image->getChemin());

        $this->em->remove($image);
        $this->em->flush();

        return $this->json([
            'success'   => true,
            'remaining' => $destination ? $destination->getImagesCount() : 0,
        ]);
    }

    // ========================= SHOW =========================
    #[Route('/{id}', name: 'admin_destination_show', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function show(Destination $destination): Response
    {
        return $this->render('destination/admin/show.html.twig', [
            'destination' => $destination,
        ]);
    }

    // ========================= AUTOCOMPLETE LOCALISATION =========================
    #[Route('/api/autocomplete-location', name: 'destination_autocomplete_location', methods: ['GET'])]
    public function autocompleteLocation(Request $request, \Symfony\Contracts\HttpClient\HttpClientInterface $httpClient): JsonResponse
    {
        $query = $request->query->get('q', '');
        if (strlen($query) < 3) {
            return $this->json([]);
        }

        try {
            $response = $httpClient->request('GET', 'https://nominatim.openstreetmap.org/search', [
                'query' => [
                    'q'      => $query,
                    'format' => 'json',
                    'limit'  => 5,
                ],
                'headers' => [
                    'User-Agent' => 'EspritPidevNexoraApp/1.0 (contact@nexora.tn)',
                    'Accept'     => 'application/json',
                ],
                'timeout' => 5,
            ]);

            $data = $response->toArray();
            $results = array_map(fn($item) => $item['display_name'] ?? '', $data);
            
            return $this->json(array_values(array_filter($results)));
        } catch (\Throwable $e) {
            // Silently ignore or log error if needed
            return $this->json([]);
        }
    }

    // ========================= HELPER : MULTIPLE UPLOAD → GOOGLE DRIVE =========================
    private function handleMultipleImageUploads($form, Destination $destination): void
    {
        /** @var \Symfony\Component\HttpFoundation\File\UploadedFile[]|null $imageFiles */
        $imageFiles = $form->get('imageFiles')->getData();
        if (empty($imageFiles)) return;

        $ordre = $destination->getImagesCount();

        foreach ($imageFiles as $imageFile) {
            try {
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename     = $this->slugger->slug($originalFilename);
                $fileName         = $safeFilename . '-' . uniqid() . '.' . $imageFile->guessExtension();
                $mimeType         = $imageFile->getMimeType() ?? 'image/jpeg';

                // Upload vers Google Drive — retourne l'URL publique
                $driveUrl = $this->driveService->uploadImage(
                    $imageFile->getPathname(),
                    $fileName,
                    $mimeType
                );

                $destImage = new DestinationImage();
                $destImage->setChemin($driveUrl);      // ex: https://drive.google.com/uc?id=XXX
                $destImage->setOrdre($ordre++);
                $destination->addDestinationImage($destImage);

            } catch (\Throwable $e) {
                // Log l'erreur sans bloquer les autres images
                $this->addFlash('warning', "Erreur upload image '{$imageFile->getClientOriginalName()}': " . $e->getMessage());
            }
        }
    }

    // ========================= HELPER : DELETE FILE (local ou Drive) =========================
    private function deleteImageFile(?string $path): void
    {
        if (!$path) return;

        // Chemin Google Drive → supprimer via l'API
        if (str_starts_with($path, 'https://drive.google.com')) {
            try {
                $this->driveService->deleteFile($path);
            } catch (\Throwable $e) {
                // On ignore si l'image n'est plus sur Drive
            }
            return;
        }

        // Ancien chemin local → supprimer le fichier physique
        if (!str_starts_with($path, 'http')) {
            $fullPath = $this->getParameter('kernel.project_dir') . '/public' . $path;
            if (file_exists($fullPath)) {
                unlink($fullPath);
            }
        }
    }
}
