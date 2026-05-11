<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Filesystem\Filesystem;

class FaceRecognitionService
{
    private string $faceDataDirectory;
    private string $scriptPath;
    private Filesystem $filesystem;

    public function __construct(string $projectDir)
    {
        $this->filesystem        = new Filesystem();
        $this->faceDataDirectory = $projectDir . '/var/face_data/';
        $this->scriptPath        = $projectDir . '/face_recognition_service.py';

        if (!$this->filesystem->exists($this->faceDataDirectory)) {
            $this->filesystem->mkdir($this->faceDataDirectory, 0777);
        }
    }

    public function registerFace(int $userId, UploadedFile $faceImage, string $userName = ''): array
    {
        try {
            // Sauvegarder l'image temporairement
            $tmpPath = sys_get_temp_dir() . '/face_register_' . $userId . '_' . uniqid() . '.jpg';
            $faceImage->move(dirname($tmpPath), basename($tmpPath));

            $result = $this->runPython('register', $userId, $tmpPath);

            @unlink($tmpPath);

            if ($result['success'] ?? false) {
                // Sauvegarder aussi le nom
                $jsonPath = $this->faceDataDirectory . "user_{$userId}_encoding.json";
                if (file_exists($jsonPath)) {
                    $data = json_decode(file_get_contents($jsonPath), true);
                    $data['user_name'] = $userName;
                    file_put_contents($jsonPath, json_encode($data));
                }
            }

            return $result;

        } catch (\Exception $e) {
            return ['success' => false, 'message' => 'Erreur: ' . $e->getMessage()];
        }
    }

    public function verifyFace(int $userId, UploadedFile $currentFaceImage): array
    {
        try {
            $userEncodingFile = $this->faceDataDirectory . "user_{$userId}_encoding.json";

            if (!$this->filesystem->exists($userEncodingFile)) {
                return ['verified' => false, 'message' => 'Aucune donnée faciale enregistrée pour cet utilisateur.'];
            }

            $tmpPath = sys_get_temp_dir() . '/face_verify_' . $userId . '_' . uniqid() . '.jpg';
            $currentFaceImage->move(dirname($tmpPath), basename($tmpPath));

            $result = $this->runPython('verify', $userId, $tmpPath);

            @unlink($tmpPath);

            return $result;

        } catch (\Exception $e) {
            return ['verified' => false, 'message' => 'Erreur: ' . $e->getMessage()];
        }
    }

    public function identifyFace(UploadedFile $faceImage): array
    {
        try {
            $tmpPath = sys_get_temp_dir() . '/face_identify_' . uniqid() . '.jpg';
            $faceImage->move(dirname($tmpPath), basename($tmpPath));

            // Chercher dans tous les utilisateurs enregistrés
            $files = glob($this->faceDataDirectory . "user_*_encoding.json") ?: [];
            $bestMatch      = null;
            $bestSimilarity = 0;

            foreach ($files as $file) {
                $data   = json_decode(file_get_contents($file), true);
                $userId = $data['user_id'] ?? null;
                if (!$userId) continue;

                $result = $this->runPython('verify', $userId, $tmpPath);
                $sim    = $result['similarity'] ?? 0;

                if (($result['verified'] ?? false) && $sim > $bestSimilarity) {
                    $bestSimilarity = $sim;
                    $bestMatch      = ['user_id' => $userId, 'user_name' => $data['user_name'] ?? '', 'similarity' => $sim];
                }
            }

            @unlink($tmpPath);

            if ($bestMatch) {
                return ['identified' => true, ...$bestMatch, 'message' => 'Utilisateur identifié'];
            }

            return ['identified' => false, 'message' => 'Aucun utilisateur correspondant'];

        } catch (\Exception $e) {
            return ['identified' => false, 'message' => 'Erreur: ' . $e->getMessage()];
        }
    }

    public function deleteFaceData(int $userId): bool
    {
        foreach (["user_{$userId}_encoding.json", "user_{$userId}_face.jpg", "user_{$userId}_reference.jpg"] as $f) {
            $path = $this->faceDataDirectory . $f;
            if ($this->filesystem->exists($path)) {
                $this->filesystem->remove($path);
            }
        }
        return true;
    }

    private function runPython(string $action, int $userId, string $imagePath): array
    {
        $cmd = sprintf(
            'python %s %s %d %s %s 2>&1',
            escapeshellarg($this->scriptPath),
            escapeshellarg($action),
            $userId,
            escapeshellarg($imagePath),
            escapeshellarg($this->faceDataDirectory)
        );

        $output   = [];
        $exitCode = 0;
        exec($cmd, $output, $exitCode);

        $json = implode('', $output);
        $data = json_decode($json, true);

        if (!$data) {
            return ['success' => false, 'verified' => false, 'message' => 'Erreur Python: ' . $json];
        }

        return $data;
    }
}
