<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/setup.php';
require_once __DIR__ . '/../includes/Router.php';

if (!in_array($_SERVER['REQUEST_METHOD'], ['GET', 'HEAD'], true)) {
    http_response_code(405);
    header('Content-Type: text/plain; charset=utf-8');
    exit('Method Not Allowed');
}

header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('Referrer-Policy: no-referrer');

$root = $config['app']['root'];
$db_file =
    $root .
    DIRECTORY_SEPARATOR .
    $config['db']['dir'] .
    DIRECTORY_SEPARATOR .
    $config['db']['name'];
$public_reserved = $config['arks']['public_reserved'];
$analytics_enabled = $config['analytics']['enabled'];
$analytics_exclude_ips_singles = $config['analytics']['exclude_ips_singles'];

try {
    $db = new PDO('sqlite:' . $db_file);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $db->setAttribute(PDO::ATTR_TIMEOUT, 5);
    $db->exec('PRAGMA journal_mode = WAL');
} catch (PDOException $e) {
    error_log('Resolver DB Connection Failed: ' . $e->getMessage());
    http_response_code(503);
    exit('Service unavailable');
}

$requested_ark = Router::getCleanPath($_SERVER['REQUEST_URI'] ?? '');

if ($requested_ark === null || $requested_ark === '') {
    http_response_code(400);
    exit('Invalid ARK Specified');
}

try {
    $sql = 'SELECT a.*, n.naan, n.naa_name AS name_authority, s.shoulder_name, s.shoulder
            FROM arks a
            LEFT JOIN shoulders s ON a.shoulder_id = s.id
            LEFT JOIN naans n ON s.naan_id = n.id
            WHERE a.full_ark = :ark LIMIT 1';
    $stmt = $db->prepare($sql);
    $stmt->execute([':ark' => $requested_ark]);
    $ark = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log('Resolver Query Failed: ' . $e->getMessage());
    http_response_code(500);
    exit('Internal Server Failure');
}

if (!$ark) {
    http_response_code(404);
    exit('ARK Not Found');
}

// 1. Log Analytics
if ($analytics_enabled) {
    if (
        !in_array($_SERVER['REMOTE_ADDR'] ?? '', $analytics_exclude_ips_singles)
    ) {
        try {
            $yearMonth = date('Y-m');
            $sql = "INSERT INTO ark_analytics_monthly (ark_id, year_month, hit_count)
                    VALUES (:id, :ym, 1)
                    ON CONFLICT(ark_id, year_month) DO UPDATE SET hit_count = hit_count + 1";

            $insert = $db->prepare($sql);
            $insert->execute([':id' => $ark['id'], ':ym' => $yearMonth]);
        } catch (PDOException $e) {
            error_log('Analytics Log Failure: ' . $e->getMessage());
        }
    }
}

// 2. Determine State
$ark_state = strtolower((string) ($ark['state'] ?? 'unknown'));
$ark_target_url = filter_var($ark['target_url'] ?? '', FILTER_VALIDATE_URL);

// 3. Construct metadata, handle withdrawn
if ($ark_state === 'withdrawn' || isset($_GET['info'])) {
    $info = [
        'ark' => $ark['full_ark'],
        'state' => $ark_state,
        'target' => $ark_target_url ?: null,
        'metadata' => [
            'title' => $ark['title'],
            'creator' => $ark['creator'],
            'local_id' => $ark['local_id'],
            'relation' => $ark['relation'],
        ],
        'commitment' => [
            'naan' => $ark['naan'],
            'name_authority' => $ark['name_authority'],
            'shoulder_label' => $ark['shoulder_name'],
        ],
        'dates' => [
            'created' => $ark['created_at'],
            'updated' => $ark['updated_at'],
        ],
    ];

    $status = 200;
    if ($ark_state === 'withdrawn') {
        $status_codes = [
            'permanent' => 410,
            'deleted' => 410,
            'restricted' => 403,
            'private' => 403,
            'legal' => 451,
            'takedown' => 451,
            'temporary' => 503,
            'under_review' => 503,
        ];
        $status = $status_codes[$ark['withdrawal_code']] ?? 410;
        $info['withdrawal'] = [
            'code' => $ark['withdrawal_code'],
            'status' => $status,
            'note' => $ark['withdrawal_note'],
        ];
    }

    header('Content-Type: application/json; charset=utf-8');
    http_response_code($status);
    exit(json_encode($info, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
}

// 3.5. Optional - public reserved-state ark info
if ($public_reserved && $ark_state === 'reserved') {
    $info = [
        'ark' => $ark['full_ark'],
        'state' => 'reserved',
        'reserved_note' => $ark['reserved_note'],
        'commitment' => [
            'naan' => $ark['naan'],
            'name_authority' => $ark['name_authority'],
            'shoulder' => $ark['shoulder'],
            'shoulder_name' => $ark['shoulder_name'],
        ],
    ];

    header('Content-Type: application/json; charset=utf-8');
    http_response_code(202);
    exit(json_encode($info, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
}

// 4. Redirect active-state ARKs
if ($ark_state === 'active' && $ark_target_url) {
    if (preg_match('/[\r\n]/', $ark_target_url) === 0) {
        header('Location: ' . trim($ark_target_url), true, 302);
        exit('Redirecting');
    }
}

// 5. Final Fallback
http_response_code(404);
exit('ARK Unavailable');
