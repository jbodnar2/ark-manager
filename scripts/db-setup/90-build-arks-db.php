<?php
declare(strict_types=1);

function fatal(string $message, int $exitCode = 1): void
{
    fwrite(STDERR, $message . PHP_EOL);
    exit($exitCode);
}

try {
    echo 'Running setup-arks-db.php' . PHP_EOL;

    $dbDir = getenv('DB_DIR') ?: __DIR__ . '/database';

    if (!is_dir($dbDir)) {
        if (!mkdir($dbDir, 0755, true) && !is_dir($dbDir)) {
            throw new RuntimeException(
                'Unable to create storage location: ' . $dbDir,
            );
        }
    }

    if (!is_writable($dbDir)) {
        throw new RuntimeException(
            'Storage directory is not writable: ' . $dbDir,
        );
    }

    echo 'Set storage location: ' . $dbDir . DIRECTORY_SEPARATOR . PHP_EOL;

    // $dbFile =
    //     $dbDir . DIRECTORY_SEPARATOR . getenv('DB_NAME') ?: 'db.sqlite';
    // $dsn = 'sqlite:' . $dbFile;

    $dbName = getenv('DB_NAME') ?: 'db.sqlite';
    $dbFile = $dbDir . DIRECTORY_SEPARATOR . $dbName;
    $dsn = 'sqlite:' . $dbFile;

    // Open PDO (SQLite creates file if missing)
    $sqlite = new PDO($dsn, '', '', [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
    echo 'SQLite DB connection opened: ' . $dsn . PHP_EOL;

    // Set and verify journal_mode
    $mode = $sqlite->query('PRAGMA journal_mode = WAL;')->fetchColumn();
    if (strtolower((string) $mode) !== 'wal') {
        throw new RuntimeException(
            'Journal mode not set to WAL; actual: ' . var_export($mode, true),
        );
    }
    echo 'Set PRAGMA journal_mode = ' . $mode . PHP_EOL;

    // Enable foreign keys for this connection and verify
    $sqlite->exec('PRAGMA foreign_keys = ON;');
    $enabled = (int) $sqlite->query('PRAGMA foreign_keys;')->fetchColumn();
    if ($enabled !== 1) {
        throw new RuntimeException(
            'Foreign key support not enabled for this connection',
        );
    }
    echo 'Set PRAGMA foreign_keys = ON' . PHP_EOL;

    // Locate and read the schema file
    $schemaFile = __DIR__ . '/00-schema-arks-db.sql';
    if (!is_file($schemaFile)) {
        throw new RuntimeException('Schema file not found: ' . $schemaFile);
    }

    $schemaSQL = file_get_contents($schemaFile);
    if ($schemaSQL === false || trim($schemaSQL) === '') {
        throw new RuntimeException(
            'Problem reading schema file: ' . $schemaFile,
        );
    }

    // Apply schema atomically
    $sqlite->beginTransaction();
    $sqlite->exec($schemaSQL);
    $sqlite->commit();

    echo 'Database setup complete' . PHP_EOL;

    // Exit success
    exit(0);
} catch (Throwable $e) {
    // Top-level error handler prints to STDERR and returns non-zero exit code
    $msg = sprintf(
        'ERROR: %s in %s on line %d',
        $e->getMessage(),
        $e->getFile(),
        $e->getLine(),
    );
    fwrite(STDERR, $msg . PHP_EOL);
    exit(1);
}
