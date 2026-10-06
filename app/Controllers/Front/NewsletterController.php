<?php

declare(strict_types=1);

namespace App\Controllers\Front;

use App\Controllers\BaseController;
use App\Core\Request;
use App\Core\Response;
use App\Services\NewsletterService;

/** GET /newsletter/unsubscribe?e=…&t=… – one-click opt-out from the monthly newsletter. */
class NewsletterController extends BaseController
{
    public function unsubscribe(Request $request, Response $response): void
    {
        $ok = NewsletterService::unsubscribe((string)$request->get('e', ''), (string)$request->get('t', ''));
        $msg = $ok
            ? 'आपको अब Jobsence का मासिक न्यूज़लेटर नहीं भेजा जाएगा। / You will no longer receive the Jobsence monthly newsletter.'
            : 'यह लिंक मान्य नहीं है – gm@jobsence.com पर लिखें। / This link is not valid – write to gm@jobsence.com.';
        $response->setStatusCode($ok ? 200 : 400);
        $response->setHeader('Content-Type', 'text/html; charset=utf-8');
        $response->setBody('<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><meta name="robots" content="noindex">'
            . '<title>Newsletter – Jobsence</title></head><body style="font-family:Arial,sans-serif;max-width:560px;margin:60px auto;padding:0 16px;color:#111827">'
            . '<p><a href="/"><img src="/uploads/jobsence.png" alt="Jobsence" style="height:48px"></a></p><p style="font-size:17px">' . htmlspecialchars($msg, ENT_QUOTES, 'UTF-8') . '</p>'
            . '<p><a href="/">← Jobsence</a></p></body></html>');
    }
}
