<?php

declare(strict_types=1);

namespace App\Services;

/**
 * Resume Text Extractor Service
 * 
 * Extracts plain text from PDF and DOCX resume files.
 * Supports: PDF (via pdftotext), DOCX (via ZipArchive)
 */
class ResumeTextExtractor
{
    private const MAX_TEXT_LENGTH = 50000; // Limit text for AI API cost control
    private const PDFTOTEXT_TIMEOUT = 30; // seconds

    /**
     * Extract text from resume file (auto-detects format)
     * 
     * @param string $filePath Full path to resume file
     * @return string Extracted plain text (normalized)
     * @throws \RuntimeException If extraction fails
     */
    public function extractResumeText(string $filePath): string
    {
        // Security: Validate file path (prevent directory traversal)
        $realPath = realpath($filePath);
        if ($realPath === false || !is_file($realPath)) {
            throw new \RuntimeException("Resume file not found: " . basename($filePath));
        }

        $extension = strtolower(pathinfo($realPath, PATHINFO_EXTENSION));

        switch ($extension) {
            case 'pdf':
                $text = $this->extractTextFromPdf($realPath);
                break;
            case 'docx':
                $text = $this->extractTextFromDocx($realPath);
                break;
            case 'doc':
                // Old .doc format - try to extract or return error
                throw new \RuntimeException("DOC format not supported. Please convert to PDF or DOCX.");
            default:
                throw new \RuntimeException("Unsupported file format: {$extension}");
        }

        return $this->normalizeText($text);
    }

    /**
     * Extract text from PDF using pdftotext command
     * 
     * @param string $filePath Full path to PDF file
     * @return string Extracted text
     * @throws \RuntimeException If extraction fails
     */
    public function extractTextFromPdf(string $filePath): string
    {
        // Check if pdftotext is available
        $pdftotextPath = $this->findPdftotext();
        if ($pdftotextPath === null) {
            throw new \RuntimeException("pdftotext command not found. Please install poppler-utils or xpdf.");
        }

        // Escape file path for shell execution
        $realPath = realpath($filePath);
        if ($realPath === false) {
            throw new \RuntimeException("Invalid PDF file path");
        }
        $escapedPath = escapeshellarg($realPath);
        
        // Execute pdftotext with timeout
        $command = sprintf(
            '%s -layout -nopgbrk %s - 2>&1',
            escapeshellcmd($pdftotextPath),
            $escapedPath
        );

        $output = [];
        $returnVar = 0;
        
        // Use proc_open for better timeout control
        $descriptorspec = [
            0 => ['pipe', 'r'],
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w']
        ];

        $process = proc_open($command, $descriptorspec, $pipes);
        
        if (!is_resource($process)) {
            throw new \RuntimeException("Failed to execute pdftotext command");
        }

        // Set timeout
        $startTime = time();
        $text = '';
        
        while (true) {
            $status = proc_get_status($process);
            if (!$status['running']) {
                break;
            }
            if (time() - $startTime > self::PDFTOTEXT_TIMEOUT) {
                proc_terminate($process);
                throw new \RuntimeException("PDF extraction timeout");
            }
            usleep(100000); // 0.1 second
        }

        $text = stream_get_contents($pipes[1]);
        $errors = stream_get_contents($pipes[2]);
        
        fclose($pipes[0]);
        fclose($pipes[1]);
        fclose($pipes[2]);
        
        proc_close($process);

        if (!empty($errors) && strpos($errors, 'Error') !== false) {
            error_log("PDF extraction error: " . $errors);
            throw new \RuntimeException("PDF extraction failed: " . substr($errors, 0, 200));
        }

        if (empty($text)) {
            throw new \RuntimeException("No text extracted from PDF");
        }

        return $text;
    }

    /**
     * Extract text from DOCX using ZipArchive
     * 
     * @param string $filePath Full path to DOCX file
     * @return string Extracted text
     * @throws \RuntimeException If extraction fails
     */
    public function extractTextFromDocx(string $filePath): string
    {
        if (!class_exists('ZipArchive')) {
            throw new \RuntimeException("ZipArchive class not available. Please install php-zip extension.");
        }

        $zip = new \ZipArchive();
        $realPath = realpath($filePath);
        
        if ($realPath === false) {
            throw new \RuntimeException("DOCX file not found");
        }

        if ($zip->open($realPath) !== true) {
            throw new \RuntimeException("Failed to open DOCX file as ZIP archive");
        }

        // Read word/document.xml
        $documentXml = $zip->getFromName('word/document.xml');
        $zip->close();

        if ($documentXml === false) {
            throw new \RuntimeException("Failed to read document.xml from DOCX");
        }

        // Remove XML tags and extract text
        $text = strip_tags($documentXml);
        
        // Decode XML entities
        $text = html_entity_decode($text, ENT_QUOTES | ENT_XML1, 'UTF-8');
        
        // Clean up Word-specific formatting
        $text = preg_replace('/\s+/', ' ', $text); // Multiple spaces to single
        $text = str_replace(["\r\n", "\r", "\n"], ' ', $text); // Newlines to space
        
        if (empty(trim($text))) {
            throw new \RuntimeException("No text extracted from DOCX");
        }

        return $text;
    }

    /**
     * Normalize extracted text
     * 
     * @param string $text Raw extracted text
     * @return string Normalized text
     */
    private function normalizeText(string $text): string
    {
        // Remove control characters except newlines and tabs
        $text = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $text);
        
        // Normalize whitespace
        $text = preg_replace('/\s+/', ' ', $text);
        
        // Trim
        $text = trim($text);
        
        // Limit length for AI API cost control
        if (mb_strlen($text) > self::MAX_TEXT_LENGTH) {
            $text = mb_substr($text, 0, self::MAX_TEXT_LENGTH) . '... [truncated]';
            error_log("Resume text truncated to " . self::MAX_TEXT_LENGTH . " characters");
        }

        return $text;
    }

    /**
     * Find pdftotext executable path
     * 
     * @return string|null Path to pdftotext or null if not found
     */
    private function findPdftotext(): ?string
    {
        $possiblePaths = [
            '/usr/bin/pdftotext',
            '/usr/local/bin/pdftotext',
            'pdftotext', // In PATH
            'C:\\Program Files\\xpdf-tools-win-4.04\\bin64\\pdftotext.exe', // Windows
        ];

        foreach ($possiblePaths as $path) {
            if ($path === 'pdftotext') {
                // Check if command exists in PATH
                $output = [];
                $returnVar = 0;
                @exec('which pdftotext 2>&1', $output, $returnVar);
                if ($returnVar === 0 && !empty($output)) {
                    return trim($output[0]);
                }
            } elseif (is_executable($path)) {
                return $path;
            }
        }

        return null;
    }
}

