<?php

namespace App\Service;

use App\Entity\ParticipationDemande;
use Psr\Log\LoggerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class PdfTicketService
{
    public function __construct(
        private LoggerInterface $logger,
        private UrlGeneratorInterface $urlGenerator
    ) {}

    public function generate(ParticipationDemande $demande): ?string
    {
        try {
            $activite = $demande->getActivite();

            $ticketUrl = $this->urlGenerator->generate(
                'app_ticket_view',
                ['id' => $demande->getId()],
                UrlGeneratorInterface::ABSOLUTE_URL
            );

            // Générer l'image du ticket via GD
            $imgData = $this->generateTicketImage($demande, $activite, $ticketUrl);
            if (!$imgData) return null;

            // Encapsuler l'image PNG dans un PDF valide
            $pdf = $this->imageToPdf($imgData);

            $this->logger->info('[PdfTicket] PDF généré.', ['demande_id' => $demande->getId()]);
            return $pdf;

        } catch (\Throwable $e) {
            $this->logger->error('[PdfTicket] Erreur: ' . $e->getMessage());
            return null;
        }
    }

    private function generateTicketImage(ParticipationDemande $demande, $activite, string $ticketUrl): ?string
    {
        // A4 à 96dpi : 794 x 1123 px
        $w = 794;
        $h = 1123;
        $img = imagecreatetruecolor($w, $h);

        // Couleurs
        $white      = imagecolorallocate($img, 255, 255, 255);
        $purple     = imagecolorallocate($img, 108,  63, 197);
        $purpleLight= imagecolorallocate($img, 240, 235, 255);
        $purpleDark = imagecolorallocate($img, 78,  45, 154);
        $green      = imagecolorallocate($img, 46, 125,  50);
        $greenLight = imagecolorallocate($img, 232, 245, 233);
        $gray       = imagecolorallocate($img, 100, 100, 100);
        $grayLight  = imagecolorallocate($img, 245, 245, 245);
        $black      = imagecolorallocate($img,  30,  30,  30);
        $border     = imagecolorallocate($img, 200, 185, 230);

        // Fond blanc
        imagefilledrectangle($img, 0, 0, $w, $h, $white);

        // ── HEADER violet ──
        imagefilledrectangle($img, 0, 0, $w, 110, $purple);

        // Titre header
        $font = 5;
        $title = 'TICKET DE PARTICIPATION - NEXORA';
        $tw = strlen($title) * imagefontwidth($font);
        imagestring($img, $font, ($w - $tw) / 2, 25, $title, $white);

        $sub = 'Justificatif officiel de participation';
        $sw = strlen($sub) * imagefontwidth(3);
        imagestring($img, 3, ($w - $sw) / 2, 55, $sub, $white);

        $ref = '#' . $demande->getId();
        imagestring($img, $font, $w - strlen($ref) * imagefontwidth($font) - 20, 25, $ref, $white);

        // ── BANDE STATUT ──
        imagefilledrectangle($img, 30, 125, $w - 30, 165, $greenLight);
        imagerectangle($img, 30, 125, $w - 30, 165, $green);
        $ok = '[OK] PARTICIPATION CONFIRMEE';
        $ow = strlen($ok) * imagefontwidth($font);
        imagestring($img, $font, ($w - $ow) / 2, 138, $ok, $green);

        // ── SECTION ACTIVITE ──
        imagefilledrectangle($img, 30, 185, 390, 420, $purpleLight);
        imagerectangle($img, 30, 185, 390, 420, $border);
        imagestring($img, 4, 45, 192, 'ACTIVITE', $purple);
        imageline($img, 45, 215, 375, 215, $border);

        $actRows = [
            ['Nom',   $activite->getNom()],
            ['Type',  $activite->getType()],
            ['Lieu',  $activite->getLieu()],
            ['Date',  ($activite->getAvecDate() && $activite->getDateActivite())
                        ? $activite->getDateActivite()->format('d/m/Y H:i')
                        : 'Date flexible'],
            ['Prix',  number_format((float)$activite->getPrix(), 2) . ' TND'],
        ];
        $y = 225;
        foreach ($actRows as [$lbl, $val]) {
            imagestring($img, 3, 45, $y, $lbl . ':', $gray);
            $col = in_array($lbl, ['Nom', 'Prix']) ? $purple : $black;
            imagestring($img, 3, 160, $y, $this->truncate($val, 28), $col);
            $y += 35;
        }

        // ── SECTION PARTICIPANT ──
        imagefilledrectangle($img, 410, 185, $w - 30, 420, $purpleLight);
        imagerectangle($img, 410, 185, $w - 30, 420, $border);
        imagestring($img, 4, 425, 192, 'PARTICIPANT', $purple);
        imageline($img, 425, 215, $w - 45, 215, $border);

        $pRows = [
            ['Nom',       $demande->getClientNom()],
            ['Email',     $demande->getClientEmail()],
            ['Tel',       $demande->getClientTelephone() ?? ''],
            ['Ref',       '#' . $demande->getId()],
            ['Date dem.', $demande->getDateDemande()?->format('d/m/Y') ?? ''],
        ];
        $y = 225;
        foreach ($pRows as [$lbl, $val]) {
            imagestring($img, 3, 425, $y, $lbl . ':', $gray);
            $col = $lbl === 'Ref' ? $purple : $black;
            imagestring($img, 3, 510, $y, $this->truncate($val, 22), $col);
            $y += 35;
        }

        // ── QR CODE ──
        $qrUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=' . urlencode($ticketUrl);
        $qrImg = null;
        try {
            $ctx = stream_context_create(['http' => ['timeout' => 5]]);
            $raw = @file_get_contents($qrUrl, false, $ctx);
            if ($raw) {
                $qrImg = @imagecreatefromstring($raw);
            }
        } catch (\Throwable $e) {}

        // Zone QR
        imagefilledrectangle($img, 30, 445, $w - 30, 660, $grayLight);
        imagerectangle($img, 30, 445, $w - 30, 660, $border);

        if ($qrImg) {
            imagecopyresampled($img, $qrImg, 50, 460, 0, 0, 150, 150, imagesx($qrImg), imagesy($qrImg));
            imagedestroy($qrImg);
            imagestring($img, 3, 220, 460, 'Scannez pour valider', $gray);
            imagestring($img, 3, 220, 490, 'votre participation', $gray);
        }

        $codeRef = 'NEXORA-' . $demande->getId() . '-' . $activite->getId();
        imagestring($img, 4, 220, 530, 'Code de validation:', $purpleDark);
        imagestring($img, $font, 220, 560, $codeRef, $purple);
        imagestring($img, 3, 220, 600, 'Presentez ce code le jour de l activite', $gray);
        imagestring($img, 3, 220, 625, 'pour valider votre participation.', $gray);

        // ── FOOTER ──
        imagefilledrectangle($img, 0, $h - 80, $w, $h, $purpleLight);
        imageline($img, 0, $h - 80, $w, $h - 80, $border);
        imagestring($img, 4, 30, $h - 65, 'Nexora Activites', $purple);
        imagestring($img, 2, 30, $h - 45, 'Ce ticket est votre justificatif officiel.', $gray);
        imagestring($img, 2, 30, $h - 28, 'Presentez-le le jour de l activite.', $gray);
        $emis = 'Emis le ' . (new \DateTime())->format('d/m/Y H:i');
        imagestring($img, 2, $w - strlen($emis) * imagefontwidth(2) - 20, $h - 45, $emis, $gray);
        imagestring($img, 2, $w - strlen($codeRef) * imagefontwidth(2) - 20, $h - 28, $codeRef, $purple);

        // Capturer en PNG
        ob_start();
        imagepng($img);
        $pngData = ob_get_clean();
        imagedestroy($img);

        return $pngData ?: null;
    }

    /**
     * Encapsule une image PNG dans un PDF valide (PDF 1.4).
     */
    private function imageToPdf(string $pngData): string
    {
        // Décoder le PNG en pixels RGB bruts via GD
        $gdImg = imagecreatefromstring($pngData);
        if (!$gdImg) throw new \RuntimeException('Impossible de lire l\'image PNG');

        $imgW = imagesx($gdImg);
        $imgH = imagesy($gdImg);

        // Extraire les pixels RGB bruts ligne par ligne
        $rawPixels = '';
        for ($y = 0; $y < $imgH; $y++) {
            for ($x = 0; $x < $imgW; $x++) {
                $color = imagecolorat($gdImg, $x, $y);
                $rawPixels .= chr(($color >> 16) & 0xFF)  // R
                            . chr(($color >> 8)  & 0xFF)  // G
                            . chr($color         & 0xFF); // B
            }
        }
        imagedestroy($gdImg);

        // A4 en points (595 x 842)
        $pageW = 595;
        $pageH = 842;

        $scaleX = $pageW / $imgW;
        $scaleY = $pageH / $imgH;
        $scale  = min($scaleX, $scaleY);
        $drawW  = round($imgW * $scale);
        $drawH  = round($imgH * $scale);
        $offX   = round(($pageW - $drawW) / 2);
        $offY   = round(($pageH - $drawH) / 2);

        $imgLen = strlen($rawPixels);

        // Construire les objets PDF
        $obj1 = "1 0 obj\n<< /Type /Catalog /Pages 2 0 R >>\nendobj\n";
        $obj2 = "2 0 obj\n<< /Type /Pages /Kids [3 0 R] /Count 1 >>\nendobj\n";
        $obj3 = "3 0 obj\n<< /Type /Page /Parent 2 0 R /MediaBox [0 0 $pageW $pageH] /Contents 4 0 R /Resources << /XObject << /Im1 5 0 R >> >> >>\nendobj\n";

        $content    = "q\n$drawW 0 0 $drawH $offX $offY cm\n/Im1 Do\nQ\n";
        $contentLen = strlen($content);
        $obj4 = "4 0 obj\n<< /Length $contentLen >>\nstream\n$content\nendstream\nendobj\n";

        $obj5 = "5 0 obj\n<< /Type /XObject /Subtype /Image /Width $imgW /Height $imgH /ColorSpace /DeviceRGB /BitsPerComponent 8 /Length $imgLen >>\nstream\n" . $rawPixels . "\nendstream\nendobj\n";

        // Assembler le PDF
        $body = "%PDF-1.4\n%\xe2\xe3\xcf\xd3\n";
        $offsets = [];

        $offsets[1] = strlen($body); $body .= $obj1;
        $offsets[2] = strlen($body); $body .= $obj2;
        $offsets[3] = strlen($body); $body .= $obj3;
        $offsets[4] = strlen($body); $body .= $obj4;
        $offsets[5] = strlen($body); $body .= $obj5;

        $xrefPos = strlen($body);
        $body .= "xref\n0 6\n";
        $body .= "0000000000 65535 f \n";
        for ($i = 1; $i <= 5; $i++) {
            $body .= str_pad((string)$offsets[$i], 10, '0', STR_PAD_LEFT) . " 00000 n \n";
        }
        $body .= "trailer\n<< /Size 6 /Root 1 0 R >>\n";
        $body .= "startxref\n$xrefPos\n%%EOF\n";

        return $body;
    }

    private function truncate(string $text, int $max): string
    {
        if (mb_strlen($text) <= $max) return $text;
        return mb_substr($text, 0, $max - 3) . '...';
    }
}
