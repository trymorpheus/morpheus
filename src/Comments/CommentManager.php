<?php

namespace Morpheus\Comments;

use PDO;

class CommentManager
{
    private PDO $pdo;
    private string $table;
    private bool $requireApproval;
    private bool $allowNested;

    public function __construct(PDO $pdo, string $table = 'comments', bool $requireApproval = true, bool $allowNested = true)
    {
        $this->pdo = $pdo;
        $this->table = $table;
        $this->requireApproval = $requireApproval;
        $this->allowNested = $allowNested;
    }

    public function add(int $postId, string $authorName, string $authorEmail, string $content, ?int $parentId = null): array
    {
        if (empty($authorName) || empty($authorEmail) || empty($content)) {
            return ['success' => false, 'error' => 'All fields are required'];
        }

        if (!filter_var($authorEmail, FILTER_VALIDATE_EMAIL)) {
            return ['success' => false, 'error' => 'Invalid email'];
        }

        if ($this->isSpam($content, $authorEmail)) {
            return ['success' => false, 'error' => 'Comment rejected as spam'];
        }

        $status = $this->requireApproval ? 'pending' : 'approved';

        $stmt = $this->pdo->prepare("
            INSERT INTO {$this->table} (post_id, author_name, author_email, content, status, parent_id, created_at)
            VALUES (:post_id, :name, :email, :content, :status, :parent_id, NOW())
        ");

        $stmt->execute([
            'post_id' => $postId,
            'name' => $authorName,
            'email' => $authorEmail,
            'content' => $content,
            'status' => $status,
            'parent_id' => $parentId,
        ]);

        return [
            'success' => true,
            'id' => (int) $this->pdo->lastInsertId(),
            'status' => $status,
        ];
    }

    public function getComments(int $postId, string $status = 'approved'): array
    {
        $stmt = $this->pdo->prepare("
            SELECT * FROM {$this->table}
            WHERE post_id = :post_id AND status = :status AND parent_id IS NULL
            ORDER BY created_at ASC
        ");
        
        $stmt->execute(['post_id' => $postId, 'status' => $status]);
        $comments = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if ($this->allowNested) {
            foreach ($comments as &$comment) {
                $comment['replies'] = $this->getReplies($comment['id'], $status);
            }
        }

        return $comments;
    }

    private function getReplies(int $parentId, string $status = 'approved'): array
    {
        $stmt = $this->pdo->prepare("
            SELECT * FROM {$this->table}
            WHERE parent_id = :parent_id AND status = :status
            ORDER BY created_at ASC
        ");
        
        $stmt->execute(['parent_id' => $parentId, 'status' => $status]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function approve(int $id): bool
    {
        $stmt = $this->pdo->prepare("UPDATE {$this->table} SET status = 'approved' WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }

    public function reject(int $id): bool
    {
        $stmt = $this->pdo->prepare("UPDATE {$this->table} SET status = 'rejected' WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->pdo->prepare("DELETE FROM {$this->table} WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }

    public function getPending(): array
    {
        $stmt = $this->pdo->query("
            SELECT c.*, p.title as post_title 
            FROM {$this->table} c
            LEFT JOIN posts p ON c.post_id = p.id
            WHERE c.status = 'pending'
            ORDER BY c.created_at DESC
        ");
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getCount(int $postId, string $status = 'approved'): int
    {
        $stmt = $this->pdo->prepare("
            SELECT COUNT(*) FROM {$this->table}
            WHERE post_id = :post_id AND status = :status
        ");
        
        $stmt->execute(['post_id' => $postId, 'status' => $status]);
        return (int) $stmt->fetchColumn();
    }

    private function isSpam(string $content, string $email): bool
    {
        // Simple spam detection
        $spamWords = ['viagra', 'casino', 'lottery', 'winner', 'click here'];
        $contentLower = strtolower($content);
        
        foreach ($spamWords as $word) {
            if (strpos($contentLower, $word) !== false) {
                return true;
            }
        }

        // Check for too many links
        if (substr_count($content, 'http') > 3) {
            return true;
        }

        return false;
    }
}
