PRAGMA foreign_keys = ON;

PRAGMA journal_mode = WAL;

-- =============================================================================
-- TABLES
-- =============================================================================
-- 1. Users
CREATE TABLE IF NOT EXISTS users (
    id INTEGER PRIMARY KEY,
    username TEXT NOT NULL UNIQUE COLLATE NOCASE,
    first_name TEXT NOT NULL,
    last_name TEXT NOT NULL,
    email TEXT NOT NULL UNIQUE COLLATE NOCASE,
    password_hash TEXT NOT NULL,
    role TEXT NOT NULL DEFAULT 'user' CHECK (role IN ('admin', 'user', 'viewer', 'inactive')),
    deactivated_at TEXT DEFAULT NULL,
    created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- 2. NAANs (Name Assigning Authority Numbers)
CREATE TABLE IF NOT EXISTS naans (
    id INTEGER PRIMARY KEY,
    naan TEXT NOT NULL UNIQUE CHECK (
        length (naan) = 5
        AND naan NOT GLOB '*[^0-9]*'
    ),
    naa_name TEXT NOT NULL,
    created_by INTEGER NOT NULL,
    created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users (id) ON DELETE RESTRICT
);

-- 3. Shoulders
CREATE TABLE IF NOT EXISTS shoulders (
    id INTEGER PRIMARY KEY,
    naan_id INTEGER NOT NULL,
    shoulder TEXT NOT NULL CHECK (
        length (shoulder) > 0
        AND shoulder NOT GLOB '*[^a-z0-9]*'
    ),
    shoulder_name TEXT NOT NULL,
    created_by INTEGER NOT NULL,
    created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (naan_id) REFERENCES naans (id) ON DELETE RESTRICT,
    FOREIGN KEY (created_by) REFERENCES users (id) ON DELETE RESTRICT,
    UNIQUE (naan_id, shoulder)
);

-- 4. ARKs
CREATE TABLE IF NOT EXISTS arks (
    id INTEGER PRIMARY KEY,
    shoulder_id INTEGER NOT NULL,
    assigned_name TEXT NOT NULL CHECK (
        length (assigned_name) > 0
        AND assigned_name NOT GLOB '*[^A-Za-z0-9._-]*'
    ),
    full_ark TEXT NOT NULL UNIQUE COLLATE NOCASE,
    title TEXT NOT NULL,
    target_url TEXT,
    creator TEXT,
    local_id TEXT,
    relation TEXT,
    state TEXT NOT NULL DEFAULT 'reserved' CHECK (
        state IN (
            'active',
            'inactive',
            'reserved',
            'deleted',
            'withdrawn'
        )
    ),
    note TEXT,
    reserved_note TEXT DEFAULT NULL,
    withdrawal_note TEXT DEFAULT NULL,
    withdrawal_code TEXT DEFAULT NULL CHECK (
        withdrawal_code IN (
            'permanent',
            'deleted',
            'restricted',
            'private',
            'embargo',
            'legal',
            'takedown',
            'temporary',
            'under_review',
            'hidden'
        )
    ),
    deleted_at TEXT DEFAULT NULL,
    created_by INTEGER NOT NULL,
    updated_by INTEGER DEFAULT NULL,
    last_http_status INTEGER,
    last_checked_at TEXT DEFAULT NULL,
    created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TEXT DEFAULT NULL,
    FOREIGN KEY (shoulder_id) REFERENCES shoulders (id) ON DELETE RESTRICT,
    FOREIGN KEY (created_by) REFERENCES users (id) ON DELETE RESTRICT,
    FOREIGN KEY (updated_by) REFERENCES users (id) ON DELETE RESTRICT
);

-- 5. Monthly Analytics
CREATE TABLE IF NOT EXISTS ark_analytics_monthly (
    ark_id INTEGER NOT NULL,
    year_month TEXT NOT NULL,
    hit_count INTEGER DEFAULT 0,
    PRIMARY KEY (ark_id, year_month),
    FOREIGN KEY (ark_id) REFERENCES arks (id) ON DELETE CASCADE
);

-- 6. Audit Log
CREATE TABLE IF NOT EXISTS arks_audit_log (
    id INTEGER PRIMARY KEY,
    ark_id INTEGER NOT NULL,
    action TEXT NOT NULL CHECK (action IN ('INSERT', 'UPDATE', 'DELETE')),
    field_name TEXT,
    old_value TEXT,
    new_value TEXT,
    changed_by INTEGER DEFAULT NULL,
    changed_by_name TEXT,
    changed_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (ark_id) REFERENCES arks (id) ON DELETE CASCADE,
    FOREIGN KEY (changed_by) REFERENCES users (id) ON DELETE SET NULL
);

-- =============================================================================
-- INDEXES
-- =============================================================================
CREATE INDEX IF NOT EXISTS idx_shoulders_naan_id ON shoulders (naan_id);

CREATE UNIQUE INDEX IF NOT EXISTS uidx_arks_full_ark ON arks (full_ark COLLATE NOCASE);

CREATE UNIQUE INDEX IF NOT EXISTS uidx_arks_shoulder_assigned_name ON arks (shoulder_id, assigned_name);

CREATE INDEX IF NOT EXISTS idx_analytics_monthly_ark_id ON ark_analytics_monthly (ark_id);

CREATE INDEX IF NOT EXISTS idx_arks_state ON arks (state);

CREATE INDEX IF NOT EXISTS idx_audit_ark_id ON arks_audit_log (ark_id);

-- =============================================================================
-- TRIGGERS
-- =============================================================================
-- 1. State transition validations
CREATE TRIGGER IF NOT EXISTS arks_clear_reserved_note_after_unreserve AFTER
UPDATE OF state ON arks WHEN OLD.state = 'reserved'
AND NEW.state != 'reserved' BEGIN
UPDATE arks
SET
    reserved_note = NULL
WHERE
    id = NEW.id;

END;

CREATE TRIGGER IF NOT EXISTS arks_prevent_illegal_deletion BEFORE
UPDATE ON arks WHEN OLD.state IN ('active', 'withdrawn')
AND NEW.state = 'deleted' BEGIN
SELECT
    RAISE (
        ABORT,
        'Active or Withdrawn ARKs cannot be moved to a Deleted state per specification.'
    );

END;

CREATE TRIGGER IF NOT EXISTS arks_require_withdrawal_fields BEFORE
UPDATE ON arks WHEN NEW.state = 'withdrawn'
AND (
    NEW.withdrawal_note IS NULL
    OR NEW.withdrawal_note = ''
    OR NEW.withdrawal_code IS NULL
    OR NEW.withdrawal_code = ''
) BEGIN
SELECT
    RAISE (
        ABORT,
        'A withdrawal_note and withdrawal_code are required when moving to Withdrawn state.'
    );

END;

-- 2. Timestamp management
CREATE TRIGGER IF NOT EXISTS trg_arks_set_updated_at AFTER
UPDATE ON arks BEGIN
UPDATE arks
SET
    updated_at = CURRENT_TIMESTAMP
WHERE
    id = NEW.id;

END;

CREATE TRIGGER IF NOT EXISTS trg_arks_set_deleted_at AFTER
UPDATE OF state ON arks WHEN NEW.state = 'deleted'
AND OLD.state != 'deleted' BEGIN
UPDATE arks
SET
    deleted_at = CURRENT_TIMESTAMP
WHERE
    id = NEW.id;

END;

CREATE TRIGGER IF NOT EXISTS trg_arks_clear_deleted_at AFTER
UPDATE OF state ON arks WHEN OLD.state = 'deleted'
AND NEW.state != 'deleted' BEGIN
UPDATE arks
SET
    deleted_at = NULL
WHERE
    id = NEW.id;

END;

CREATE TRIGGER IF NOT EXISTS trg_arks_clear_withdrawal_fields AFTER
UPDATE OF state ON arks WHEN OLD.state = 'withdrawn'
AND NEW.state != 'withdrawn' BEGIN
UPDATE arks
SET
    withdrawal_note = NULL,
    withdrawal_code = NULL
WHERE
    id = NEW.id;

END;

-- 3. Audit Logging
CREATE TRIGGER IF NOT EXISTS trg_arks_audit_insert AFTER INSERT ON arks BEGIN
INSERT INTO
    arks_audit_log (
        ark_id,
        action,
        new_value,
        changed_by,
        changed_by_name
    )
VALUES
    (
        NEW.id,
        'INSERT',
        NEW.full_ark,
        NEW.created_by,
        (
            SELECT
                first_name || ' ' || last_name
            FROM
                users
            WHERE
                id = NEW.created_by
        )
    );

END;

CREATE TRIGGER IF NOT EXISTS trg_arks_audit_update AFTER
UPDATE ON arks BEGIN
-- Log State
INSERT INTO
    arks_audit_log (
        ark_id,
        action,
        field_name,
        old_value,
        new_value,
        changed_by,
        changed_by_name
    )
SELECT
    NEW.id,
    'UPDATE',
    'state',
    OLD.state,
    NEW.state,
    NEW.updated_by,
    (
        SELECT
            first_name || ' ' || last_name
        FROM
            users
        WHERE
            id = NEW.updated_by
    )
WHERE
    OLD.state <> NEW.state;

-- Log Target URL
INSERT INTO
    arks_audit_log (
        ark_id,
        action,
        field_name,
        old_value,
        new_value,
        changed_by,
        changed_by_name
    )
SELECT
    NEW.id,
    'UPDATE',
    'target_url',
    OLD.target_url,
    NEW.target_url,
    NEW.updated_by,
    (
        SELECT
            first_name || ' ' || last_name
        FROM
            users
        WHERE
            id = NEW.updated_by
    )
WHERE
    (
        OLD.target_url IS NULL
        AND NEW.target_url IS NOT NULL
    )
    OR (
        OLD.target_url IS NOT NULL
        AND NEW.target_url IS NULL
    )
    OR (OLD.target_url <> NEW.target_url);

END;

CREATE TRIGGER IF NOT EXISTS trg_arks_audit_delete AFTER DELETE ON arks BEGIN
INSERT INTO
    arks_audit_log (ark_id, action, old_value, changed_by_name)
VALUES
    (OLD.id, 'DELETE', OLD.full_ark, 'System/Unknown');

END;
