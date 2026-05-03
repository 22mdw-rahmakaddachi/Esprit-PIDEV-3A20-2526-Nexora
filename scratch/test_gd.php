<?php

// This is a dummy test script to verify PdfTicketService logic

class MockDemande {
    public function getId() { return 123; }
    public function getActivite() { return new MockActivite(); }
    public function getClientNom() { return "John Doe"; }
    public function getClientEmail() { return "john@example.com"; }
    public function getClientTelephone() { return "12345678"; }
    public function getDateDemande() { return new DateTime(); }
}

class MockActivite {
    public function getId() { return 456; }
    public function getNom() { return "Excursion Sahara"; }
    public function getType() { return "Aventure"; }
    public function getLieu() { return "Tozeur"; }
    public function getAvecDate() { return true; }
    public function getDateActivite() { return new DateTime("+2 days"); }
    public function getPrix() { return 150.0; }
}

// simulate the service logic
function imageToPdfMock(string $pngData): string {
    $gdImg = imagecreatefromstring($pngData);
    $imgW = imagesx($gdImg); $imgH = imagesy($gdImg);
    $rawPixels = '';
    for ($y = 0; $y < $imgH; $y++){
        for ($x = 0; $x < $imgW; $x++){
            $color = imagecolorat($gdImg, $x, $y);
            $rawPixels .= chr(($color >> 16) & 0xFF) . chr(($color >> 8)  & 0xFF) . chr($color & 0xFF);
        }
    }
    imagedestroy($gdImg);
    $pageW = 595; $pageH = 842;
    $obj1 = "1 0 obj\n<< /Type /Catalog /Pages 2 0 R >>\nendobj\n";
    $obj2 = "2 0 obj\n<< /Type /Pages /Kids [3 0 R] /Count 1 >>\nendobj\n";
    $obj3 = "3 0 obj\n<< /Type /Page /Parent 2 0 R /MediaBox [0 0 $pageW $pageH] /Contents 4 0 R /Resources << /XObject << /Im1 5 0 R >> >> >>\nendobj\n";
    $scale = min($pageW / $imgW, $pageH / $imgH);
    $drawW = round($imgW * $scale); $drawH = round($imgH * $scale);
    $content = "q\n$drawW 0 0 $drawH 0 0 cm\n/Im1 Do\nQ\n";
    $obj4 = "4 0 obj\n<< /Length ".strlen($content)." >>\nstream\n$content\nendstream\nendobj\n";
    $obj5 = "5 0 obj\n<< /Type /XObject /Subtype /Image /Width $imgW /Height $imgH /ColorSpace /DeviceRGB /BitsPerComponent 8 /Length ".strlen($rawPixels)." >>\nstream\n" . $rawPixels . "\nendstream\nendobj\n";
    $body = "%PDF-1.4\n";
    $body .= $obj1 . $obj2 . $obj3 . $obj4 . $obj5;
    return $body;
}

echo "Testing GD functions...\n";
if (extension_loaded('gd')) {
    echo "GD is loaded.\n";
    $img = imagecreatetruecolor(100, 100);
    ob_start();
    imagepng($img);
    $data = ob_get_clean();
    echo "Image PNG generated: " . strlen($data) . " bytes\n";
    $pdf = imageToPdfMock($data);
    echo "PDF generated: " . strlen($pdf) . " bytes\n";
} else {
    echo "GD is NOT loaded.\n";
}
