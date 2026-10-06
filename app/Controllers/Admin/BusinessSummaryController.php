<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Core\Request;
use App\Core\Response;
use App\Services\Admin\BusinessSummary;

/** GET /admin/business-summary – registrations and payments received by use case. */
class BusinessSummaryController extends BaseController
{
    public function index(Request $request, Response $response): void
    {
        if (!$this->currentUser || !$this->currentUser->isAdmin()) {
            $response->redirect('/admin/login');
            return;
        }
        $period = (string)$request->get('period', '30d');
        $period = isset(BusinessSummary::PERIODS[$period]) ? $period : '30d';
        $fromIn = (string)$request->get('from', '');
        $toIn = (string)$request->get('to', '');
        [$from, $to] = BusinessSummary::range($period, $fromIn, $toIn);

        $groups = BusinessSummary::grouped(BusinessSummary::registrations($from, $to));
        $subs = BusinessSummary::subscriptions($from, $to);
        $totals = ['registered' => 0, 'completed' => 0, 'pending' => 0, 'payments' => 0, 'inr' => 0.0, 'gst' => 0.0, 'usd' => 0.0];
        foreach ($groups as $g) {
            foreach ($totals as $k => $v) {
                $totals[$k] = $v + $g['sum'][$k];
            }
        }
        $subsInr = 0.0;
        foreach ($subs as $s) {
            if (($s['currency'] ?? 'INR') !== 'USD') {
                $subsInr += (float)$s['amount'];
            }
        }

        $response->view('admin/business-summary', [
            'title' => 'Registrations & payments by use case',
            'period' => $period,
            'from' => $fromIn,
            'to' => $toIn,
            'rangeFrom' => $from,
            'rangeTo' => $to,
            'groups' => $groups,
            'totals' => $totals,
            'accounts' => BusinessSummary::accounts($from, $to),
            'subs' => $subs,
            'subsInr' => $subsInr,
            'daily' => BusinessSummary::daily(30),
        ], 200, 'admin/layout');
    }
}
