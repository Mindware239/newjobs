<?php

declare(strict_types=1);

namespace App\Services;

use Dompdf\Dompdf;
use Dompdf\Options;
use App\Core\Database;

class EmploymentVerificationPDFService
{
    private string $storagePath;
    private string $publicPath;

    public function __construct()
    {
        $baseDir = dirname(__DIR__, 2);
        $this->storagePath = $baseDir . '/storage/verification/';
        $this->publicPath = '/storage/verification/';
        if (!is_dir($this->storagePath)) {
            @mkdir($this->storagePath, 0755, true);
        }
    }

    public function generate(int $employmentId): ?string
    {
        $db = \App\Core\Database::getInstance();
        $er = $db->fetchOne("SELECT er.*, c.full_name FROM employment_records er INNER JOIN candidates c ON er.candidate_id = c.id WHERE er.id = :id", ['id' => $employmentId]);
        if (!$er) return null;
        $score = $db->fetchOne("SELECT score, category FROM verification_scores WHERE employment_id = :id ORDER BY calculated_at DESC LIMIT 1", ['id' => $employmentId]) ?: [];
        $html = $this->renderHtml($er, $score);
        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);
        $options->set('defaultFont', 'DejaVu Sans');
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        $filename = 'verify_' . $employmentId . '_' . time() . '.pdf';
        $path = $this->storagePath . $filename;
        $pdf = $dompdf->output();
        if (@file_put_contents($path, $pdf) === false) {
            return null;
        }
        return $this->publicPath . $filename;
    }

    private function renderHtml(array $er, array $score): string
    {
        $name = trim(((string)($er['first_name'] ?? '')) . ' ' . ((string)($er['last_name'] ?? '')));
        $company = htmlspecialchars((string)($er['company_name'] ?? ''));
        $designation = htmlspecialchars((string)($er['designation'] ?? ''));
        $period = htmlspecialchars((string)($er['start_date'] ?? '')) . ' — ' . htmlspecialchars((string)($er['end_date'] ?? ''));
        $method = 'HR + Documents';
        $riskCat = htmlspecialchars((string)($score['category'] ?? ''));
        $finalStatus = htmlspecialchars((string)($er['status_overall'] ?? 'under_review'));
        $verDate = htmlspecialchars((string)($er['verification_date'] ?? date('Y-m-d')));
        $verBy = 'Mindware Employment Screening Division';
        return '<html><head><style>
            body{font-family:DejaVu Sans, sans-serif;color:#111;}
            .card{padding:18px;border:1px solid #e5e7eb;border-radius:10px}
            .title{font-size:20px;font-weight:700;margin-bottom:8px}
            .meta{font-size:12px;color:#6b7280}
            .row{display:flex;gap:12px;margin-top:10px}
            .col{flex:1}
            .badge{display:inline-block;padding:4px 10px;border-radius:9999px;background:#eef2ff;color:#3730a3;font-size:12px}
            table{width:100%;border-collapse:collapse;margin-top:12px}
            th,td{border:1px solid #e5e7eb;padding:8px;text-align:left;font-size:12px}
        </style></head><body>
        <div class="card">
          <div class="title">Employment Verification Report</div>
          <div class="meta">Verified by ' . $verBy . '</div>
          <div class="row">
            <div class="col"><strong>Candidate</strong><br>' . htmlspecialchars($name) . '</div>
            <div class="col"><strong>Company</strong><br>' . $company . '</div>
            <div class="col"><strong>Designation</strong><br>' . $designation . '</div>
          </div>
          <div class="row">
            <div class="col"><strong>Period</strong><br>' . $period . '</div>
            <div class="col"><strong>Verification Date</strong><br>' . $verDate . '</div>
            <div class="col"><strong>Method</strong><br>' . $method . '</div>
          </div>
          <div style="margin-top:12px">
            <span class="badge">Risk: ' . $riskCat . '</span>
            <span class="badge">Status: ' . $finalStatus . '</span>
          </div>
          <table>
            <thead><tr><th>Check</th><th>Status</th></tr></thead>
            <tbody>
              <tr><td>Level 1: Documents</td><td>' . htmlspecialchars((string)($er['status_level1'] ?? 'pending')) . '</td></tr>
              <tr><td>Level 2: HR</td><td>' . htmlspecialchars((string)($er['status_level2'] ?? 'pending')) . '</td></tr>
              <tr><td>Level 3: Advanced</td><td>' . htmlspecialchars((string)($er['status_level3'] ?? 'pending')) . '</td></tr>
            </tbody>
          </table>
          <div class="meta" style="margin-top:10px">© ' . date('Y') . ' ' . $verBy . '</div>
        </div>
        </body></html>';
    }
}
