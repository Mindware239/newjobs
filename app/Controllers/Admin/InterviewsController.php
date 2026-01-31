<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Core\Database;
use App\Core\Request;
use App\Core\Response;
use App\Services\JitsiService;

class InterviewsController extends BaseController
{
    public function index(Request $request, Response $response): void
    {
        if (!$this->requireAdmin($request, $response)) {
            return;
        }

        $db = Database::getInstance();

        $status = (string)$request->get('status', 'all');
        $type = (string)$request->get('type', 'all');
        $platform = (string)$request->get('platform', 'all');
        $search = trim((string)$request->get('search', ''));
        $dateFrom = (string)$request->get('date_from', '');
        $dateTo = (string)$request->get('date_to', '');
        $page = max(1, (int)$request->get('page', 1));
        $perPage = max(5, min(50, (int)$request->get('per_page', 20)));

        $conditions = [];
        $params = [];

        if ($status !== 'all') {
            $conditions[] = "i.status = :status";
            $params['status'] = $status;
        }

        if ($type !== 'all') {
            $conditions[] = "i.interview_type = :type";
            $params['type'] = $type;
        }

        if ($search !== '') {
            $conditions[] = "(j.title LIKE :search1 OR e.company_name LIKE :search2 OR u.email LIKE :search3 OR cand.full_name LIKE :search4)";
            $params['search1'] = '%' . $search . '%';
            $params['search2'] = '%' . $search . '%';
            $params['search3'] = '%' . $search . '%';
            $params['search4'] = '%' . $search . '%';
        }

        if ($dateFrom !== '') {
            $conditions[] = "i.scheduled_start >= :date_from";
            $params['date_from'] = $dateFrom . ' 00:00:00';
        }

        if ($dateTo !== '') {
            $conditions[] = "i.scheduled_start <= :date_to";
            $params['date_to'] = $dateTo . ' 23:59:59';
        }

        $where = '';
        if (!empty($conditions)) {
            $where = 'WHERE ' . implode(' AND ', $conditions);
        }

        $countSql = "SELECT COUNT(*) AS total
                FROM interviews i
                INNER JOIN applications a ON a.id = i.application_id
                INNER JOIN jobs j ON j.id = a.job_id
                INNER JOIN employers e ON e.id = i.employer_id
                INNER JOIN users u ON u.id = a.candidate_user_id
                LEFT JOIN candidates cand ON cand.user_id = u.id
                {$where}";
        $total = (int)($db->fetchOne($countSql, $params)['total'] ?? 0);
        $totalPages = max(1, (int)ceil($total / $perPage));
        if ($page > $totalPages) $page = $totalPages;
        $offset = ($page - 1) * $perPage;

        $sql = "SELECT 
                    i.*,
                    j.title AS job_title,
                    e.company_name,
                    u.email AS candidate_email,
                    COALESCE(cand.full_name, u.google_name, u.apple_name, u.email) AS candidate_name
                FROM interviews i
                INNER JOIN applications a ON a.id = i.application_id
                INNER JOIN jobs j ON j.id = a.job_id
                INNER JOIN employers e ON e.id = i.employer_id
                INNER JOIN users u ON u.id = a.candidate_user_id
                LEFT JOIN candidates cand ON cand.user_id = u.id
                {$where}
                ORDER BY i.scheduled_start DESC
                LIMIT {$perPage} OFFSET {$offset}";

        $rows = $db->fetchAll($sql, $params);

        $jitsi = new JitsiService();
        $jitsiDomain = $jitsi->getDomain();

        $weekStart = date('Y-m-d', strtotime('monday this week'));
        $weekEnd = date('Y-m-d', strtotime('sunday this week'));
        $statsSql = "SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN (i.status = 'live' OR (i.status IN ('scheduled','rescheduled') AND i.scheduled_start >= NOW())) THEN 1 ELSE 0 END) as upcoming,
                    SUM(CASE WHEN DATE(i.scheduled_start) = CURDATE() AND i.status IN ('scheduled','rescheduled','live') THEN 1 ELSE 0 END) as today,
                    SUM(CASE WHEN DATE(i.scheduled_start) BETWEEN :week_start AND :week_end AND i.status IN ('scheduled','rescheduled','live') THEN 1 ELSE 0 END) as this_week,
                    SUM(CASE WHEN i.status = 'completed' THEN 1 ELSE 0 END) as completed,
                    SUM(CASE WHEN i.status = 'cancelled' THEN 1 ELSE 0 END) as cancelled
                FROM interviews i
                INNER JOIN applications a ON a.id = i.application_id
                INNER JOIN jobs j ON j.id = a.job_id
                INNER JOIN employers e ON e.id = i.employer_id
                INNER JOIN users u ON u.id = a.candidate_user_id
                LEFT JOIN candidates cand ON cand.user_id = u.id
                {$where}";
        $stats = $db->fetchOne($statsSql, array_merge($params, [
            'week_start' => $weekStart,
            'week_end' => $weekEnd
        ])) ?: [];

        $typeSql = "SELECT i.interview_type AS type, COUNT(*) AS cnt
                FROM interviews i
                INNER JOIN applications a ON a.id = i.application_id
                INNER JOIN jobs j ON j.id = a.job_id
                INNER JOIN employers e ON e.id = i.employer_id
                INNER JOIN users u ON u.id = a.candidate_user_id
                LEFT JOIN candidates cand ON cand.user_id = u.id
                {$where}
                GROUP BY i.interview_type";
        $typeRows = $db->fetchAll($typeSql, $params);
        $typeStats = [
            'video' => 0,
            'phone' => 0,
            'onsite' => 0,
            'telephonic' => 0,
            'other' => 0
        ];
        foreach ($typeRows as $tr) {
            $t = strtolower((string)($tr['type'] ?? ''));
            $c = (int)($tr['cnt'] ?? 0);
            if (isset($typeStats[$t])) {
                $typeStats[$t] = $c;
            } else {
                $typeStats['other'] += $c;
            }
        }

        $detectPlatform = function (array $row) use ($jitsiDomain): string {
            $type = (string)($row['interview_type'] ?? '');
            $link = (string)($row['meeting_link'] ?? '');
            if ($type === 'phone') return 'Phone';
            if ($type === 'onsite') return 'On-site';
            if ($type === 'telephonic') return 'Telephonic';
            if ($type !== 'video') return ucfirst($type ?: 'Other');
            if ($link === '') return 'Jitsi (auto)';
            $host = parse_url($link, PHP_URL_HOST) ?: '';
            $host = strtolower((string)$host);
            if ($host === '' || strpos($link, $jitsiDomain) !== false || strpos($host, 'jit.si') !== false) return 'Jitsi';
            if (strpos($host, 'zoom.us') !== false) return 'Zoom';
            if (strpos($host, 'meet.google.com') !== false) return 'Google Meet';
            if (strpos($host, 'teams.microsoft.com') !== false || strpos($host, 'office.com') !== false) return 'Microsoft Teams';
            return 'Video';
        };

        $now = time();
        foreach ($rows as &$row) {
            $startTs = strtotime((string)$row['scheduled_start']);
            $endTs = strtotime((string)$row['scheduled_end']);

            $row['scheduled_date'] = $startTs ? date('M d, Y', $startTs) : null;
            $row['scheduled_time'] = $startTs ? date('h:i A', $startTs) : null;
            $row['scheduled_end_time'] = $endTs ? date('h:i A', $endTs) : null;

            $row['is_live'] = ($row['status'] === 'live');
            $row['is_upcoming'] = $startTs && $startTs > $now;
            $row['is_past'] = $endTs && $endTs < $now;
            $row['platform_label'] = $detectPlatform($row);
        }
        unset($row);

        if ($platform !== 'all') {
            $rows = array_values(array_filter($rows, function($r) use ($platform) {
                return strtolower($r['platform_label']) === strtolower($platform);
            }));
        }

        // Build platform stats from current rows (post-filter)
        $platformStats = [];
        foreach ($rows as $r) {
            $label = (string)($r['platform_label'] ?? 'Unknown');
            if ($label === '') $label = 'Unknown';
            $platformStats[$label] = ($platformStats[$label] ?? 0) + 1;
        }

        // Determine time window for charts
        $today = date('Y-m-d');
        if ($dateFrom === '' && $dateTo === '') {
            $chartStart = date('Y-m-d', strtotime('-13 days', strtotime($today)));
            $chartEnd = $today;
        } else {
            $chartStart = $dateFrom !== '' ? $dateFrom : date('Y-m-d', strtotime('-13 days', strtotime($today)));
            $chartEnd = $dateTo !== '' ? $dateTo : $today;
        }

        // Interviews over time by status
        $timeseriesSql = "SELECT DATE(i.scheduled_start) AS d,
                    SUM(CASE WHEN i.status = 'scheduled' THEN 1 ELSE 0 END) AS scheduled,
                    SUM(CASE WHEN i.status = 'rescheduled' THEN 1 ELSE 0 END) AS rescheduled,
                    SUM(CASE WHEN i.status = 'live' THEN 1 ELSE 0 END) AS live,
                    SUM(CASE WHEN i.status = 'completed' THEN 1 ELSE 0 END) AS completed,
                    SUM(CASE WHEN i.status = 'cancelled' THEN 1 ELSE 0 END) AS cancelled
                FROM interviews i
                INNER JOIN applications a ON a.id = i.application_id
                INNER JOIN jobs j ON j.id = a.job_id
                INNER JOIN employers e ON e.id = i.employer_id
                INNER JOIN users u ON u.id = a.candidate_user_id
                LEFT JOIN candidates cand ON cand.user_id = u.id
                WHERE " . (empty($conditions) ? "1=1" : implode(' AND ', $conditions)) . " 
                AND DATE(i.scheduled_start) BETWEEN :chart_start AND :chart_end
                GROUP BY DATE(i.scheduled_start)
                ORDER BY d ASC";
        $tsParams = $params;
        $tsParams['chart_start'] = $chartStart;
        $tsParams['chart_end'] = $chartEnd;
        $timeseriesRows = $db->fetchAll($timeseriesSql, $tsParams);
        // Normalize missing dates to zero
        $dateCursor = strtotime($chartStart);
        $dateEndTs = strtotime($chartEnd);
        $timeseries = [];
        $tsIndex = [];
        foreach ($timeseriesRows as $r) {
            $tsIndex[(string)$r['d']] = [
                'scheduled' => (int)$r['scheduled'],
                'rescheduled' => (int)$r['rescheduled'],
                'live' => (int)$r['live'],
                'completed' => (int)$r['completed'],
                'cancelled' => (int)$r['cancelled'],
            ];
        }
        while ($dateCursor <= $dateEndTs) {
            $d = date('Y-m-d', $dateCursor);
            $timeseries[] = [
                'date' => $d,
                'scheduled' => (int)($tsIndex[$d]['scheduled'] ?? 0),
                'rescheduled' => (int)($tsIndex[$d]['rescheduled'] ?? 0),
                'live' => (int)($tsIndex[$d]['live'] ?? 0),
                'completed' => (int)($tsIndex[$d]['completed'] ?? 0),
                'cancelled' => (int)($tsIndex[$d]['cancelled'] ?? 0),
            ];
            $dateCursor = strtotime('+1 day', $dateCursor);
        }

        // Average interview duration per day (in minutes)
        $durationSql = "SELECT DATE(i.scheduled_start) AS d,
                    ROUND(AVG(TIMESTAMPDIFF(MINUTE, i.scheduled_start, i.scheduled_end))) AS avg_minutes
                FROM interviews i
                INNER JOIN applications a ON a.id = i.application_id
                INNER JOIN jobs j ON j.id = a.job_id
                INNER JOIN employers e ON e.id = i.employer_id
                INNER JOIN users u ON u.id = a.candidate_user_id
                LEFT JOIN candidates cand ON cand.user_id = u.id
                WHERE " . (empty($conditions) ? "1=1" : implode(' AND ', $conditions)) . " 
                AND DATE(i.scheduled_start) BETWEEN :chart_start AND :chart_end
                AND i.scheduled_end IS NOT NULL
                GROUP BY DATE(i.scheduled_start)
                ORDER BY d ASC";
        $durationRows = $db->fetchAll($durationSql, $tsParams);
        $durIndex = [];
        foreach ($durationRows as $r) {
            $durIndex[(string)$r['d']] = (int)($r['avg_minutes'] ?? 0);
        }
        $durationSeries = [];
        $dateCursor = strtotime($chartStart);
        while ($dateCursor <= $dateEndTs) {
            $d = date('Y-m-d', $dateCursor);
            $durationSeries[] = [
                'date' => $d,
                'avg_minutes' => (int)($durIndex[$d] ?? 0),
            ];
            $dateCursor = strtotime('+1 day', $dateCursor);
        }

        // KPI trends (last 7 days vs previous 7)
        $sevenStart = date('Y-m-d', strtotime('-6 days', strtotime($today)));
        $prevSevenStart = date('Y-m-d', strtotime('-13 days', strtotime($today)));
        $kpiSql = function(string $statusKey) use ($db, $conditions, $params) {
            $cond = (empty($conditions) ? "1=1" : implode(' AND ', $conditions));
            $sql = "SELECT 
                        SUM(CASE WHEN i.status = :kpi_status1 AND DATE(i.scheduled_start) BETWEEN :start AND :end THEN 1 ELSE 0 END) AS curr,
                        SUM(CASE WHEN i.status = :kpi_status2 AND DATE(i.scheduled_start) BETWEEN :prev_start AND :prev_end THEN 1 ELSE 0 END) AS prev
                    FROM interviews i
                    INNER JOIN applications a ON a.id = i.application_id
                    INNER JOIN jobs j ON j.id = a.job_id
                    INNER JOIN employers e ON e.id = i.employer_id
                    INNER JOIN users u ON u.id = a.candidate_user_id
                    LEFT JOIN candidates cand ON cand.user_id = u.id
                    WHERE {$cond}";
            return [$sql, $params];
        };
        [$compSql, $compParams] = $kpiSql('completed');
        $compData = $db->fetchOne($compSql, array_merge($compParams, [
            'kpi_status1' => 'completed',
            'kpi_status2' => 'completed',
            'start' => $sevenStart,
            'end' => $today,
            'prev_start' => $prevSevenStart,
            'prev_end' => date('Y-m-d', strtotime('-7 days', strtotime($today)))
        ])) ?: ['curr' => 0, 'prev' => 0];
        [$cancelSql, $cancelParams] = $kpiSql('cancelled');
        $cancelData = $db->fetchOne($cancelSql, array_merge($cancelParams, [
            'kpi_status1' => 'cancelled',
            'kpi_status2' => 'cancelled',
            'start' => $sevenStart,
            'end' => $today,
            'prev_start' => $prevSevenStart,
            'prev_end' => date('Y-m-d', strtotime('-7 days', strtotime($today)))
        ])) ?: ['curr' => 0, 'prev' => 0];
        // Live now
        $liveNow = 0;
        foreach ($rows as $r) if (($r['status'] ?? '') === 'live') $liveNow++;
        // Avg duration overall (minutes)
        $avgDurSql = "SELECT ROUND(AVG(TIMESTAMPDIFF(MINUTE, i.scheduled_start, i.scheduled_end))) AS avg_minutes
                      FROM interviews i
                      INNER JOIN applications a ON a.id = i.application_id
                      INNER JOIN jobs j ON j.id = a.job_id
                      INNER JOIN employers e ON e.id = i.employer_id
                      INNER JOIN users u ON u.id = a.candidate_user_id
                      LEFT JOIN candidates cand ON cand.user_id = u.id
                      {$where}";
        $avgDurRow = $db->fetchOne($avgDurSql, $params) ?: ['avg_minutes' => 0];

        // Flagged signals: forced_end events counts and recent list
        $flagCountsSql = "SELECT DATE(created_at) AS d, COUNT(*) AS cnt
                          FROM interview_events
                          WHERE event_type = 'forced_end'
                          GROUP BY DATE(created_at)
                          ORDER BY d ASC
                          LIMIT 30";
        $flagCountsRows = $db->fetchAll($flagCountsSql, []);
        $flagCountIndex = [];
        foreach ($flagCountsRows as $r) $flagCountIndex[(string)$r['d']] = (int)$r['cnt'];
        $flagSeries = [];
        $dateCursor = strtotime($chartStart);
        while ($dateCursor <= $dateEndTs) {
            $d = date('Y-m-d', $dateCursor);
            $flagSeries[] = ['date' => $d, 'count' => (int)($flagCountIndex[$d] ?? 0)];
            $dateCursor = strtotime('+1 day', $dateCursor);
        }
        $flagListSql = "SELECT ie.interview_id, ie.created_at, ie.event_type,
                               i.status, j.title AS job_title, e.company_name
                        FROM interview_events ie
                        INNER JOIN interviews i ON i.id = ie.interview_id
                        INNER JOIN applications a ON a.id = i.application_id
                        INNER JOIN jobs j ON j.id = a.job_id
                        INNER JOIN employers e ON e.id = i.employer_id
                        WHERE ie.event_type = 'forced_end'
                        ORDER BY ie.created_at DESC
                        LIMIT 6";
        $flagList = $db->fetchAll($flagListSql, []);

        // Live snapshot cards
        $liveSnapshot = array_values(array_filter($rows, function($r) {
            return ($r['status'] ?? '') === 'live';
        }));
        $liveSnapshot = array_slice($liveSnapshot, 0, 6);

        $response->view('admin/interviews/index', [
            'title' => 'Interview Control Center',
            'interviews' => $rows,
            'filters' => [
                'status' => $status,
                'type' => $type,
                'search' => $search,
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
                'platform' => $platform,
                'page' => $page,
                'per_page' => $perPage,
            ],
            'pagination' => [
                'page' => $page,
                'perPage' => $perPage,
                'total' => $total,
                'totalPages' => $totalPages
            ],
            'analytics' => [
                'status' => [
                    'total' => (int)($stats['total'] ?? 0),
                    'upcoming' => (int)($stats['upcoming'] ?? 0),
                    'today' => (int)($stats['today'] ?? 0),
                    'this_week' => (int)($stats['this_week'] ?? 0),
                    'completed' => (int)($stats['completed'] ?? 0),
                    'cancelled' => (int)($stats['cancelled'] ?? 0),
                ],
                'types' => $typeStats,
                'platforms' => $platformStats,
                'timeseries' => $timeseries,
                'duration' => $durationSeries,
                'flags' => [
                    'series' => $flagSeries,
                    'list' => $flagList
                ],
                'kpis' => [
                    'live_now' => $liveNow,
                    'avg_duration' => (int)($avgDurRow['avg_minutes'] ?? 0),
                    'completed_7d' => (int)($compData['curr'] ?? 0),
                    'completed_prev7d' => (int)($compData['prev'] ?? 0),
                    'cancelled_7d' => (int)($cancelData['curr'] ?? 0),
                    'cancelled_prev7d' => (int)($cancelData['prev'] ?? 0)
                ],
                'live' => $liveSnapshot
            ],
            'user' => $this->currentUser
        ], 200, 'admin/layout');
    }

    public function show(Request $request, Response $response): void
    {
        if (!$this->requireAdmin($request, $response)) {
            return;
        }

        $id = (int)$request->param('id');
        $db = Database::getInstance();

        $row = $db->fetchOne(
            "SELECT 
                i.*,
                a.candidate_user_id,
                j.title AS job_title,
                j.slug AS job_slug,
                e.company_name,
                COALESCE(c.full_name, u.google_name, u.apple_name, u.email) AS candidate_name
             FROM interviews i
             INNER JOIN applications a ON a.id = i.application_id
             INNER JOIN jobs j ON j.id = a.job_id
             INNER JOIN employers e ON e.id = i.employer_id
             INNER JOIN users u ON u.id = a.candidate_user_id
             LEFT JOIN candidates c ON c.user_id = u.id
             WHERE i.id = :id
             LIMIT 1",
            ['id' => $id]
        );

        if (!$row) {
            $response->redirect('/admin/interviews');
            return;
        }

        $events = $db->fetchAll(
            "SELECT event_type, COUNT(*) AS cnt
             FROM interview_events
             WHERE interview_id = :id
             GROUP BY event_type
             ORDER BY cnt DESC",
            ['id' => $id]
        );

        $timeline = $db->fetchAll(
            "SELECT actor_role, event_type, created_at
             FROM interview_events
             WHERE interview_id = :id
             ORDER BY created_at ASC
             LIMIT 250",
            ['id' => $id]
        );

        $response->view('admin/interviews/show', [
            'title' => 'Interview Details',
            'interview' => $row,
            'events' => $events,
            'timeline' => $timeline,
            'user' => $this->currentUser
        ], 200, 'admin/layout');
    }

    public function logs(Request $request, Response $response): void
    {
        if (!$this->requireAdmin($request, $response)) {
            return;
        }

        $id = (int)$request->param('id');
        $db = Database::getInstance();

        $row = $db->fetchOne(
            "SELECT i.*, j.title AS job_title, e.company_name
             FROM interviews i
             INNER JOIN applications a ON a.id = i.application_id
             INNER JOIN jobs j ON j.id = a.job_id
             INNER JOIN employers e ON e.id = i.employer_id
             WHERE i.id = :id
             LIMIT 1",
            ['id' => $id]
        );

        if (!$row) {
            $response->redirect('/admin/interviews');
            return;
        }

        $logs = $db->fetchAll(
            "SELECT actor_role, event_type, payload, created_at, ip_address, user_agent
             FROM interview_events
             WHERE interview_id = :id
             ORDER BY created_at ASC",
            ['id' => $id]
        );

        $response->view('admin/interviews/logs', [
            'title' => 'Interview Audit Logs',
            'interview' => $row,
            'logs' => $logs,
            'user' => $this->currentUser
        ], 200, 'admin/layout');
    }

    public function metrics(Request $request, Response $response): void
    {
        if (!$this->requireAdmin($request, $response)) {
            return;
        }

        $id = (int)$request->param('id');
        $db = Database::getInstance();

        $row = $db->fetchOne("SELECT status, started_at, ended_at, scheduled_start, scheduled_end FROM interviews WHERE id = :id LIMIT 1", ['id' => $id]);
        if (!$row) {
            $response->json(['error' => 'Interview not found'], 404);
            return;
        }

        $events = $db->fetchAll(
            "SELECT event_type, created_at, payload 
             FROM interview_events
             WHERE interview_id = :id
             ORDER BY created_at ASC",
            ['id' => $id]
        );

        $joins = 0;
        $leaves = 0;
        $screenOn = false;
        $recordingOn = false;
        $disconnects = 0;

        foreach ($events as $evt) {
            $type = (string)$evt['event_type'];
            if ($type === 'participant_joined' || $type === 'user_joined') {
                $joins++;
            } elseif ($type === 'participant_left') {
                $leaves++;
            } elseif ($type === 'screen_share_started') {
                $screenOn = true;
            } elseif ($type === 'screen_share_stopped') {
                $screenOn = false;
            } elseif ($type === 'recording_started') {
                $recordingOn = true;
            } elseif ($type === 'recording_stopped') {
                $recordingOn = false;
            } elseif ($type === 'connection_interrupted' || $type === 'connection_restored') {
                $disconnects++;
            }
        }

        $currentParticipants = max(0, $joins - $leaves);

        $now = time();
        $startedAt = $row['started_at'] ? strtotime((string)$row['started_at']) : null;
        $endedAt = $row['ended_at'] ? strtotime((string)$row['ended_at']) : null;
        $elapsedSec = 0;
        if ($startedAt) {
            $elapsedSec = ($endedAt ?? $now) - $startedAt;
            if ($elapsedSec < 0) $elapsedSec = 0;
        }

        $scheduledStart = $row['scheduled_start'] ? strtotime((string)$row['scheduled_start']) : null;
        $scheduledEnd = $row['scheduled_end'] ? strtotime((string)$row['scheduled_end']) : null;
        $outsideSchedule = false;
        if ($scheduledStart && $scheduledEnd && $startedAt) {
            $outsideSchedule = ($startedAt < ($scheduledStart - 900)) || (($endedAt ?? $now) > ($scheduledEnd + 900)); // 15-min grace
        }

        $risk = 0;
        if ($elapsedSec > 3600) $risk += 30; // > 60 mins
        if ($outsideSchedule) $risk += 20;
        if ($disconnects > 5) $risk += 25;
        if ($screenOn && $elapsedSec > 1800) $risk += 10; // long screen sharing
        if ($currentParticipants > 3) $risk += 15; // unknown participants likely
        if ($risk > 100) $risk = 100;

        $response->json([
            'success' => true,
            'status' => $row['status'],
            'elapsed_seconds' => $elapsedSec,
            'participants' => $currentParticipants,
            'screen_sharing' => $screenOn,
            'recording' => $recordingOn,
            'disconnects' => $disconnects,
            'outside_schedule' => $outsideSchedule,
            'risk_score' => $risk
        ]);
    }

    public function forceEnd(Request $request, Response $response): void
    {
        if (!$this->requireAdmin($request, $response)) {
            return;
        }

        $id = (int)$request->param('id');
        $reason = trim((string)$request->post('reason', ''));

        $db = Database::getInstance();
        $row = $db->fetchOne("SELECT * FROM interviews WHERE id = :id LIMIT 1", ['id' => $id]);
        if (!$row) {
            $response->json(['error' => 'Interview not found'], 404);
            return;
        }

        $db->query(
            "UPDATE interviews 
             SET status = 'completed', ended_at = :ended_at, updated_at = NOW()
             WHERE id = :id",
            [
                'id' => $id,
                'ended_at' => date('Y-m-d H:i:s')
            ]
        );

        $db->query(
            "INSERT INTO interview_events (interview_id, actor_user_id, actor_role, event_type, payload, ip_address, user_agent, created_at)
             VALUES (:interview_id, :actor_user_id, :actor_role, :event_type, :payload, :ip_address, :user_agent, :created_at)",
            [
                'interview_id' => $id,
                'actor_user_id' => (int)$this->currentUser->id,
                'actor_role' => 'admin',
                'event_type' => 'admin_force_end',
                'payload' => json_encode(['reason' => $reason]),
                'ip_address' => $request->ip(),
                'user_agent' => substr($request->userAgent(), 0, 512),
                'created_at' => date('Y-m-d H:i:s')
            ]
        );

        $response->json(['success' => true]);
    }

    public function suspend(Request $request, Response $response): void
    {
        if (!$this->requireAdmin($request, $response)) {
            return;
        }

        $id = (int)$request->param('id');
        $reason = trim((string)$request->post('reason', ''));

        $db = Database::getInstance();
        $row = $db->fetchOne("SELECT * FROM interviews WHERE id = :id LIMIT 1", ['id' => $id]);
        if (!$row) {
            $response->json(['error' => 'Interview not found'], 404);
            return;
        }

        $db->query(
            "UPDATE interviews 
             SET status = 'cancelled', updated_at = NOW()
             WHERE id = :id",
            ['id' => $id]
        );

        $db->query(
            "INSERT INTO interview_events (interview_id, actor_user_id, actor_role, event_type, payload, ip_address, user_agent, created_at)
             VALUES (:interview_id, :actor_user_id, :actor_role, :event_type, :payload, :ip_address, :user_agent, :created_at)",
            [
                'interview_id' => $id,
                'actor_user_id' => (int)$this->currentUser->id,
                'actor_role' => 'admin',
                'event_type' => 'admin_suspend_room',
                'payload' => json_encode(['reason' => $reason]),
                'ip_address' => $request->ip(),
                'user_agent' => substr($request->userAgent(), 0, 512),
                'created_at' => date('Y-m-d H:i:s')
            ]
        );

        $response->json(['success' => true]);
    }

    public function join(Request $request, Response $response): void
    {
        if (!$this->requireAdmin($request, $response)) {
            return;
        }

        $id = (int)$request->param('id');

        $db = Database::getInstance();
        $row = $db->fetchOne(
            "SELECT i.id, i.room_name, i.room_password_enc
             FROM interviews i
             WHERE i.id = :id
             LIMIT 1",
            ['id' => $id]
        );

        if (!$row) {
            $response->json(['error' => 'Interview not found'], 404);
            return;
        }

        $response->json(['success' => true, 'join_url' => '/interviews/' . $id . '/room']);
    }

    public function joinSilent(Request $request, Response $response): void
    {
        if (!$this->requireAdmin($request, $response)) {
            return;
        }

        $id = (int)$request->param('id');

        $db = Database::getInstance();
        $row = $db->fetchOne(
            "SELECT i.id
             FROM interviews i
             WHERE i.id = :id
             LIMIT 1",
            ['id' => $id]
        );

        if (!$row) {
            $response->json(['error' => 'Interview not found'], 404);
            return;
        }

        $response->json(['success' => true, 'join_url' => '/interviews/' . $id . '/room?mode=silent']);
    }

    private function requireAdmin(Request $request, Response $response): bool
    {
        if (!$this->currentUser || !$this->currentUser->isAdmin()) {
            $response->redirect('/admin/login');
            return false;
        }
        return true;
    }
}
