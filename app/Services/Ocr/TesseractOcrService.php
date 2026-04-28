<?php

namespace App\Services\Ocr;

use thiagoalessio\TesseractOCR\TesseractOCR;

class TesseractOcrService
{
    /**
     * Extract text from an image file using Tesseract OCR.
     */
    public function extractText(string $imagePath): string
    {
        $preprocessed = $this->preprocess($imagePath);
        $pathToUse = $preprocessed ?? $imagePath;

        try {
            $ocr = new TesseractOCR($pathToUse);
            $ocr->executable(config('ocr.tesseract_path'));
            $ocr->lang(config('ocr.language'));
            $ocr->psm(6); // Assume a single uniform block of text (good for tables)

            return (string) $ocr->run();
        } finally {
            if ($preprocessed && file_exists($preprocessed)) {
                unlink($preprocessed);
            }
        }
    }

    /**
     * Pre-process image for better OCR accuracy: grayscale + contrast.
     */
    private function preprocess(string $imagePath): ?string
    {
        $info = getimagesize($imagePath);
        if ($info === false) {
            return null;
        }

        $mime = $info['mime'];
        $image = match ($mime) {
            'image/jpeg' => imagecreatefromjpeg($imagePath),
            'image/png'  => imagecreatefrompng($imagePath),
            default      => null,
        };

        if ($image === null) {
            return null;
        }

        // Convert to grayscale
        imagefilter($image, IMG_FILTER_GRAYSCALE);
        // Increase contrast
        imagefilter($image, IMG_FILTER_CONTRAST, -30);
        // Increase brightness slightly
        imagefilter($image, IMG_FILTER_BRIGHTNESS, 20);

        $tmpPath = $imagePath . '_preprocessed.png';
        imagepng($image, $tmpPath);
        imagedestroy($image);

        return $tmpPath;
    }
}
