<?php
declare(strict_types=1);

/**
 * CSV import/export for the product catalog.
 * Format: name,artist,category,price,stock,genre,color_variant,description,cover_url,is_limited
 */
class ProductCsv {
    public const CATEGORIES = ['vinyl', 'single', 'cd', 'dvd', 'merch'];

    public function __construct(private PDO $db) {}

    /** Stream all products as CSV and exit. */
    public function export(): never {
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="vinylwave-products-' . date('Y-m-d') . '.csv"');

        $out = fopen('php://output', 'w');
        fwrite($out, "\xEF\xBB\xBF"); // UTF-8 BOM so Excel opens it correctly
        fputcsv($out, [
            'name', 'artist', 'category', 'price', 'stock', 'genre',
            'color_variant', 'description', 'cover_url', 'is_limited', 'is_active'
        ]);

        $rows = $this->db->query("
            SELECT p.name, a.name AS artist, p.category, p.price, p.stock, p.genre,
                   p.color_variant, p.description, p.cover_url, p.is_limited, p.is_active
            FROM products p JOIN artists a ON a.id = p.artist_id
            ORDER BY p.id ASC
        ")->fetchAll();

        foreach ($rows as $r) {
            fputcsv($out, [
                $r['name'], $r['artist'], $r['category'], $r['price'], $r['stock'],
                $r['genre'], $r['color_variant'], $r['description'], $r['cover_url'],
                (int)$r['is_limited'], (int)$r['is_active'],
            ]);
        }
        fclose($out);
        exit;
    }

    /**
     * Import products from a CSV string. Artists are matched by name
     * (created when missing). Existing products are matched by (name + artist)
     * and updated, otherwise inserted.
     *
     * @return array{created: int, updated: int, skipped: int, errors: array<string>}
     */
    public function import(string $csvContent): array {
        $lines = preg_split('/\r\n|\r|\n/', trim($csvContent));
        if (!$lines || count($lines) < 2) {
            return ['created' => 0, 'updated' => 0, 'skipped' => 0, 'errors' => ['Файл пуст или содержит только заголовок.']];
        }

        $header = array_map(fn($h) => strtolower(trim($h, " \t\"'")), str_getcsv($lines[0]));

        $result = ['created' => 0, 'updated' => 0, 'skipped' => 0, 'errors' => []];

        for ($i = 1, $n = count($lines); $i < $n; $i++) {
            $line = trim($lines[$i]);
            if ($line === '') continue;

            $row = str_getcsv($line);
            if (count($row) < count($header)) {
                $row = array_pad($row, count($header), '');
            }
            $data = array_combine($header, array_slice($row, 0, count($header)));

            $name = trim($data['name'] ?? '');
            $artist = trim($data['artist'] ?? '');
            $category = strtolower(trim($data['category'] ?? 'vinyl'));
            $price = (float)str_replace(',', '.', $data['price'] ?? 0);
            $stock = (int)($data['stock'] ?? 0);

            if ($name === '' || $artist === '') {
                $result['skipped']++;
                $result['errors'][] = "Строка " . ($i + 1) . ": не указано название или артист.";
                continue;
            }
            if (!in_array($category, self::CATEGORIES, true)) {
                $category = 'vinyl';
            }
            if ($price <= 0) {
                $result['skipped']++;
                $result['errors'][] = "Строка " . ($i + 1) . ": некорректная цена («{$name}»).";
                continue;
            }

            $artistId = $this->resolveArtist($artist);
            if ($artistId === null) {
                $result['skipped']++;
                $result['errors'][] = "Строка " . ($i + 1) . ": не удалось создать артиста «{$artist}».";
                continue;
            }

            $existing = $this->findByNameAndArtist($name, $artistId);

            $cover = trim($data['cover_url'] ?? '');
            $payload = [
                'artist_id' => $artistId,
                'name' => $name,
                'category' => $category,
                'genre' => trim($data['genre'] ?? ''),
                'price' => $price,
                'stock' => max(0, $stock),
                'color_variant' => trim($data['color_variant'] ?? ''),
                'description' => trim($data['description'] ?? ''),
                'cover_url' => $cover !== '' ? $cover : 'https://images.unsplash.com/photo-1539375665275-f9de415ef9ac?auto=format&fit=crop&w=1000&q=85',
                'is_limited' => in_array(strtolower(trim($data['is_limited'] ?? '')), ['1', 'yes', 'true'], true) ? 1 : 0,
                'is_active' => isset($data['is_active']) ? (int)in_array(strtolower(trim($data['is_active'])), ['1', 'yes', 'true'], true) : 1,
            ];

            $productModel = new Product($this->db);
            if ($existing) {
                $payload['cover_thumb_url'] = $existing['cover_thumb_url'] ?? null;
                $payload['video_url'] = $existing['video_url'] ?? null;
                $payload['video_thumb_url'] = $existing['video_thumb_url'] ?? null;
                $payload['drop_ends_at'] = $existing['drop_ends_at'] ?? null;
                $productModel->update((int)$existing['id'], $payload);
                $result['updated']++;
            } else {
                $productModel->create($payload);
                $result['created']++;
            }
        }

        return $result;
    }

    /** Find or create an artist by name; returns id or null on failure. */
    private function resolveArtist(string $name): ?int {
        $stmt = $this->db->prepare("SELECT id FROM artists WHERE LOWER(name) = LOWER(?) LIMIT 1");
        $stmt->execute([$name]);
        $row = $stmt->fetch();
        if ($row) {
            return (int)$row['id'];
        }

        $artistModel = new Artist($this->db);
        try {
            return $artistModel->create([
                'name' => $name,
                'genre' => '',
                'image_url' => 'https://images.unsplash.com/photo-1493225457124-a3eb161ffa5f?auto=format&fit=crop&w=900&q=80',
            ]);
        } catch (Throwable) {
            return null;
        }
    }

    private function findByNameAndArtist(string $name, int $artistId): ?array {
        $stmt = $this->db->prepare("
            SELECT id, cover_thumb_url, video_url, video_thumb_url, drop_ends_at
            FROM products
            WHERE LOWER(name) = LOWER(?) AND artist_id = ?
            LIMIT 1
        ");
        $stmt->execute([$name, $artistId]);
        $row = $stmt->fetch();
        return $row ?: null;
    }
}
