<?php
declare(strict_types=1);

// TODO: Finish UserRepository, ARKRepository, NaanRepository, ShoulderRepository.

class UserRepository
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function findById(int $id): ?array
    {
        $sql = 'SELECT * FROM users WHERE id = :id';
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
        return $stmt->fetch() ?: null;
    }

    public function findByUsername(string $username): ?array
    {
        $sql = 'SELECT * FROM users WHERE username = :un LIMIT 1';
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':un' => $username]);
        return $stmt->fetch() ?: null;
    }
}
