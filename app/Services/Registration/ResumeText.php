<?php

declare(strict_types=1);

namespace App\Services\Registration;

/**
 * Plain text of an uploaded resume (PDF / DOCX / DOC) so hospitals and hirers can match
 * candidates by keywords inside their resume.
 */
class ResumeText
{
    private const MAX_CHARS = 20000;

    public static function extract(string $absolutePath): string
    {
        if (!is_file($absolutePath)) {
            return '';
        }
        $ext = strtolower(pathinfo($absolutePath, PATHINFO_EXTENSION));
        $text = '';
        try {
            if ($ext === 'pdf' && class_exists(\Smalot\PdfParser\Parser::class)) {
                $text = (new \Smalot\PdfParser\Parser())->parseFile($absolutePath)->getText();
            } elseif ($ext === 'docx' && class_exists(\ZipArchive::class)) {
                $zip = new \ZipArchive();
                if ($zip->open($absolutePath) === true) {
                    $xml = (string)$zip->getFromName('word/document.xml');
                    $zip->close();
                    $text = strip_tags(str_replace(['</w:p>', '<w:tab/>'], ["\n", ' '], $xml));
                }
            } elseif ($ext === 'doc') {
                // Old binary Word: keep readable runs of text.
                preg_match_all('/[\x20-\x7E]{4,}/', (string)file_get_contents($absolutePath, false, null, 0, 2_000_000), $m);
                $text = implode(' ', $m[0]);
            }
        } catch (\Throwable $e) {
            error_log('ResumeText: ' . $e->getMessage());
            return '';
        }
        $text = html_entity_decode($text, ENT_QUOTES | ENT_XML1, 'UTF-8');
        $text = preg_replace('/[^\P{C}\n]+/u', ' ', $text) ?? '';
        $text = trim((string)preg_replace('/[ \t]+/', ' ', (string)preg_replace("/\n{3,}/", "\n\n", $text)));
        return mb_substr($text, 0, self::MAX_CHARS);
    }

    /** Lower-case keyword tokens (3+ chars, common words removed). */
    public static function keywords(string $text): array
    {
        static $stop = ['and', 'the', 'for', 'with', 'from', 'that', 'this', 'are', 'was', 'have', 'has', 'you', 'your', 'our', 'will', 'can', 'all', 'any', 'job', 'jobs', 'work', 'need', 'required', 'looking', 'candidate', 'candidates', 'years', 'year', 'experience', 'who', 'should', 'must', 'per', 'month'];
        preg_match_all('/[\p{L}\p{N}+#.]{3,}/u', mb_strtolower($text), $m);
        return array_values(array_diff(array_unique(array_map(static fn($w) => rtrim($w, '.'), $m[0])), $stop));
    }
}
