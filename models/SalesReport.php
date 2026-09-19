<?php
declare(strict_types=1);

class SalesReport {
    public function __construct(private PDO $db) {}

    /**
     * Aggregated sales for a date range, optionally filtered by category or artist.
     * @return array{summary: array, daily: array, by_category: array, by_artist: array, top_products: array}
     */
    public function build(string $from, string $to, string $category = '', int $artistId = 0): array {
        $where = ["o.status != 'cancelled'", "o.created_at >= :from", "o.created_at < :to_exclusive"];
        $params = [
            'from' => $from . ' 00:00:00',
            // +1 day so the end date is inclusive
            'to_exclusive' => date('Y-m-d 00:00:00', strtotime($to . ' +1 day')),
        ];

        if ($category !== '') {
            $where[] = "p.category = :category";
            $params['category'] = $category;
        }
        if ($artistId > 0) {
            $where[] = "p.artist_id = :artist";
            $params['artist'] = $artistId;
        }
        $whereSql = implode(' AND ', $where);
        $join = "FROM order_items oi
                 JOIN orders o ON o.id = oi.order_id
                 JOIN products p ON p.id = oi.product_id";

        // Summary
        $stmt = $this->db->prepare("
            SELECT COALESCE(SUM(oi.quantity), 0) AS units,
                   COALESCE(SUM(oi.quantity * oi.unit_price), 0) AS revenue,
                   COUNT(DISTINCT o.id) AS orders
            $join WHERE $whereSql
        ");
        $stmt->execute($params);
        $summary = $stmt->fetch() ?: ['units' => 0, 'revenue' => 0, 'orders' => 0];

        // Daily revenue series
        $stmt = $this->db->prepare("
            SELECT DATE(o.created_at) AS day,
                   SUM(oi.quantity * oi.unit_price) AS revenue,
                   SUM(oi.quantity) AS units
            $join WHERE $whereSql
            GROUP BY DATE(o.created_at)
            ORDER BY day ASC
        ");
        $stmt->execute($params);
        $daily = $stmt->fetchAll();

        // By category
        $stmt = $this->db->prepare("
            SELECT p.category,
                   SUM(oi.quantity) AS units,
                   SUM(oi.quantity * oi.unit_price) AS revenue
            $join WHERE $whereSql
            GROUP BY p.category
            ORDER BY revenue DESC
        ");
        $stmt->execute($params);
        $byCategory = $stmt->fetchAll();

        // By artist
        $stmt = $this->db->prepare("
            SELECT a.name AS artist,
                   SUM(oi.quantity) AS units,
                   SUM(oi.quantity * oi.unit_price) AS revenue
            $join WHERE $whereSql
            GROUP BY a.id, a.name
            ORDER BY revenue DESC
        ");
        $stmt->execute($params);
        $byArtist = $stmt->fetchAll();

        // Top products
        $stmt = $this->db->prepare("
            SELECT p.id, p.name, p.category,
                   SUM(oi.quantity) AS units,
                   SUM(oi.quantity * oi.unit_price) AS revenue
            $join WHERE $whereSql
            GROUP BY p.id, p.name, p.category
            ORDER BY revenue DESC
            LIMIT 10
        ");
        $stmt->execute($params);
        $topProducts = $stmt->fetchAll();

        return [
            'summary' => $summary,
            'daily' => $daily,
            'by_category' => $byCategory,
            'by_artist' => $byArtist,
            'top_products' => $topProducts,
        ];
    }

    /** Stream a CSV of the report to the browser and exit. */
    public function downloadCsv(string $from, string $to, string $category = '', int $artistId = 0): never {
        $report = $this->build($from, $to, $category, $artistId);

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="vinylwave-report-' . $from . '_' . $to . '.csv"');

        $out = fopen('php://output', 'w');
        fwrite($out, "\xEF\xBB\xBF"); // UTF-8 BOM for Excel
        fputcsv($out, ['Section', 'Label', 'Units', 'Revenue']);

        fputcsv($out, ['Summary', 'Period', $from . ' — ' . $to, '']);
        fputcsv($out, ['Summary', 'Orders', $report['summary']['orders'], '']);
        fputcsv($out, ['Summary', 'Units sold', $report['summary']['units'], '']);
        fputcsv($out, ['Summary', 'Revenue', '', number_format((float)$report['summary']['revenue'], 2, '.', '')]);

        fputcsv($out, []);
        fputcsv($out, ['Daily', 'Day', 'Units', 'Revenue']);
        foreach ($report['daily'] as $d) {
            fputcsv($out, ['Daily', $d['day'], $d['units'], number_format((float)$d['revenue'], 2, '.', '')]);
        }

        fputcsv($out, []);
        fputcsv($out, ['Category', 'Category', 'Units', 'Revenue']);
        foreach ($report['by_category'] as $c) {
            fputcsv($out, ['Category', $c['category'], $c['units'], number_format((float)$c['revenue'], 2, '.', '')]);
        }

        fputcsv($out, []);
        fputcsv($out, ['Artist', 'Artist', 'Units', 'Revenue']);
        foreach ($report['by_artist'] as $a) {
            fputcsv($out, ['Artist', $a['artist'], $a['units'], number_format((float)$a['revenue'], 2, '.', '')]);
        }

        fputcsv($out, []);
        fputcsv($out, ['Top product', 'Product', 'Units', 'Revenue']);
        foreach ($report['top_products'] as $p) {
            fputcsv($out, ['Top product', $p['name'], $p['units'], number_format((float)$p['revenue'], 2, '.', '')]);
        }

        fclose($out);
        exit;
    }
}
