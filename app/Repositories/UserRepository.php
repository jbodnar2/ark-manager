<?php
declare(strict_types=1);

namespace App\Repositories;

use App\Models\User;
use PDO;
use InvalidArgumentException;

class UserRepository
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
        $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    }

    private function mapToModel(array $data): User
    {
        $user = new User();
        $user->id = (int) ($data['id'] ?? 0);
        $user->username = $data['username'] ?? '';
        $user->first_name = $data['first_name'] ?? '';
        $user->last_name = $data['last_name'] ?? '';
        $user->email = $data['email'] ?? '';
        $user->role = $data['role'] ?? '';
        $user->api_token = $data['api_token'] ?? null;
        return $user;
    }

    private function validateRole(string $role, bool $allowActive = false): void
    {
        $allowed = User::getAllowedRoles();

        if ($allowActive && $role === 'active') {
            return;
        }

        if (!in_array($role, $allowed, true)) {
            throw new InvalidArgumentException("Invalid role: {$role}");
        }
    }

    public function verifyUniqueEmail(string $email, int $user_id = 0): array
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
        $stmt->execute([':id' => $user_id, ':email' => $email_trimmed]);

        if ($stmt->fetchColumn() !== false) {
            return [
                'success' => false,
                'error' => 'Email already in use.',
                'email' => null,
            ];
        }

        return ['success' => true, 'error' => null, 'email' => $email_trimmed];
    }

    public function createUser(
        string $username,
        string $firstName,
        string $lastName,
        string $email,
        string $password,
        string $role = User::ROLE_USER, // Reference the Model constant
    ): int {
        $trimmed_username = trim($username);
        $trimmed_email = trim($email);

        // 1. Validate Password presence
        if (empty(trim($password))) {
            throw new InvalidArgumentException('Password cannot be empty.');
        }

        // 2. Validate Username uniqueness
        if ($this->findByUsername($trimmed_username) !== null) {
            throw new InvalidArgumentException(
                "Username '{$trimmed_username}' is already taken.",
            );
        }

        // 3. Validate Email uniqueness and format
        $email_verification = $this->verifyUniqueEmail($trimmed_email);
        if (!$email_verification['success']) {
            throw new InvalidArgumentException($email_verification['error']);
        }

        $this->validateRole($role);

        $password_hash = password_hash($password, PASSWORD_ARGON2ID);

        $sql = 'INSERT INTO users (username, first_name, last_name, email, password_hash, role)
                VALUES (:u, :fn, :ln, :e, :p, :r)';

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':u' => $trimmed_username,
            ':fn' => trim($firstName),
            ':ln' => trim($lastName),
            ':e' => $trimmed_email, // Use the trimmed version
            ':p' => $password_hash,
            ':r' => $role,
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function findById(int $user_id): ?User
    {
        $sql = 'SELECT * FROM users WHERE id = :id LIMIT 1';
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $user_id]);
        $result = $stmt->fetch();

        return $result ? $this->mapToModel($result) : null;
    }

    public function findByUsername(string $username): ?User
    {
        $sql = 'SELECT * FROM users WHERE username = :un LIMIT 1';
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':un' => trim($username)]);
        $result = $stmt->fetch();

        return $result ? $this->mapToModel($result) : null;
    }

    public function getAllUsers(?string $role = null): array
    {
        if ($role !== null) {
            $this->validateRole($role, true);
        }

        $sql = 'SELECT * FROM users';
        $params = [];

        if ($role === 'active') {
            $sql .= ' WHERE role != :inactive';
            $params[':inactive'] = User::ROLE_INACTIVE;
        } elseif ($role != null) {
            $sql .= ' WHERE role = :role';
            $params[':role'] = $role;
        }

        $sql .=
            ' ORDER BY last_name ASC, CASE WHEN role = "inactive" THEN 1 ELSE 0 END ASC, role;';

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $results = $stmt->fetchAll();

        return array_map([$this, 'mapToModel'], $results);
    }

    public function findByToken(string $token): ?User
    {
        $sql = 'SELECT * FROM users WHERE api_token = :token LIMIT 1';
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':token' => $token]);
        $result = $stmt->fetch();

        return $result ? $this->mapToModel($result) : null;
    }

    public function findByPartialMatch(string $term): array
    {
        $query_term = trim($term);

        if ($query_term === '') {
            return [];
        }

        $sql = 'SELECT *
                FROM users
                WHERE email LIKE :q
                   OR username LIKE :q
                   OR first_name LIKE :q
                   OR last_name LIKE :q';

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':q' => "%$query_term%"]);
        $results = $stmt->fetchAll();

        return array_map([$this, 'mapToModel'], $results);
    }

    public function getPasswordHashByUsername(string $username): ?string
    {
        $sql =
            'SELECT password_hash FROM users WHERE username = :username LIMIT 1';
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':username' => $username]);
        $result = $stmt->fetch();

        return $result['password_hash'] ?? null;
    }

    public function updateToken(int $user_id, string $token): bool
    {
        $sql = 'UPDATE users
            SET api_token = :token,
                api_token_issued = CURRENT_TIMESTAMP
            WHERE id = :id';

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            ':token' => $token,
            ':id' => $user_id,
        ]);
    }

    public function revokeToken(int $user_id): bool
    {
        $sql = "UPDATE users
                SET api_token = NULL,
                    api_token_issued = NULL
                WHERE id = :id";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':id' => $user_id]);
    }

    public function setUserRole(int $user_id, string $role): bool
    {
        $allowedRoles = User::getAllowedRoles();

        if (!in_array($role, $allowedRoles, true)) {
            throw new InvalidArgumentException("Invalid role: {$role}.");
        }

        $sql = 'UPDATE users SET role = :role WHERE id = :id';

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            ':role' => $role,
            ':id' => $user_id,
        ]);
    }

    public function updateUserInfo(User $user): bool
    {
        $email_verification = $this->verifyUniqueEmail($user->email, $user->id);

        if (!$email_verification['success']) {
            throw new InvalidArgumentException($email_verification['error']);
        }

        $sql = 'UPDATE users
                SET email = :email,
                    first_name = :first_name,
                    last_name = :last_name
                WHERE id = :id';

        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':email' => $email_verification['email'],
            ':first_name' => trim($user->first_name),
            ':last_name' => trim($user->last_name),
            ':id' => $user->id,
        ]) && $stmt->rowCount() > 0;
    }
}
