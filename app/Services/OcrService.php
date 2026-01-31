<?php

namespace App\Services;

use App\Core\Database;
use App\Core\Storage;
use thiagoalessio\TesseractOCR\TesseractOCR;

class OcrService
{
    public function extract(array $document): array
    {
        $localPath = Storage::disk('kyc')->path($document['path']);
        $rawText = (new TesseractOCR($localPath))
            ->lang('eng')
            ->psm(6)
            ->oem(1)
            ->run();

        $parsed = $this->parseFields($rawText);

        Database::getInstance()->query(
            "INSERT INTO document_ocr_results (document_id, employer_id, extracted_name, extracted_gst,
             extracted_cin, extracted_address, extracted_registration_date, confidence_score, raw_text)
             VALUES (:document_id, :employer_id, :name, :gst, :cin, :address, :reg_date, :confidence, :raw_text)",
            [
                'document_id' => $document['id'],
                'employer_id' => $document['employer_id'],
                'name' => $parsed['company_name'],
                'gst' => $parsed['gst'],
                'cin' => $parsed['cin'],
                'address' => $parsed['address'],
                'reg_date' => $parsed['registration_date'],
                'confidence' => $parsed['confidence'],
                'raw_text' => $rawText,
            ]
        );

        return $parsed;
    }

    private function parseFields(string $text): array
    {
        $gstPattern = '/\b[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}Z[0-9A-Z]{1}\b/';
        $cinPattern = '/\b[UL][0-9]{5}[A-Z]{2}[0-9]{4}[PLC|PTC|LLC|OPC]{3}[0-9]{6}\b/';

        preg_match($gstPattern, $text, $gst);
        preg_match($cinPattern, $text, $cin);

        return [
            'company_name'      => $this->matchLine($text, 'Company|Name|Firm'),
            'gst'               => $gst[0] ?? null,
            'cin'               => $cin[0] ?? null,
            'address'           => $this->matchBlock($text, 'Address'),
            'registration_date' => $this->matchDate($text),
            'confidence'        => 0.82 // basic default; can be improved with OCR engine meta
        ];
    }

    private function matchLine(string $text, string $keywordsPattern): ?string
    {
        $lines = preg_split('/\r\n|\r|\n/', $text);
        foreach ($lines as $line) {
            if (preg_match('/(' . $keywordsPattern . ')\s*[:\-]/i', $line)) {
                return trim(preg_replace('/(' . $keywordsPattern . ')\s*[:\-]\s*/i', '', $line));
            }
        }
        return null;
    }

    private function matchBlock(string $text, string $keyword): ?string
    {
        if (!preg_match('/' . preg_quote($keyword, '/') . '[:\-]?(.*)/i', $text, $m)) {
            return null;
        }
        $block = trim($m[1]);
        $block = preg_replace('/\s{2,}/', ' ', $block);
        return $block;
    }

    private function matchDate(string $text): ?string
    {
        $pattern = '/\b(0?[1-9]|[12][0-9]|3[01])[\-\/](0?[1-9]|1[0-2])[\-\/](19|20)\d\d\b/';
        if (preg_match($pattern, $text, $m)) {
            $date = \DateTime::createFromFormat('d-m-Y', $m[0]) ?: \DateTime::createFromFormat('d/m/Y', $m[0]);
            return $date ? $date->format('Y-m-d') : null;
        }
        return null;
    }
}