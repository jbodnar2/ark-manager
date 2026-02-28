<?php
declare(strict_types=1);

/**
 * database-operations.php
 */

/* -------------------------
    CORE & CONNECTION
   ------------------------- */

function openSqlitePdo(string $dbFile = __DIR__ . '/database/arks.sqlite'): PDO
{
    $dsn = 'sqlite:' . $dbFile;
    $db = new PDO($dsn, '', '', [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);

    $db->exec('PRAGMA foreign_keys = ON;');
    $db->exec('PRAGMA journal_mode = WAL;');

    return $db;
}

/* -------------------------
    VALIDATION HELPERS
   ------------------------- */

/**
 * Validates the assigned_name (blade) against schema constraints.
 */
function isValidAssignedName(string $name): bool
{
    if (empty($name)) {
        return false;
    }
    return (bool) preg_match('/^[A-Za-z0-9._-]+$/', $name);
}

/**
 * Validates the NAAN against schema constraints.
 */
function isValidNaan(string $naan): bool
{
    return (bool) preg_match('/^[0-0]{5}$/', $naan);
}

/* -------------------------
    USER MANAGEMENT
   ------------------------- */

function createUser(
    PDO $db,
    string $u,
    string $fn,
    string $ln,
    string $e,
    string $p,
    string $r = 'user',
): int {
    $sql = 'INSERT INTO users (username, first_name, last_name, email, password_hash, role)
            VALUES (:u, :fn, :ln, :e, :p, :r)';
    $db->prepare($sql)->execute([
        ':u' => $u,
        ':fn' => $fn,
        ':ln' => $ln,
        ':e' => $e,
        ':p' => $p,
        ':r' => $r,
    ]);
    return (int) $db->lastInsertId();
}

function getUser(PDO $db, int $id): ?array
{
    $stmt = $db->prepare('SELECT * FROM users WHERE id = :id');
    $stmt->execute([':id' => $id]);
    return $stmt->fetch() ?: null;
}

function searchUsers(PDO $db, string $term): array
{
    $sql = 'SELECT id, username, first_name, last_name, email, role
            FROM users
            WHERE username LIKE :q OR email LIKE :q OR last_name LIKE :q';
    $stmt = $db->prepare($sql);
    $stmt->execute([':q' => "%$term%"]);
    return $stmt->fetchAll();
}

function setUserStatus(PDO $db, int $userId, bool $active): void
{
    $role = $active ? 'user' : 'inactive';
    $da = $active ? null : date('Y-m-d H:i:s');
    $sql = 'UPDATE users SET role = :role, deactivated_at = :da WHERE id = :id';
    $db->prepare($sql)->execute([
        ':role' => $role,
        ':da' => $da,
        ':id' => $userId,
    ]);
}

/* -------------------------
    NAAN & SHOULDER MANAGEMENT
   ------------------------- */

function createNaan(PDO $db, string $v, string $l, int $cb): int
{
    if (!isValidNaan($v)) {
        throw new InvalidArgumentException('NAAN must be exactly 5 digits.');
    }
    $sql =
        'INSERT INTO naans (naan, naa_name, created_by) VALUES (:v, :l, :cb)';
    $db->prepare($sql)->execute([':v' => $v, ':l' => $l, ':cb' => $cb]);
    return (int) $db->lastInsertId();
}

function createShoulder(PDO $db, int $nid, string $s, string $l, int $cb): int
{
    $sql = 'INSERT INTO shoulders (naan_id, shoulder, shoulder_name, created_by)
            VALUES (:nid, :s, :l, :cb)';
    $db->prepare($sql)->execute([
        ':nid' => $nid,
        ':s' => $s,
        ':l' => $l,
        ':cb' => $cb,
    ]);
    return (int) $db->lastInsertId();
}

function listShoulders(PDO $db): array
{
    $sql = 'SELECT s.id, n.naan, s.shoulder, s.shoulder_name, n.naa_name as name_authority
            FROM shoulders s
            JOIN naans n ON s.naan_id = n.id
            ORDER BY n.naan, s.shoulder';
    return $db->query($sql)->fetchAll();
}

/* -------------------------
    ARK LIFECYCLE MANAGEMENT
   ------------------------- */

function createArk(
    PDO $db,
    int $sid,
    string $b,
    string $fa,
    string $t,
    int $cb,
    string $s = 'reserved',
    ?string $url = null,
    ?string $c = null,
    ?string $n = null,
    ?string $rn = null,
): int {
    if (!isValidAssignedName($b)) {
        throw new InvalidArgumentException(
            'Assigned name contains illegal characters.',
        );
    }

    $sql = 'INSERT INTO arks (shoulder_id, assigned_name, full_ark, title, target_url, creator, state, note, reserved_note, created_by)
            VALUES (:sid, :b, :fa, :t, :url, :c, :s, :n, :rn, :cb)';
    $db->prepare($sql)->execute([
        ':sid' => $sid,
        ':b' => $b,
        ':fa' => $fa,
        ':t' => $t,
        ':url' => $url,
        ':c' => $c,
        ':s' => $s,
        ':n' => $n,
        ':rn' => $rn,
        ':cb' => $cb,
    ]);
    return (int) $db->lastInsertId();
}

function updateArk(PDO $db, int $id, int $uid, array $fields): void
{
    $allowed = [
        'title',
        'target_url',
        'creator',
        'local_id',
        'relation',
        'note',
        'state',
        'reserved_note',
    ];
    $set = [];
    $params = [':id' => $id, ':uid' => $uid];
    foreach ($fields as $k => $v) {
        if (in_array($k, $allowed, true)) {
            $set[] = "{$k} = :{$k}";
            $params[":{$k}"] = $v;
        }
    }
    if (empty($set)) {
        return;
    }
    $sql =
        'UPDATE arks SET ' .
        implode(', ', $set) .
        ', updated_by = :uid, updated_at = CURRENT_TIMESTAMP WHERE id = :id';
    $db->prepare($sql)->execute($params);
}

function publishReservedArk(PDO $db, int $id, string $url, int $uid): bool
{
    try {
        $db->beginTransaction();
        $sql = 'UPDATE arks SET state = "active", target_url = :url, updated_by = :uid, updated_at = CURRENT_TIMESTAMP
                WHERE id = :id AND state = "reserved"';
        $stmt = $db->prepare($sql);
        $stmt->execute([':url' => $url, ':uid' => $uid, ':id' => $id]);
        if ($stmt->rowCount() === 0) {
            $db->rollBack();
            return false;
        }
        $db->commit();
        return true;
    } catch (Exception $e) {
        $db->rollBack();
        throw $e;
    }
}

function withdrawArk(
    PDO $db,
    int $id,
    string $code,
    string $note,
    int $uid,
): void {
    $sql = 'UPDATE arks SET state = "withdrawn", withdrawal_code = :c, withdrawal_note = :n,
            updated_by = :u, updated_at = CURRENT_TIMESTAMP WHERE id = :id';
    $db->prepare($sql)->execute([
        ':c' => $code,
        ':n' => $note,
        ':u' => $uid,
        ':id' => $id,
    ]);
}

function searchArks(PDO $db, string $term): array
{
    $sql = 'SELECT a.*, n.naan, s.shoulder, n.naa_name as name_authority
            FROM arks a
            JOIN shoulders s ON a.shoulder_id = s.id
            JOIN naans n ON s.naan_id = n.id
            WHERE a.full_ark LIKE :q OR a.title LIKE :q OR a.creator LIKE :q
            LIMIT 50';
    $stmt = $db->prepare($sql);
    $stmt->execute([':q' => "%$term%"]);
    return $stmt->fetchAll();
}

function listReservedArks(PDO $db): array
{
    $sql = 'SELECT id, full_ark, title, reserved_note, created_at
            FROM arks
            WHERE state = "reserved"
            ORDER BY created_at DESC';
    return $db->query($sql)->fetchAll();
}

/* -------------------------
    REPORTING & ANALYTICS
   ------------------------- */

function logArkHit(PDO $db, int $id): void
{
    $ym = date('Y-m');
    $sql = 'INSERT INTO ark_analytics_monthly (ark_id, year_month, hit_count)
            VALUES (:id, :ym, 1)
            ON CONFLICT(ark_id, year_month) DO UPDATE SET hit_count = hit_count + 1';
    $db->prepare($sql)->execute([':id' => $id, ':ym' => $ym]);
}

function getSystemSummary(PDO $db): array
{
    $sql = "SELECT
                (SELECT COUNT(*) FROM arks) as total_arks,
                (SELECT COUNT(*) FROM arks WHERE state = 'active') as active_arks,
                (SELECT COUNT(*) FROM arks WHERE state = 'reserved') as reserved_arks,
                (SELECT COUNT(*) FROM arks WHERE state = 'withdrawn') as withdrawn_arks,
                (SELECT IFNULL(SUM(hit_count), 0) FROM ark_analytics_monthly) as total_hits";
    return $db->query($sql)->fetch();
}

function getAuditReport(PDO $db, ?int $id = null): array
{
    $query = "SELECT changed_at, changed_by_name, 'ARK ID: ' || ark_id AS identifier,
                GROUP_CONCAT('* ' || UPPER(field_name) || ': ' || IFNULL(old_value, '[NULL]') || ' to ' || IFNULL(new_value, '[NULL]'), CHAR(10)) AS change_list
              FROM arks_audit_log ";
    if ($id) {
        $query .= ' WHERE ark_id = :id ';
    }
    $query .=
        ' GROUP BY ark_id, changed_at, changed_by_name ORDER BY changed_at DESC';
    $stmt = $db->prepare($query);
    if ($id) {
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
    }
    $stmt->execute();
    return $stmt->fetchAll();
}

function getTopArksReport(PDO $db, int $limit = 10): array
{
    $sql = 'SELECT a.full_ark, a.title, SUM(m.hit_count) as total_hits
            FROM arks a
            JOIN ark_analytics_monthly m ON a.id = m.ark_id
            GROUP BY a.id ORDER BY total_hits DESC LIMIT :limit';
    $stmt = $db->prepare($sql);
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll();
}
