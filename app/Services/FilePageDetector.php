<?php
namespace App\Services;

use Illuminate\Http\UploadedFile;

class FilePageDetector
{
    /**
     * Detect number of pages from uploaded file.
     * Returns null if detection fails or format unsupported.
     */
    public static function detect(UploadedFile $file): ?int
    {
        $ext = strtolower($file->getClientOriginalExtension());
        $path = $file->getRealPath();

        try {
            return match($ext) {
                'pdf'        => static::fromPdf($path),
                'docx'       => static::fromDocx($path),
                'doc'        => static::fromDoc($path),
                'jpg','jpeg',
                'png','webp' => 1, // images are always 1 page
                default      => null,
            };
        } catch (\Throwable $e) {
            return null;
        }
    }

    private static function fromPdf(string $path): ?int
    {
        // Method 1: smalot/pdfparser
        try {
            $parser = new \Smalot\PdfParser\Parser();
            $pdf    = $parser->parseFile($path);
            $pages  = count($pdf->getPages());
            if ($pages > 0) return $pages;
        } catch (\Throwable $e) {}

        // Method 2: Read raw PDF bytes for /Type /Page count
        try {
            $content = file_get_contents($path);
            preg_match_all('/\/Type\s*\/Page[^s]/i', $content, $matches);
            $count = count($matches[0]);
            if ($count > 0) return $count;
        } catch (\Throwable $e) {}

        return null;
    }

    private static function fromDocx(string $path): ?int
    {
        try {
            // Read docx as zip, extract word/document.xml
            $zip = new \ZipArchive();
            if ($zip->open($path) === true) {
                // Try app.xml for page count first
                $appXml = $zip->getFromName('docProps/app.xml');
                if ($appXml) {
                    preg_match('/<Pages>(\d+)<\/Pages>/i', $appXml, $m);
                    if (!empty($m[1]) && (int)$m[1] > 0) {
                        $zip->close();
                        return (int)$m[1];
                    }
                }
                $zip->close();
            }

            // Fallback: use PhpWord word count estimation
            $phpWord = \PhpOffice\PhpWord\IOFactory::load($path);
            $text    = '';
            foreach ($phpWord->getSections() as $section) {
                foreach ($section->getElements() as $element) {
                    if (method_exists($element, 'getText')) {
                        $text .= $element->getText() . ' ';
                    }
                }
            }
            // Rough estimate: ~300 words per page
            $words = str_word_count(strip_tags($text));
            return max(1, (int)ceil($words / 300));

        } catch (\Throwable $e) {
            return null;
        }
    }

    private static function fromDoc(string $path): ?int
    {
        try {
            // Try to read binary .doc and estimate
            $content = file_get_contents($path);
            // Very rough: count form feed characters
            $pages = substr_count($content, "\x0C");
            return $pages > 0 ? $pages + 1 : null;
        } catch (\Throwable $e) {
            return null;
        }
    }
}