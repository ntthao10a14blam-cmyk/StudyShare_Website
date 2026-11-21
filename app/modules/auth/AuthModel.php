<?php

class AuthModel
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function findByEmail(string $email): array|false
    {
        $stmt = $this->db->prepare(
            "SELECT id, full_name, email, password, avatar_path, role, status
             FROM users WHERE email = :email LIMIT 1"
        );
        $stmt->execute([':email' => $email]);
        return $stmt->fetch();
    }

    public function findById(int $id): array|false
    {
        $stmt = $this->db->prepare(
            "SELECT id, full_name, email, avatar_path, role, status, created_at
             FROM users WHERE id = :id LIMIT 1"
        );
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    public function emailExists(string $email): bool
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM users WHERE email = :email");
        $stmt->execute([':email' => $email]);
        return (int) $stmt->fetchColumn() > 0;
    }

    public function createUser(string $fullName, string $email, string $hashedPassword): int
    {
        $stmt = $this->db->prepare(
            "INSERT INTO users (full_name, email, password) VALUES (:full_name, :email, :password)"
        );
        $stmt->execute([
            ':full_name' => $fullName,
            ':email'     => $email,
            ':password'  => $hashedPassword,
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function updateProfile(int $id, string $fullName, ?string $avatarPath): void
    {
        $stmt = $this->db->prepare(
            "UPDATE users SET full_name = :full_name, avatar_path = :avatar_path WHERE id = :id"
        );
        $stmt->execute([
            ':full_name'   => $fullName,
            ':avatar_path' => $avatarPath,
            ':id'          => $id,
        ]);
    }

    public function updatePassword(int $id, string $hashedPassword): void
    {
        $stmt = $this->db->prepare("UPDATE users SET password = :password WHERE id = :id");
        $stmt->execute([':password' => $hashedPassword, ':id' => $id]);
    }
}
