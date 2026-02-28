-- 1. Users
BEGIN TRANSACTION;

INSERT INTO
    users (
        id,
        username,
        first_name,
        last_name,
        email,
        password_hash,
        role,
        deactivated_at,
        created_at
    )
VALUES
    (
        1,
        'admin',
        'Jon',
        'Doe',
        'j.doe@example.com',
        'password',
        'admin',
        NULL,
        '2026-01-10 08:00:00'
    ),
    (
        2,
        'user_jane',
        'Jane',
        'Smith',
        'j.smith@example.com',
        'hash_3b9e2',
        'user',
        NULL,
        '2026-01-11 09:15:00'
    ),
    (
        3,
        'user_alice',
        'Alice',
        'Brown',
        'a.brown@example.com',
        'hash_c7d4e',
        'user',
        NULL,
        '2026-01-12 10:30:00'
    ),
    (
        4,
        'user_robert',
        'Robert',
        'Wilson',
        'r.wilson@example.com',
        'hash_f1a2b',
        'user',
        NULL,
        '2026-01-15 11:45:00'
    ),
    (
        5,
        'viewer_emily',
        'Emily',
        'Davis',
        'e.davis@example.com',
        'hash_e5c8d',
        'viewer',
        NULL,
        '2026-02-01 14:20:00'
    );

COMMIT;

-- 2. NAANs
BEGIN TRANSACTION;

INSERT INTO
    naans (id, naan, naa_name, created_by, created_at)
VALUES
    (
        1,
        '12345',
        'University Library',
        1,
        '2026-01-10 08:30:00'
    ),
    (
        2,
        '67890',
        'National Archives',
        1,
        '2026-01-10 08:35:00'
    );

COMMIT;

-- 3. Shoulders
BEGIN TRANSACTION;

INSERT INTO
    shoulders (
        id,
        naan_id,
        shoulder,
        shoulder_name,
        created_by,
        created_at
    )
VALUES
    (
        1,
        1,
        's1',
        'Digital Collections',
        2,
        '2026-01-11 10:00:00'
    ),
    (
        2,
        2,
        'fk3',
        'University Records',
        2,
        '2026-01-11 10:05:00'
    );

COMMIT;

-- 4. ARKs (Corrected blade -> assigned_name)
BEGIN TRANSACTION;

-- 4.1 Success & Redirect Scenairos
INSERT INTO
    arks (
        id,
        shoulder_id,
        assigned_name,
        full_ark,
        title,
        target_url,
        state,
        created_by,
        note
    )
VALUES
    (
        1,
        1,
        '2837',
        'ark:/12345/s12837',
        'Standard Success',
        'https://httpbin.org/status/200',
        'active',
        1,
        'Tests standard redirect.'
    ),
    (
        2,
        1,
        '9921',
        'ark:/12345/s19921',
        'Redirect Chain',
        'https://httpbin.org/relative-redirect/3',
        'active',
        1,
        'Tests multiple hops.'
    ),
    (
        24,
        2,
        'v1.0_final',
        'ark:/67890/fk3v1.0_final',
        'Character Constraint Test',
        'https://httpbin.org/get',
        'active',
        1,
        'Tests dots and underscores.'
    ),
    (
        5,
        1,
        '8841',
        'ark:/12345/s18841',
        'Timeout Test',
        'https://httpbin.org/delay/5',
        'active',
        1,
        'Tests slow response.'
    );

-- 4.2 Administrative States
INSERT INTO
    arks (
        id,
        shoulder_id,
        assigned_name,
        full_ark,
        title,
        target_url,
        state,
        created_by,
        reserved_note,
        note
    )
VALUES
    (
        20,
        1,
        'res-01',
        'ark:/12345/s1res-01',
        'Reserved Item',
        NULL,
        'reserved',
        1,
        'Holding for pending publication',
        'Blocks redirection.'
    ),
    (
        21,
        1,
        'res-02',
        'ark:/12345/s1res-02',
        'Future Publication',
        NULL,
        'reserved',
        1,
        'Metadata reserved for upcoming journal.',
        'Tests reserved metadata.'
    ),
    (
        22,
        1,
        'in-01',
        'ark:/12345/s1in-01',
        'Temporarily Offline',
        'https://httpbin.org/status/503',
        'inactive',
        1,
        NULL,
        'Tests paused redirect.'
    ),
    (
        4,
        1,
        '7732',
        'ark:/12345/s17732',
        'Deleted Record',
        'https://httpbin.org/status/404',
        'deleted',
        1,
        NULL,
        'Tests deleted state.'
    );

-- 4.3 Withdrawal Logic (Must include note and code per trigger)
INSERT INTO
    arks (
        id,
        shoulder_id,
        assigned_name,
        full_ark,
        title,
        target_url,
        state,
        created_by,
        withdrawal_note,
        withdrawal_code,
        note
    )
VALUES
    (
        101,
        1,
        'w-perm',
        'ark:/12345/s1w-perm',
        'Gone Permanently',
        'http://httpbin.org/get',
        'withdrawn',
        1,
        'Item removed.',
        'permanent',
        'Tests 410 response.'
    ),
    (
        103,
        1,
        'w-restr',
        'ark:/12345/s1w-restr',
        'Restricted Access',
        'http://httpbin.org/get',
        'withdrawn',
        1,
        'Internal users only.',
        'restricted',
        'Tests 403 response.'
    ),
    (
        106,
        2,
        'w-legal',
        'ark:/67890/fk3w-legal',
        'Legal Dispute',
        'http://httpbin.org/get',
        'withdrawn',
        1,
        'Disabled due to litigation.',
        'legal',
        'Tests 451 response.'
    );

-- 4.4 Edge Cases
-- Note: id 111 is inserted as 'inactive' so withdrawal fields can be NULL for the test.
INSERT INTO
    arks (
        id,
        shoulder_id,
        assigned_name,
        full_ark,
        title,
        target_url,
        state,
        created_by,
        withdrawal_note,
        note
    )
VALUES
    (
        111,
        2,
        'w-fallback',
        'ark:/67890/fk3w-fallback',
        'Legacy Withdrawal',
        'http://httpbin.org/get',
        'inactive',
        1,
        'Legacy withdrawal note.',
        'Tests fallback to 410.'
    );

COMMIT;

-- 5. Audit Updates and Logic Tests
BEGIN TRANSACTION;

-- Moving from reserved to active (will trigger trg_arks_set_updated_at and audit logs)
UPDATE arks
SET
    state = 'active',
    target_url = 'https://example.com/item20',
    updated_by = 1
WHERE
    id = 20;

-- Metadata updates
UPDATE arks
SET
    title = 'Updated Success Title',
    creator = 'Principal Investigator',
    updated_by = 2
WHERE
    id = 1;

-- Moving to withdrawn (Trigger will verify withdrawal_note/code are present)
UPDATE arks
SET
    state = 'withdrawn',
    withdrawal_note = 'Privacy concern.',
    withdrawal_code = 'private',
    updated_by = 1
WHERE
    id = 5;

-- URL Change
UPDATE arks
SET
    target_url = 'https://httpbin.org/get?updated=true',
    updated_by = 2
WHERE
    id = 24;

-- Delete
DELETE FROM arks
WHERE
    id = 111;

COMMIT;

-- 6. Analytics (Historical hits)
BEGIN TRANSACTION;

INSERT INTO
    ark_analytics_monthly (ark_id, year_month, hit_count)
VALUES
    (1, '2026-01', 15),
    (1, '2026-02', 6),
    (2, '2026-02', 13),
    (24, '2026-02', 5),
    (5, '2026-02', 1);

COMMIT;
