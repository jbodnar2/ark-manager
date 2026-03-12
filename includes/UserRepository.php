<?php
declare(strict_types=1);

class UserRepository
{
    private PDO $db;

    private const ROLE_ADMIN = 'admin';
    private const ROLE_USER = 'user';
    private const ROLE_VIEWER = 'viewer';
    private const ROLE_INACTIVE = 'inactive';

    private function getAllowedRoles()
    {
        return [
            self::ROLE_ADMIN,
            self::ROLE_USER,
            self::ROLE_VIEWER,
            self::ROLE_INACTIVE,
        ];
    }

    /**
     * Ensures the provided role is valid.
     * * @param string $role The role string to check.
     * @param bool $allowActive Whether the 'active' logical filter is permitted.
     * @throws InvalidArgumentException If the role is not recognized.
     */
    private function validateRole(string $role, bool $allowActive = false): void
    {
        $allowed = [
            self::ROLE_ADMIN,
            self::ROLE_USER,
            self::ROLE_VIEWER,
            self::ROLE_INACTIVE,
        ];

        if ($allowActive && $role === 'active') {
            return;
        }

        if (!in_array($role, $allowed, true)) {
            throw new InvalidArgumentException("Invalid role: {$role}");
        }
    }

    public function __construct(PDO $db)
    {
        $this->db = $db;
        $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    }

    /**
     * Create a new user record using Argon2 hashing.
     *
     * @param string $username
     * @param string $firstName
     * @param string $lastName
     * @param string $email
     * @param string $password
     * @param string $role
     * @return int New user ID
     */
    public function createUser(
        string $username,
        string $firstName,
        string $lastName,
        string $email,
        string $password,
        string $role = self::ROLE_USER,
    ): int {
        $trimmedUsername = trim($username);
        if ($this->findByUsername($trimmedUsername) !== null) {
            throw new InvalidArgumentException(
                "Username '{$trimmedUsername}' is already taken.",
            );
        }

        $emailCheck = $this->verifyUniqueEmail($email);
        if (!$emailCheck['success']) {
            throw new InvalidArgumentException($emailCheck['error']);
        }

        $this->validateRole($role);

        // Using Argon2id for password hashing
        $passwordHash = password_hash($password, PASSWORD_ARGON2ID);

        $sql = 'INSERT INTO users (username, first_name, last_name, email, password_hash, role)
                VALUES (:u, :fn, :ln, :e, :p, :r)';

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':u' => $trimmedUsername,
            ':fn' => trim($firstName),
            ':ln' => trim($lastName),
            ':e' => $emailCheck['email'],
            ':p' => $passwordHash,
            ':r' => $role,
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function findById(int $id): ?array
    {
        $sql = 'SELECT * FROM users WHERE id = :id LIMIT 1';
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
        return $stmt->fetch() ?: null;
    }

    public function findByUsername(string $username): ?array
    {
        $sql = 'SELECT * FROM users WHERE username = :un LIMIT 1';
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':un' => trim($username)]);
        return $stmt->fetch() ?: null;
    }

    public function findByPartialMatch(string $term): array
    {
        $query_term = trim($term);
        if ($query_term === '') {
            return [];
        }

        $sql = 'SELECT id, email, username, first_name, last_name, role
                FROM users
                WHERE email LIKE :q
                   OR username LIKE :q
                   OR first_name LIKE :q
                   OR last_name LIKE :q';

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':q' => "%$query_term%"]);
        return $stmt->fetchAll();
    }

    // public function getAllUsersOLD(bool $show_inactive = true): array
    // {
    //     $sql =
    //         'SELECT id, email, username, first_name, last_name, role FROM users ORDER BY id';
    //     return $this->db->query($sql)->fetchAll();
    // }

    public function getAllUsers(?string $role = null): array
    {
        $allowedRoles = $this->getAllowedRoles();

        if ($role !== null) {
            $this->validateRole($role, true);
        }

        $sql =
            'SELECT id, username, first_name, last_name, email, role FROM users';
        $params = [];

        if ($role === 'active') {
            $sql .= ' WHERE role != :inactive';
            $params[':inactive'] = self::ROLE_INACTIVE;
        } elseif ($role != null) {
            $sql .= ' WHERE role = :role';
            $params[':role'] = $role;
        }

        $sql .=
            ' ORDER BY last_name ASC, CASE WHEN role = "inactive" THEN 1 ELSE 0 END ASC, role;';

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function setUserRole(int $userid, string $role): bool
    {
        $allowedRoles = [
            self::ROLE_ADMIN,
            self::ROLE_USER,
            self::ROLE_VIEWER,
            self::ROLE_INACTIVE,
        ];
        if (!in_array($role, $allowedRoles, true)) {
            throw new InvalidArgumentException("Invalid role: {$role}.");
        }

        $sql = 'UPDATE users SET role = :role, deactivated_at = CASE
                WHEN :role_check = :inactive THEN :now ELSE NULL END
                WHERE id = :id';

        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':role' => $role,
            ':role_check' => $role,
            ':inactive' => self::ROLE_INACTIVE,
            ':now' => date('Y-m-d H:i:s'),
            ':id' => $userid,
        ]) && $stmt->rowCount() > 0;
    }

    public function verifyUniqueEmail(string $email, int $userid = 0): array
    {
        $email_trimmed = trim($email);
        if (
            $email_trimmed === '' ||
            !filter_var($email_trimmed, FILTER_VALIDATE_EMAIL)
        ) {
            return [
                'success' => false,
                'error' => 'Invalid or empty email.',
                'email' => null,
            ];
        }

        $sql = 'SELECT 1 FROM users WHERE email = :email AND id <> :id LIMIT 1';
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $userid, ':email' => $email_trimmed]);

        if ($stmt->fetchColumn() !== false) {
            return [
                'success' => false,
                'error' => 'Email already in use.',
                'email' => null,
            ];
        }

        return ['success' => true, 'error' => null, 'email' => $email_trimmed];
    }

    public function updateUserInfo(int $userid, array $changes): bool
    {
        $allowedFields = ['email', 'first_name', 'last_name'];
        $params = [':id' => $userid];
        $updates = [];

        foreach ($changes as $field => $new_value) {
            if (
                !is_string($new_value) ||
                trim($new_value) === '' ||
                !in_array($field, $allowedFields, true)
            ) {
                continue;
            }

            if ($field === 'email') {
                $result = $this->verifyUniqueEmail($new_value, $userid);
                if (!$result['success']) {
                    throw new InvalidArgumentException($result['error']);
                }
                $new_value = $result['email'];
            }

            $updates[] = "$field = :$field";
            $params[":$field"] = $new_value;
        }

        if (empty($updates)) {
            return false;
        }

        $sql =
            'UPDATE users SET ' . implode(', ', $updates) . ' WHERE id = :id';
        return $this->db->prepare($sql)->execute($params);
    }
}
