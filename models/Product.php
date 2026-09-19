<?php
declare(strict_types=1);

class Product {
    public function __construct(private PDO $db) {}

    public function all(array $filters = []): array {
        $sql = "SELECT p.*, a.name AS artist_name,
                       (SELECT COALESCE(AVG(rating), 5.0) FROM reviews WHERE product_id = p.id AND status = 'approved') AS avg_rating,
                       (SELECT COUNT(*) FROM reviews WHERE product_id = p.id AND status = 'approved') AS reviews_count
                FROM products p 
                JOIN artists a ON a.id = p.artist_id 
                WHERE 1=1";
        $params = [];

        if (!($filters['include_inactive'] ?? false)) {
            $sql .= " AND p.is_active = 1";
        }

        if (!empty($filters['category'])) {
            $sql .= " AND p.category = :category";
            $params['category'] = $filters['category'];
        }
        if (!empty($filters['artist'])) {
            $sql .= " AND p.artist_id = :artist";
            $params['artist'] = (int)$filters['artist'];
        }
        if (!empty($filters['genre'])) {
            $sql .= " AND p.genre = :genre";
            $params['genre'] = $filters['genre'];
        }
        if (!empty($filters['variant'])) {
            $sql .= " AND p.color_variant = :variant";
            $params['variant'] = $filters['variant'];
        }
        if (!empty($filters['in_stock'])) {
            $sql .= " AND p.stock > 0";
        }
        if (!empty($filters['q'])) {
            $sql .= " AND (p.name LIKE :q OR a.name LIKE :q OR p.genre LIKE :q)";
            $params['q'] = '%' . trim($filters['q']) . '%';
        }

        $sql .= " ORDER BY p.is_limited DESC, p.created_at DESC";

        // Pagination support for "Load more"
        $limit = (int)($filters['limit'] ?? 0);
        if ($limit > 0) {
            $offset = max(0, (int)($filters['offset'] ?? 0));
            $sql .= " LIMIT " . $limit . " OFFSET " . $offset;
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /** Total row count for the current filter set (ignores limit/offset). */
    public function countAll(array $filters = []): int {
        $sql = "SELECT COUNT(*) FROM products p JOIN artists a ON a.id = p.artist_id WHERE 1=1";
        $params = [];
        if (!($filters['include_inactive'] ?? false)) {
            $sql .= " AND p.is_active = 1";
        }
        if (!empty($filters['category'])) {
            $sql .= " AND p.category = :category";
            $params['category'] = $filters['category'];
        }
        if (!empty($filters['artist'])) {
            $sql .= " AND p.artist_id = :artist";
            $params['artist'] = (int)$filters['artist'];
        }
        if (!empty($filters['genre'])) {
            $sql .= " AND p.genre = :genre";
            $params['genre'] = $filters['genre'];
        }
        if (!empty($filters['variant'])) {
            $sql .= " AND p.color_variant = :variant";
            $params['variant'] = $filters['variant'];
        }
        if (!empty($filters['in_stock'])) {
            $sql .= " AND p.stock > 0";
        }
        if (!empty($filters['q'])) {
            $sql .= " AND (p.name LIKE :q OR a.name LIKE :q OR p.genre LIKE :q)";
            $params['q'] = '%' . trim($filters['q']) . '%';
        }
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (int)$stmt->fetchColumn();
    }

    public function find(int $id): ?array {
        $stmt = $this->db->prepare("
            SELECT p.*, a.name AS artist_name, a.image_url AS artist_image,
                   (SELECT COALESCE(AVG(rating), 5.0) FROM reviews WHERE product_id = p.id AND status = 'approved') AS avg_rating,
                   (SELECT COUNT(*) FROM reviews WHERE product_id = p.id AND status = 'approved') AS reviews_count
            FROM products p 
            JOIN artists a ON a.id = p.artist_id 
            WHERE p.id = ?
        ");
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function tracks(int $id): array {
        $stmt = $this->db->prepare("SELECT * FROM tracks WHERE product_id = ? ORDER BY track_number ASC");
        $stmt->execute([$id]);
        return $stmt->fetchAll();
    }

    public function create(array $data): int {
        $slug = $data['slug'] ?? '';
        if (empty($slug)) {
            $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $data['name'] ?? ''), '-'));
        }
        $baseSlug = $slug;
        $counter = 1;
        while ($this->slugExists($slug)) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        $stmt = $this->db->prepare("
            INSERT INTO products (
                artist_id, name, slug, category, genre, price, stock,
                color_variant, description, cover_url, cover_thumb_url, video_url, video_thumb_url,
                is_limited, is_active, drop_ends_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            (int)$data['artist_id'],
            trim($data['name']),
            $slug,
            $data['category'],
            trim($data['genre'] ?? ''),
            (float)$data['price'],
            (int)($data['stock'] ?? 0),
            !empty($data['color_variant']) ? trim($data['color_variant']) : null,
            trim($data['description'] ?? ''),
            trim($data['cover_url'] ?? ''),
            !empty($data['cover_thumb_url']) ? trim($data['cover_thumb_url']) : null,
            !empty($data['video_url']) ? trim($data['video_url']) : null,
            !empty($data['video_thumb_url']) ? trim($data['video_thumb_url']) : null,
            !empty($data['is_limited']) ? 1 : 0,
            isset($data['is_active']) ? (int)(bool)$data['is_active'] : 1,
            !empty($data['drop_ends_at']) ? $data['drop_ends_at'] : null
        ]);
        return (int)$this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool {
        $stmt = $this->db->prepare("
            UPDATE products SET
                artist_id = ?,
                name = ?,
                category = ?,
                genre = ?,
                price = ?,
                stock = ?,
                color_variant = ?,
                description = ?,
                cover_url = ?,
                cover_thumb_url = ?,
                video_url = ?,
                video_thumb_url = ?,
                is_limited = ?,
                is_active = ?,
                drop_ends_at = ?
            WHERE id = ?
        ");
        return $stmt->execute([
            (int)$data['artist_id'],
            trim($data['name']),
            $data['category'],
            trim($data['genre'] ?? ''),
            (float)$data['price'],
            (int)($data['stock'] ?? 0),
            !empty($data['color_variant']) ? trim($data['color_variant']) : null,
            trim($data['description'] ?? ''),
            trim($data['cover_url'] ?? ''),
            !empty($data['cover_thumb_url']) ? trim($data['cover_thumb_url']) : null,
            !empty($data['video_url']) ? trim($data['video_url']) : null,
            !empty($data['video_thumb_url']) ? trim($data['video_thumb_url']) : null,
            !empty($data['is_limited']) ? 1 : 0,
            isset($data['is_active']) ? (int)(bool)$data['is_active'] : 1,
            !empty($data['drop_ends_at']) ? $data['drop_ends_at'] : null,
            $id
        ]);
    }

    public function setActive(int $id, bool $active): bool {
        $stmt = $this->db->prepare("UPDATE products SET is_active = ? WHERE id = ?");
        return $stmt->execute([$active ? 1 : 0, $id]);
    }

    public function bulkSetActive(array $ids, bool $active): int {
        if (!$ids) return 0;
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $stmt = $this->db->prepare("UPDATE products SET is_active = ? WHERE id IN ($placeholders)");
        $stmt->execute(array_merge([$active ? 1 : 0], $ids));
        return $stmt->rowCount();
    }

    public function bulkDelete(array $ids): int {
        if (!$ids) return 0;
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $stmt = $this->db->prepare("DELETE FROM products WHERE id IN ($placeholders)");
        $stmt->execute($ids);
        return $stmt->rowCount();
    }

    public function bulkSetStock(array $ids, int $stock): int {
        if (!$ids) return 0;
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $stmt = $this->db->prepare("UPDATE products SET stock = ? WHERE id IN ($placeholders)");
        $stmt->execute(array_merge([$stock], $ids));
        return $stmt->rowCount();
    }

    public function decrementStock(int $id, int $qty): void {
        $this->db->prepare("UPDATE products SET stock = GREATEST(0, stock - ?) WHERE id = ?")
            ->execute([$qty, $id]);
    }

    public function delete(int $id): bool {
        $stmt = $this->db->prepare("DELETE FROM products WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function saveTracks(int $productId, array $tracks): void {
        $this->db->prepare("DELETE FROM tracks WHERE product_id = ?")->execute([$productId]);
        if (empty($tracks)) {
            return;
        }

        $stmt = $this->db->prepare("
            INSERT INTO tracks (product_id, track_number, title, preview_url, duration_seconds)
            VALUES (?, ?, ?, ?, ?)
        ");
        // Rows arrive in visual (drag-and-drop) order — array order defines track numbers.
        $trackNum = 1;
        foreach ($tracks as $t) {
            $title = trim($t['title'] ?? '');
            if ($title === '') continue;
            $previewUrl = trim($t['preview_url'] ?? '');
            $duration = (int)($t['duration_seconds'] ?? 15);
            $stmt->execute([$productId, $trackNum, $title, $previewUrl ?: null, $duration]);
            $trackNum++;
        }
    }

    public function artists(): array {
        return $this->db->query("SELECT * FROM artists ORDER BY name ASC")->fetchAll();
    }

    public function genres(): array {
        return $this->db->query("SELECT DISTINCT genre FROM products WHERE genre IS NOT NULL AND genre != '' ORDER BY genre ASC")->fetchAll(PDO::FETCH_COLUMN);
    }

    public function variants(): array {
        return $this->db->query("SELECT DISTINCT color_variant FROM products WHERE color_variant IS NOT NULL AND color_variant != '' ORDER BY color_variant ASC")->fetchAll(PDO::FETCH_COLUMN);
    }

    private function slugExists(string $slug): bool {
        $stmt = $this->db->prepare("SELECT id FROM products WHERE slug = ?");
        $stmt->execute([$slug]);
        return (bool)$stmt->fetch();
    }
}
