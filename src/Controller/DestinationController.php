<?php

namespace App\Controller;

use App\Entity\Destination;
use App\Entity\DestinationImage;
use App\Form\DestinationType;
use App\Repository\DestinationRepository;
use App\Service\GoogleDriveService;
use App\Service\TravelInfoService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\DestinationParticipantRepository;
use App\Repository\UsersRepository;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/admin/destinations')]
class DestinationController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $em,
        private DestinationRepository  $repo,
        private SluggerInterface       $slugger,
        private GoogleDriveService     $driveService,
        private TravelInfoService       $travelService,
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
            $this->handleMultipleImageUploads($form, $destination);

            $this->em->persist($destination);
            $this->em->flush();

            $this->addFlash('success', 'Excursion ajoutée avec succès !');
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

            $this->addFlash('success', 'Excursion modifiée avec succès !');
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
            $this->addFlash('success', 'Excursion supprimée.');
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

    // ========================= EXPORT PARTICIPANTS (EXCEL) =========================
    #[Route('/{id}/export-participants', name: 'admin_destination_export_participants', methods: ['GET'])]
    public function exportParticipants(Destination $destination,DestinationParticipantRepository $partRepo,UsersRepository $userRepo): Response {
        $participants = $partRepo->findBy(['destination' => $destination]);

        if (count($participants) === 0) {
            $this->addFlash('warning', 'Aucun participant à exporter pour ' . $destination->getNom());
            return $this->redirectToRoute('admin_destination_index');
        }

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Participants');

        // Style des en-têtes
        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => '4F46E5']],
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
        ];

        // Remplissage En-têtes
        $sheet->setCellValue('A1', 'Nom Complet');
        $sheet->setCellValue('B1', 'Adresse Email');
        $sheet->setCellValue('C1', 'Date d\'inscription');
        $sheet->getStyle('A1:C1')->applyFromArray($headerStyle);

        $row = 2;
        foreach ($participants as $p) {
            $user = $userRepo->find($p->getUserId());
            $email = $user ? $user->getEmail() : 'Utilisateur supprimé';

            $sheet->setCellValue('A' . $row, $p->getUserNom());
            $sheet->setCellValue('B' . $row, $email);
            $sheet->setCellValue('C' . $row, $p->getJoinedAt()->format('d/m/Y H:i'));
            
            // Bordures pour les données
            $sheet->getStyle("A$row:C$row")->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
            
            $row++;
        }

        // Auto-dimensionnement
        foreach (range('A', 'C') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $writer = new Xlsx($spreadsheet);
        $safeName = $this->slugger->slug($destination->getNom());
        $fileName = "Participants_{$safeName}_" . date('Y-m-d') . ".xlsx";

        $response = new StreamedResponse(function() use ($writer) {
            $writer->save('php://output');
        });

        $disposition = HeaderUtils::makeDisposition(HeaderUtils::DISPOSITION_ATTACHMENT, $fileName);
        $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response->headers->set('Content-Disposition', $disposition);

        return $response;
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
        $newUrls = [];

        foreach ($imageFiles as $imageFile) {
            try {
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename     = $this->slugger->slug($originalFilename);
                $fileName         = $safeFilename . '-' . uniqid() . '.' . $imageFile->guessExtension();
                $mimeType         = $imageFile->getMimeType() ?? 'image/jpeg';

                // Upload vers Google Drive — retourne l'URL publique (thumbnail avec sz=w1200)
                $driveUrl = $this->driveService->uploadImage(
                    $imageFile->getPathname(),
                    $fileName,
                    $mimeType
                );

                // 1. Stockage dans la table de relation (Recommandé)
                $destImage = new DestinationImage();
                $destImage->setChemin($driveUrl);
                $destImage->setOrdre($ordre++);
                $destination->addDestinationImage($destImage);
                
                $newUrls[] = $driveUrl;

            } catch (\Throwable $e) {
                $this->addFlash('warning', "Erreur upload image '{$imageFile->getClientOriginalName()}': " . $e->getMessage());
            }
        }

        // 2. Synchronisation avec l'ancienne colonne 'images' (pour visibilité directe dans la table 'destination')
        if (!empty($newUrls)) {
            $existingImages = $destination->getImages() ? explode(',', $destination->getImages()) : [];
            $allImages = array_merge($existingImages, $newUrls);
            $destination->setImages(implode(',', array_filter($allImages)));
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

    // ========================= MAGIC AUTO-FILL ESSENTIALS =========================
    #[Route('/api/essentials-magic', name: 'admin_destination_essentials', methods: ['GET'])]
    public function apiEssentials(Request $request): JsonResponse
    {
        $localisation = $request->query->get('q', '');
        if (!$localisation) return $this->json(['error' => 'Localisation manquante'], 400);

        // Extraire le pays (souvent après la dernière virgule)
        $parts = explode(',', $localisation);
        $country = trim(end($parts));

        $data = $this->travelService->getEssentials($country);
        
        if (empty($data)) {
            // Tentative sans virgule si un seul mot
            $data = $this->travelService->getEssentials($localisation);
        }

        return $this->json($data);
    }
}
