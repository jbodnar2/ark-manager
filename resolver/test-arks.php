<?php
/*
 * test-arks.php
 * Bare HTML version aligned with revised schema
 */

declare(strict_types=1);

// Configuration
$resolver_base_url = 'https://resolver.test/';
$max_hops = 15;
$per_request_timeout = 10;

// Locate DB
$projectRoot = dirname(__DIR__);
$dbDir = getenv('DB_DIR') ?: 'data';
$dbName = getenv('DB_NAME') ?: 'arks.sqlite';
$dbFile =
    $projectRoot . DIRECTORY_SEPARATOR . $dbDir . DIRECTORY_SEPARATOR . $dbName;

// Helpers --------------------------------------------------------------------

function h(string $s): string
{
    return htmlspecialchars($s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function resolve_url(string $base, string $relative): string
{
    $relative = trim($relative);
    if (parse_url($relative, PHP_URL_SCHEME) !== null) {
        return $relative;
    }

    $baseParts = parse_url($base);
    $scheme = $baseParts['scheme'] ?? 'http';
    $host = $baseParts['host'] ?? '';
    $port = isset($baseParts['port']) ? ':' . $baseParts['port'] : '';
    $basePath = $baseParts['path'] ?? '/';

    if (strpos($relative, '//') === 0) {
        return $scheme . ':' . $relative;
    }

    if (strpos($relative, '/') === 0) {
        return $scheme . '://' . $host . $port . $relative;
    }

    $dir = rtrim(dirname($basePath), '/') . '/';
    $abs = $scheme . '://' . $host . $port . $dir . $relative;

    $parts = parse_url($abs);
    $path = $parts['path'] ?? '';
    $segments = explode('/', $path);
    $resolved = [];
    foreach ($segments as $seg) {
        if ($seg === '' || $seg === '.') {
            continue;
        }
        if ($seg === '..') {
            array_pop($resolved);
            continue;
        }
        $resolved[] = $seg;
    }
    return $scheme . '://' . $host . $port . '/' . implode('/', $resolved);
}

function request_headers(
    string $url,
    bool $use_head = true,
    int $timeout = 10,
): array {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HEADER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
    curl_setopt($ch, CURLOPT_NOBODY, (bool) $use_head);

    $raw = curl_exec($ch);
    $info = curl_getinfo($ch);
    $status = (int) ($info['http_code'] ?? 0);
    $header_text = $raw ?: '';

    if ($use_head && $status === 405) {
        curl_setopt($ch, CURLOPT_NOBODY, false);
        curl_setopt($ch, CURLOPT_HTTPGET, true);
        $raw = curl_exec($ch);
        $info = curl_getinfo($ch);
        $status = (int) ($info['http_code'] ?? 0);
        $header_text = $raw ?: '';
    }

    curl_close($ch);

    $headers = [];
    if ($header_text !== '') {
        $header_text = preg_replace("/\r\n/", "\n", $header_text);
        $lines = explode("\n", $header_text);
        foreach ($lines as $line) {
            if (strpos($line, ':') !== false) {
                [$k, $v] = explode(':', $line, 2);
                $headers[strtolower(trim($k))] = trim($v);
            }
        }
    }

    return [
        'status' => $status,
        'headers' => $headers,
        'raw_headers' => $header_text,
        'total_time' => (float) ($info['total_time'] ?? 0.0),
    ];
}

function fetch_hops(string $start, int $max_hops = 10, int $timeout = 10): array
{
    $hops = [];
    $current = $start;
    $total_time = 0.0;

    for ($i = 0; $i < $max_hops; $i++) {
        $res = request_headers($current, true, $timeout);
        if (
            ($res['status'] === 0 || $res['status'] === 405) &&
            $res['status'] !== 200
        ) {
            $res = request_headers($current, false, $timeout);
        }

        $status = $res['status'];
        $location = $res['headers']['location'] ?? null;
        $time = $res['total_time'];
        $total_time += $time;

        $hops[] = [
            'url' => $current,
            'status' => $status,
            'location' => $location,
            'time' => $time,
            'raw_headers' => $res['raw_headers'],
        ];

        if ($status >= 300 && $status < 400 && $location) {
            $next = resolve_url($current, $location);
            if ($next === $current) {
                break;
            }
            $current = $next;
            continue;
        }
        break;
    }

    return ['hops' => $hops, 'total_time' => $total_time];
}

function determine_expected_status(array $ark): int
{
    $state = strtolower((string) ($ark['state'] ?? ''));

    if ($state === 'active') {
        return 200;
    }

    if ($state === 'reserved') {
        return 202;
    }

    if ($state === 'withdrawn') {
        switch ($ark['withdrawal_code']) {
            case 'permanent':
            case 'deleted':
                return 410;
            case 'restricted':
            case 'private':
                return 403;
            case 'legal':
            case 'takedown':
                return 451;
            case 'temporary':
            case 'under_review':
                return 503;
            default:
                return 410;
        }
    }

    return 404;
}

// Fetch ARKs from DB ---------------------------------------------------------

$rows = [];
$dbError = null;

if (!file_exists($dbFile)) {
    $dbError = 'Database file not found: ' . h($dbFile);
} else {
    try {
        $db = new PDO('sqlite:' . $dbFile);
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $db->exec('PRAGMA journal_mode = WAL');

        $sql = 'SELECT full_ark, state, withdrawal_code FROM arks';
        $stmt = $db->query($sql);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $dbError = 'DB connection failed: ' . h($e->getMessage());
    }
}

// Render HTML ---------------------------------------------------------------
?><!doctype html>
<html lang="en">
<head>
<meta charset="utf-8" />
<title>ARK Resolver Tests</title>
<link rel="shortcut icon" href="https://manager.test/assets/img/trees.svg" type="image/x-icon">
	<link rel="stylesheet" href="https://manager.test/assets/css/main.css">

</head>
<body>
<h1>ARK Resolver Tests</h1>
<p>Resolver: <?php echo h($resolver_base_url); ?></p>

<?php if ($dbError): ?>
    <p><?php echo $dbError; ?></p>
<?php else: ?>
    <table>
        <thead>
            <tr>
                <th>ARK</th>
                <th>State</th>
                <th>Expected</th>
                <th>Result</th>
                <th>Actual</th>
                <th>Time</th>
                <th>Hops</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($rows as $row):

            $ark_path = $row['full_ark'];
            $expected = determine_expected_status($row);
            $test_url =
                rtrim($resolver_base_url, '/') . '/' . ltrim($ark_path, '/');

            $trace = fetch_hops($test_url, $max_hops, $per_request_timeout);
            $hops = $trace['hops'];
            $lastHop = end($hops);
            $actual = $lastHop['status'] ?? 0;
            $is_pass = $actual === $expected;
            ?>
            <tr>
                <td><?php echo h($ark_path); ?></td>
                <td><?php echo h($row['state']); ?></td>
                <td><?php echo $expected; ?></td>
                <td>
                    <?php echo $is_pass ? 'PASS' : 'FAIL'; ?>
                </td>
                <td><?php echo $actual; ?></td>
                <td><?php echo number_format($trace['total_time'], 3); ?>s</td>
                <td>
                    <details>
                        <summary><?php echo count($hops); ?> hops</summary>
                        <ol>
                        <?php foreach ($hops as $h): ?>
                            <li>
                                <strong><?php echo $h['status']; ?></strong>
                                <?php echo h($h['url']); ?>
                                <details>
                                    <summary>Headers</summary>
                                    <pre><?php echo h(
                                        $h['raw_headers'],
                                    ); ?></pre>
                                </details>
                            </li>
                        <?php endforeach; ?>
                        </ol>
                    </details>
                </td>
            </tr>
        <?php
        endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>
<p>Generated: <?php echo date('Y-m-d H:i:s'); ?></p>
</body>
</html>
