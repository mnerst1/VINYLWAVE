<?php
declare(strict_types=1);

class Artist {
    public function __construct(private PDO $db) {}

    public function all(): array {
        $sql = "SELECT a.*, COUNT(p.id) AS product_count 
                FROM artists a 
                LEFT JOIN products p ON p.artist_id = a.id 
                GROUP BY a.id 
                ORDER BY a.name ASC";
        return $this->db->query($sql)->fetchAll();
    }

    public function find(int $id): ?array {
        $stmt = $this->db->prepare("SELECT * FROM artists WHERE id = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function create(array $data): int {
        $slug = $data['slug'] ?? '';
        if (empty($slug)) {
            $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $data['name'] ?? ''), '-'));
        }

        // Ensure unique slug
        $baseSlug = $slug;
        $counter = 1;
        while ($this->slugExists($slug)) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        $stmt = $this->db->prepare("INSERT INTO artists (name, slug, genre, image_url) VALUES (?, ?, ?, ?)");
        $stmt->execute([
            trim($data['name'] ?? ''),
            $slug,
            trim($data['genre'] ?? ''),
            trim($data['image_url'] ?? '')
        ]);
        return (int)$this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool {
        $stmt = $this->db->prepare("UPDATE artists SET name = ?, genre = ?, image_url = ? WHERE id = ?");
        return $stmt->execute([
            trim($data['name'] ?? ''),
            trim($data['genre'] ?? ''),
            trim($data['image_url'] ?? ''),
            $id
        ]);
    }

    public function delete(int $id): bool {
        $stmt = $this->db->prepare("DELETE FROM artists WHERE id = ?");
        return $stmt->execute([$id]);
    }

    private function slugExists(string $slug): bool {
        $stmt = $this->db->prepare("SELECT id FROM artists WHERE slug = ?");
        $stmt->execute([$slug]);
        return (bool)$stmt->fetch();
    }
}
