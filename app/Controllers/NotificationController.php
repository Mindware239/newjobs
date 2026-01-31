<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Core\Database;

class NotificationController extends BaseController
{
    /**
     * Track email open (Tracking Pixel)
     * GET /notifications/track/open?id={log_id}&h={hash}
     */
    public function trackOpen(Request $request, Response $response): void
    {
        $id = (int)$request->get('id');
        $hash = $request->get('h');

        if ($id && $hash && $this->verifyHash($id, $hash)) {
            try {
                $db = Database::getInstance();
                $db->query(
                    "UPDATE notification_logs SET opened_at = NOW(), status = 'opened' WHERE id = :id AND opened_at IS NULL",
                    ['id' => $id]
                );
            } catch (\Throwable $e) {
                // Ignore errors
            }
        }

        // Return 1x1 transparent GIF
        header('Content-Type: image/gif');
        echo base64_decode('R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7');
        exit;
    }

    /**
     * Track link click
     * GET /notifications/track/click?id={log_id}&h={hash}&url={target_url}
     */
    public function trackClick(Request $request, Response $response): void
    {
        $id = (int)$request->get('id');
        $hash = $request->get('h');
        $url = $request->get('url');

        if ($id && $hash && $this->verifyHash($id, $hash)) {
            try {
                $db = Database::getInstance();
                $db->query(
                    "UPDATE notification_logs SET clicked_at = NOW(), status = 'clicked' WHERE id = :id",
                    ['id' => $id]
                );
            } catch (\Throwable $e) {
                // Ignore errors
            }
        }

        if ($url) {
            $response->redirect($url);
        } else {
            $response->redirect('/');
        }
    }

    private function verifyHash(int $id, string $hash): bool
    {
        $secret = $_ENV['APP_KEY'] ?? 'secret';
        return hash_hmac('sha256', (string)$id, $secret) === $hash;
    }
}
