<?php

return [
    'tesseract_path' => env('TESSERACT_PATH', '/usr/bin/tesseract'),
    'language'       => env('OCR_LANGUAGE', 'spa'),
    'confidence_threshold' => (float) env('OCR_CONFIDENCE_THRESHOLD', 0.6),
];
