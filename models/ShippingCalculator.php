<?php
declare(strict_types=1);

class ShippingCalculator {
    public function __construct(private PDO $db) {}

    public function calculate(array $items, string $city, string $country = 'KZ'): array {
        $zone = $this->getZone($city, $country);
        $baseRate = $zone['base_rate'] ?? 0;
        $freeThreshold = $zone['free_shipping_threshold'] ?? 0;
        
        $subtotal = array_reduce($items, fn($s, $i) => $s + $i['price'] * $i['qty'], 0);
        $weight = $this->calculateWeight($items);
        
        $shippingCost = $baseRate;
        
        // Weight-based surcharge
        if ($weight > 2) {
            $shippingCost += ($weight - 2) * ($zone['per_kg'] ?? 500); // 500 tenge per kg over 2kg
        }
        
        // Free shipping
        if ($freeThreshold > 0 && $subtotal >= $freeThreshold) {
            $shippingCost = 0;
        }
        
        // Express option
        $expressCost = $shippingCost + ($zone['express_surcharge'] ?? 2000);
        
        return [
            'zone' => $zone['name'] ?? 'Standard',
            'city' => $city,
            'country' => $country,
            'weight_kg' => round($weight, 2),
            'subtotal' => $subtotal,
            'standard' => [
                'name' => 'Стандартная доставка',
                'cost' => max(0, $shippingCost),
                'days' => $zone['standard_days'] ?? '5-7',
            ],
            'express' => [
                'name' => 'Экспресс доставка',
                'cost' => max(0, $expressCost),
                'days' => $zone['express_days'] ?? '1-2',
            ],
            'pickup' => $this->getPickupPoints($city),
            'free_threshold' => $freeThreshold,
        ];
    }

    private function getZone(string $city, string $country): array {
        $stmt = $this->db->prepare("
            SELECT * FROM shipping_zones 
            WHERE country = ? AND (cities LIKE ? OR cities = '*')
            ORDER BY priority ASC
            LIMIT 1
        ");
        $stmt->execute([$country, "%{$city}%"]);
        $zone = $stmt->fetch();
        
        if (!$zone) {
            // Default zone
            return [
                'name' => 'Другая локация',
                'base_rate' => 2000,
                'per_kg' => 500,
                'free_shipping_threshold' => 50000,
                'express_surcharge' => 2000,
                'standard_days' => '7-14',
                'express_days' => '3-5',
            ];
        }
        
        return $zone;
    }

    private function calculateWeight(array $items): float {
        $weights = [
            'vinyl' => 0.6,
            'single' => 0.3,
            'cd' => 0.15,
            'dvd' => 0.2,
            'merch' => 0.25,
        ];
        
        $totalWeight = 0;
        foreach ($items as $item) {
            $category = $item['category'] ?? 'vinyl';
            $unitWeight = $weights[$category] ?? 0.5;
            $totalWeight += $unitWeight * $item['qty'];
        }
        
        return $totalWeight;
    }

    private function getPickupPoints(string $city): array {
        $stmt = $this->db->prepare("
            SELECT * FROM pickup_points 
            WHERE city = ? AND is_active = 1
            ORDER BY name ASC
        ");
        $stmt->execute([$city]);
        return $stmt->fetchAll();
    }

    // Admin methods
    public function getZones(): array {
        return $this->db->query("SELECT * FROM shipping_zones ORDER BY priority ASC")->fetchAll();
    }

    public function createZone(array $data): int {
        $stmt = $this->db->prepare("
            INSERT INTO shipping_zones (name, country, cities, base_rate, per_kg, free_shipping_threshold, express_surcharge, standard_days, express_days, priority)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $data['name'], $data['country'], $data['cities'],
            $data['base_rate'], $data['per_kg'] ?? 0,
            $data['free_shipping_threshold'] ?? 0,
            $data['express_surcharge'] ?? 0,
            $data['standard_days'] ?? '5-7',
            $data['express_days'] ?? '1-2',
            $data['priority'] ?? 100
        ]);
        return (int)$this->db->lastInsertId();
    }

    public function updateZone(int $id, array $data): bool {
        $stmt = $this->db->prepare("
            UPDATE shipping_zones SET
                name = ?, country = ?, cities = ?, base_rate = ?, per_kg = ?,
                free_shipping_threshold = ?, express_surcharge = ?,
                standard_days = ?, express_days = ?, priority = ?
            WHERE id = ?
        ");
        return $stmt->execute([
            $data['name'], $data['country'], $data['cities'],
            $data['base_rate'], $data['per_kg'] ?? 0,
            $data['free_shipping_threshold'] ?? 0,
            $data['express_surcharge'] ?? 0,
            $data['standard_days'] ?? '5-7',
            $data['express_days'] ?? '1-2',
            $data['priority'] ?? 100,
            $id
        ]);
    }

    public function deleteZone(int $id): bool {
        $stmt = $this->db->prepare("DELETE FROM shipping_zones WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function getPickupPointsList(): array {
        return $this->db->query("SELECT * FROM pickup_points ORDER BY city, name")->fetchAll();
    }

    public function createPickupPoint(array $data): int {
        $stmt = $this->db->prepare("
            INSERT INTO pickup_points (name, city, address, phone, schedule, coordinates, is_active)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $data['name'], $data['city'], $data['address'],
            $data['phone'] ?? null, $data['schedule'] ?? null,
            $data['coordinates'] ?? null, $data['is_active'] ?? 1
        ]);
        return (int)$this->db->lastInsertId();
    }

    public function updatePickupPoint(int $id, array $data): bool {
        $stmt = $this->db->prepare("
            UPDATE pickup_points SET
                name = ?, city = ?, address = ?, phone = ?, schedule = ?,
                coordinates = ?, is_active = ?
            WHERE id = ?
        ");
        return $stmt->execute([
            $data['name'], $data['city'], $data['address'],
            $data['phone'] ?? null, $data['schedule'] ?? null,
            $data['coordinates'] ?? null, $data['is_active'] ?? 1,
            $id
        ]);
    }

    public function deletePickupPoint(int $id): bool {
        $stmt = $this->db->prepare("DELETE FROM pickup_points WHERE id = ?");
        return $stmt->execute([$id]);
    }
}