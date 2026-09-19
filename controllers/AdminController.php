<?php
declare(strict_types=1);

class AdminController {
    private PDO $db;
    private Product $productModel;
    private Artist $artistModel;
    private Review $reviewModel;
    private User $userModel;

    public function __construct() {
        if (!User::isAdmin()) {
            $_SESSION['auth_error'] = 'Доступ только для администраторов.';
            header('Location: index.php?page=login&return_to=index.php?page=admin');
            exit;
        }
        $this->db = Database::connection();
        $this->productModel = new Product($this->db);
        $this->artistModel = new Artist($this->db);
        $this->reviewModel = new Review($this->db);
        $this->userModel = new User($this->db);
    }

    public function index(): void {
        $tab = $_GET['tab'] ?? 'dashboard';
        if (!empty($_GET['edit_product_id'])) {
            $tab = 'product-edit';
        }
        if (!empty($_GET['edit_artist_id'])) {
            $tab = 'artists';
        }

        // Stats
        $stats = [
            'products' => (int)$this->db->query("SELECT COUNT(*) FROM products")->fetchColumn(),
            'artists' => (int)$this->db->query("SELECT COUNT(*) FROM artists")->fetchColumn(),
            'orders' => (int)$this->db->query("SELECT COUNT(*) FROM orders")->fetchColumn(),
            'revenue' => (float)$this->db->query("SELECT COALESCE(SUM(total), 0) FROM orders WHERE status != 'cancelled'")->fetchColumn(),
            'reviews' => (int)$this->db->query("SELECT COUNT(*) FROM reviews")->fetchColumn(),
            'low_stock' => (int)$this->db->query("SELECT COUNT(*) FROM products WHERE stock <= 5")->fetchColumn(),
        ];

        $products = $this->productModel->all(['include_inactive' => true]);
        $artists = $this->artistModel->all();
        $reviews = $this->reviewModel->all(100);
        $pendingReviews = $this->reviewModel->pending(50);
        $pendingReviewsCount = $this->reviewModel->pendingCount();

        $orderStmt = $this->db->query("
            SELECT o.*, u.name AS user_account_name, u.email AS user_account_email
            FROM orders o
            LEFT JOIN users u ON u.id = o.user_id
            ORDER BY o.created_at DESC
            LIMIT 50
        ");
        $orders = $orderStmt->fetchAll();

        foreach ($orders as &$ord) {
            $itStmt = $this->db->prepare("SELECT * FROM order_items WHERE order_id = ?");
            $itStmt->execute([$ord['id']]);
            $ord['items'] = $itStmt->fetchAll();
        }
        unset($ord);

        // --- Chart data (Chart.js) ---
        $revenueDays = 30;
        $chartDaily = [];
        $stmt = $this->db->prepare("
            SELECT DATE(created_at) AS day, SUM(total) AS revenue, COUNT(*) AS orders
            FROM orders WHERE status != 'cancelled' AND created_at >= DATE_SUB(CURDATE(), INTERVAL ? DAY)
            GROUP BY DATE(created_at) ORDER BY day ASC
        ");
        $stmt->bindValue(1, $revenueDays, PDO::PARAM_INT);
        $stmt->execute();
        $chartDaily = $stmt->fetchAll();

        $chartTop = $this->db->query("
            SELECT p.name, SUM(oi.quantity) AS units, SUM(oi.quantity * oi.unit_price) AS revenue
            FROM order_items oi
            JOIN products p ON p.id = oi.product_id
            JOIN orders o ON o.id = oi.order_id
            WHERE o.status != 'cancelled'
            GROUP BY p.id, p.name ORDER BY revenue DESC LIMIT 7
        ")->fetchAll();

        $chartCategory = $this->db->query("
            SELECT p.category, SUM(oi.quantity * oi.unit_price) AS revenue
            FROM order_items oi
            JOIN products p ON p.id = oi.product_id
            JOIN orders o ON o.id = oi.order_id
            WHERE o.status != 'cancelled'
            GROUP BY p.category ORDER BY revenue DESC
        ")->fetchAll();

        // User management data
        $userModel = new User($this->db);
        $users = $userModel->allUsers(trim($_GET['user_search'] ?? ''));

        // Sales report data (reports tab)
        $reportModel = new SalesReport($this->db);
        $reportFrom = $_GET['report_from'] ?? date('Y-m-01');
        $reportTo = $_GET['report_to'] ?? date('Y-m-d');
        $reportCategory = $_GET['report_category'] ?? '';
        $reportArtist = (int)($_GET['report_artist'] ?? 0);
        $report = $reportModel->build($reportFrom, $reportTo, $reportCategory, $reportArtist);

        // Edit product state
        $editProduct = null;
        $editTracks = [];
        if (!empty($_GET['edit_product_id'])) {
            $editProduct = $this->productModel->find((int)$_GET['edit_product_id']);
            if ($editProduct) {
                $editTracks = $this->productModel->tracks((int)$editProduct['id']);
            }
        }

        // Edit artist state
        $editArtist = null;
        if (!empty($_GET['edit_artist_id'])) {
            $editArtist = $this->artistModel->find((int)$_GET['edit_artist_id']);
        }

        require __DIR__ . '/../views/admin/dashboard.php';
    }

    public function saveProduct(): void {
        Csrf::verifyOrFail($_POST['csrf_token'] ?? '');
        UploadService::clearUploadedFiles();
        $id = (int)($_POST['product_id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        $artistId = (int)($_POST['artist_id'] ?? 0);
        $category = $_POST['category'] ?? 'vinyl';
        $price = (float)($_POST['price'] ?? 0);
        $stock = (int)($_POST['stock'] ?? 0);
        $genre = trim($_POST['genre'] ?? '');
        $colorVariant = trim($_POST['color_variant'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $videoUrl = trim($_POST['video_url'] ?? '');
        $videoThumb = trim($_POST['video_thumb_url'] ?? '');
        $isLimited = !empty($_POST['is_limited']);
        $isActive = $id > 0 ? !empty($_POST['is_active']) : true; // new products are active by default
        $dropEndsAt = !empty($_POST['drop_ends_at']) ? $_POST['drop_ends_at'] : null;

        if ($name === '' || $artistId <= 0 || $price < 0) {
            UploadService::cleanupUploadedFiles();
            $_SESSION['toast'] = ['type' => 'error', 'message' => 'Заполните обязательные поля: название, артист, цена.'];
            header('Location: index.php?page=admin&tab=products');
            exit;
        }

        // Cover upload
        $coverUrl = trim($_POST['cover_url'] ?? '');
        $coverThumb = trim($_POST['cover_thumb_url'] ?? '');
        try {
            if (!empty($_FILES['cover']['name'])) {
                $coverUrl = UploadService::handleImageUpload($_FILES['cover'], 'covers', $coverUrl);
                $coverThumb = UploadService::lastThumbnail() ?? $coverThumb;
            }
        } catch (Exception $e) {
            UploadService::cleanupUploadedFiles();
            $_SESSION['toast'] = ['type' => 'error', 'message' => 'Ошибка загрузки обложки: ' . $e->getMessage()];
            header('Location: index.php?page=admin&tab=products');
            exit;
        }

        // Video file upload (MP4) with ffmpeg poster frame
        try {
            $videoUpload = UploadService::handleVideoUpload($_FILES['video_file'] ?? []);
            if (!empty($videoUpload['url'])) {
                $videoUrl = $videoUpload['url'];
                $videoThumb = $videoUpload['thumb'] ?? $videoThumb;
            }
        } catch (Exception $e) {
            UploadService::cleanupUploadedFiles();
            $_SESSION['toast'] = ['type' => 'error', 'message' => 'Ошибка загрузки видео: ' . $e->getMessage()];
            header('Location: index.php?page=admin&tab=products');
            exit;
        }

        if (empty($coverUrl)) {
            $coverUrl = 'https://images.unsplash.com/photo-1539375665275-f9de415ef9ac?auto=format&fit=crop&w=1000&q=85';
        }

        $data = [
            'name' => $name,
            'artist_id' => $artistId,
            'category' => $category,
            'price' => $price,
            'stock' => $stock,
            'genre' => $genre,
            'color_variant' => $colorVariant,
            'description' => $description,
            'cover_url' => $coverUrl,
            'cover_thumb_url' => $coverThumb ?: null,
            'video_url' => $videoUrl ?: null,
            'video_thumb_url' => $videoThumb ?: null,
            'is_limited' => $isLimited ? 1 : 0,
            'is_active' => $isActive ? 1 : 0,
            'drop_ends_at' => $dropEndsAt
        ];

        try {
            if ($id > 0) {
                $this->productModel->update($id, $data);
                $productId = $id;
                $_SESSION['toast'] = ['type' => 'success', 'message' => "Товар «{$name}» успешно обновлен! Клиенты видят актуальные данные."];
            } else {
                $productId = $this->productModel->create($data);
                $_SESSION['toast'] = ['type' => 'success', 'message' => "Товар «{$name}» успешно добавлен в каталог!"];
            }

            // Process tracks & audio uploads
            $tracks = [];
            $titles = $_POST['track_titles'] ?? [];
            $previews = $_POST['track_previews'] ?? [];
            $durations = $_POST['track_durations'] ?? [];
            $files = $_FILES['track_files'] ?? [];

            foreach ($titles as $idx => $title) {
                $title = trim($title);
                if ($title === '') continue;

                $previewUrl = trim($previews[$idx] ?? '');

                // Check if file was uploaded for this track
                if (!empty($files['name'][$idx]) && $files['error'][$idx] === UPLOAD_ERR_OK) {
                    $fileArr = [
                        'name' => $files['name'][$idx],
                        'type' => $files['type'][$idx],
                        'tmp_name' => $files['tmp_name'][$idx],
                        'error' => $files['error'][$idx],
                        'size' => $files['size'][$idx]
                    ];
                    try {
                        $uploadedAudio = UploadService::handleAudioUpload($fileArr);
                        if ($uploadedAudio) {
                            $previewUrl = $uploadedAudio;
                        }
                    } catch (Exception $e) {
                        // Ignore track audio upload error and fall back to previewUrl
                    }
                }

                $tracks[] = [
                    'title' => $title,
                    'preview_url' => $previewUrl,
                    'duration_seconds' => (int)($durations[$idx] ?? 15)
                ];
            }

            $this->productModel->saveTracks($productId, $tracks);
        } catch (Throwable $e) {
            UploadService::cleanupUploadedFiles();
            throw $e;
        }

        header('Location: index.php?page=admin&tab=products');
        exit;
    }

    public function deleteProduct(): void {
        Csrf::verifyOrFail($_POST['csrf_token'] ?? '');
        $id = (int)($_POST['product_id'] ?? 0);
        if ($id > 0) {
            $this->productModel->delete($id);
            $_SESSION['toast'] = ['type' => 'info', 'message' => 'Товар удален из каталога.'];
        }
        header('Location: index.php?page=admin&tab=products');
        exit;
    }

    /** Bulk actions over the product list: delete, activate/hide, stock update. */
    public function bulkProducts(): void {
        Csrf::verifyOrFail($_POST['csrf_token'] ?? '');
        $action = $_POST['bulk_action'] ?? '';
        $ids = array_map('intval', (array)($_POST['product_ids'] ?? []));
        $ids = array_filter($ids, fn($i) => $i > 0);

        if (!$ids || $action === '') {
            $_SESSION['toast'] = ['type' => 'error', 'message' => 'Выберите товары и действие.'];
            header('Location: index.php?page=admin&tab=products');
            exit;
        }

        $count = match ($action) {
            'delete' => $this->productModel->bulkDelete($ids),
            'activate' => $this->productModel->bulkSetActive($ids, true),
            'deactivate' => $this->productModel->bulkSetActive($ids, false),
            'stock' => $this->productModel->bulkSetStock($ids, max(0, (int)($_POST['bulk_stock'] ?? 0))),
            default => 0,
        };

        $labels = ['delete' => 'Удалено товаров', 'activate' => 'Активировано', 'deactivate' => 'Скрыто', 'stock' => 'Остаток обновлен для'];
        $_SESSION['toast'] = ['type' => 'success', 'message' => ($labels[$action] ?? 'Обработано') . ": {$count}"];
        header('Location: index.php?page=admin&tab=products');
        exit;
    }

    /** Export product catalog as CSV. */
    public function exportProducts(): void {
        (new ProductCsv($this->db))->export();
    }

    /** Import product catalog from an uploaded CSV. */
    public function importProducts(): void {
        Csrf::verifyOrFail($_POST['csrf_token'] ?? '');
        if (empty($_FILES['csv_file']['tmp_name']) || $_FILES['csv_file']['error'] !== UPLOAD_ERR_OK) {
            $_SESSION['toast'] = ['type' => 'error', 'message' => 'Выберите CSV-файл для импорта.'];
            header('Location: index.php?page=admin&tab=import');
            exit;
        }

        $content = file_get_contents($_FILES['csv_file']['tmp_name']);
        if ($content === false) {
            $_SESSION['toast'] = ['type' => 'error', 'message' => 'Не удалось прочитать файл.'];
            header('Location: index.php?page=admin&tab=import');
            exit;
        }

        // Strip UTF-8 BOM if present
        if (str_starts_with($content, "\xEF\xBB\xBF")) {
            $content = substr($content, 3);
        }

        $result = (new ProductCsv($this->db))->import($content);

        $msg = "Импорт завершен: добавлено {$result['created']}, обновлено {$result['updated']}, пропущено {$result['skipped']}.";
        if ($result['errors']) {
            $msg .= ' ' . implode(' ', array_slice($result['errors'], 0, 3));
        }
        $_SESSION['toast'] = ['type' => $result['created'] + $result['updated'] > 0 ? 'success' : 'error', 'message' => $msg];
        header('Location: index.php?page=admin&tab=import');
        exit;
    }

    /** Download sales report as CSV. */
    public function exportReport(): void {
        $from = preg_match('/^\d{4}-\d{2}-\d{2}$/', $_GET['report_from'] ?? '') ? $_GET['report_from'] : date('Y-m-01');
        $to = preg_match('/^\d{4}-\d{2}-\d{2}$/', $_GET['report_to'] ?? '') ? $_GET['report_to'] : date('Y-m-d');
        $category = trim($_GET['report_category'] ?? '');
        $artist = (int)($_GET['report_artist'] ?? 0);
        (new SalesReport($this->db))->downloadCsv($from, $to, $category, $artist);
    }

    public function saveArtist(): void {
        Csrf::verifyOrFail($_POST['csrf_token'] ?? '');
        UploadService::clearUploadedFiles();
        $id = (int)($_POST['artist_id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        $genre = trim($_POST['genre'] ?? '');
        $imageUrl = trim($_POST['image_url'] ?? '');

        if ($name === '') {
            UploadService::cleanupUploadedFiles();
            $_SESSION['toast'] = ['type' => 'error', 'message' => 'Имя артиста обязательно.'];
            header('Location: index.php?page=admin&tab=artists');
            exit;
        }

        try {
            if (!empty($_FILES['image']['name'])) {
                $imageUrl = UploadService::handleImageUpload($_FILES['image'], 'artists', $imageUrl);
            }
        } catch (Exception $e) {
            UploadService::cleanupUploadedFiles();
            $_SESSION['toast'] = ['type' => 'error', 'message' => 'Ошибка загрузки фото артиста: ' . $e->getMessage()];
            header('Location: index.php?page=admin&tab=artists');
            exit;
        }

        if (empty($imageUrl)) {
            $imageUrl = 'https://images.unsplash.com/photo-1493225457124-a3eb161ffa5f?auto=format&fit=crop&w=900&q=80';
        }

        $data = [
            'name' => $name,
            'genre' => $genre,
            'image_url' => $imageUrl
        ];

        try {
            if ($id > 0) {
                $this->artistModel->update($id, $data);
                $_SESSION['toast'] = ['type' => 'success', 'message' => "Данные артиста «{$name}» обновлены!"];
            } else {
                $this->artistModel->create($data);
                $_SESSION['toast'] = ['type' => 'success', 'message' => "Артист «{$name}» успешно добавлен!"];
            }
        } catch (Throwable $e) {
            UploadService::cleanupUploadedFiles();
            throw $e;
        }

        header('Location: index.php?page=admin&tab=artists');
        exit;
    }

    public function deleteArtist(): void {
        Csrf::verifyOrFail($_POST['csrf_token'] ?? '');
        $id = (int)($_POST['artist_id'] ?? 0);
        if ($id > 0) {
            $this->artistModel->delete($id);
            $_SESSION['toast'] = ['type' => 'info', 'message' => 'Артист удален.'];
        }
        header('Location: index.php?page=admin&tab=artists');
        exit;
    }

    public function deleteReview(): void {
        Csrf::verifyOrFail($_POST['csrf_token'] ?? '');
        $id = (int)($_POST['review_id'] ?? 0);
        if ($id > 0) {
            $this->reviewModel->delete($id);
            $_SESSION['toast'] = ['type' => 'info', 'message' => 'Отзыв удален.'];
        }
        header('Location: index.php?page=admin&tab=reviews');
        exit;
    }

    /** Approve/reject a review from the moderation queue. */
    public function moderateReview(): void {
        Csrf::verifyOrFail($_POST['csrf_token'] ?? '');
        $id = (int)($_POST['review_id'] ?? 0);
        $decision = $_POST['decision'] ?? '';

        if ($id > 0 && in_array($decision, ['approved', 'rejected'], true)) {
            $this->reviewModel->setStatus($id, $decision);
            $_SESSION['toast'] = ['type' => 'success', 'message' => $decision === 'approved' ? 'Отзыв одобрен и опубликован.' : 'Отзыв отклонен.'];
        }
        header('Location: index.php?page=admin&tab=moderation');
        exit;
    }

    /** Bulk approve/reject reviews. */
    public function bulkModerateReviews(): void {
        Csrf::verifyOrFail($_POST['csrf_token'] ?? '');
        $decision = $_POST['decision'] === 'approved' ? 'approved' : 'rejected';
        $ids = array_filter(array_map('intval', (array)($_POST['review_ids'] ?? [])));
        $count = $this->reviewModel->bulkSetStatus($ids, $decision);
        $_SESSION['toast'] = ['type' => 'success', 'message' => "Обработано отзывов: {$count}."];
        header('Location: index.php?page=admin&tab=moderation');
        exit;
    }

    /** Change a user's role. */
    public function updateUserRole(): void {
        Csrf::verifyOrFail($_POST['csrf_token'] ?? '');
        $id = (int)($_POST['user_id'] ?? 0);
        $role = $_POST['role'] ?? 'customer';
        if ($id > 0 && $id !== (int)User::current()['id'] && $this->userModel->setRole($id, $role)) {
            $_SESSION['toast'] = ['type' => 'success', 'message' => "Роль пользователя #{$id} изменена на «{$role}»."];
        }
        header('Location: index.php?page=admin&tab=users');
        exit;
    }

    /** Ban / unban a user. */
    public function toggleUserBan(): void {
        Csrf::verifyOrFail($_POST['csrf_token'] ?? '');
        $id = (int)($_POST['user_id'] ?? 0);
        $ban = ($_POST['ban'] ?? '0') === '1';
        $reason = trim($_POST['reason'] ?? '');

        if ($id > 0 && $id !== (int)User::current()['id'] && $this->userModel->setBanned($id, $ban, $reason)) {
            $_SESSION['toast'] = ['type' => $ban ? 'info' : 'success', 'message' => $ban ? "Пользователь #{$id} заблокирован." : "Пользователь #{$id} разблокирован."];
        }
        header('Location: index.php?page=admin&tab=users');
        exit;
    }

    /** Log in as another user (support/debugging). */
    public function impersonate(): void {
        Csrf::verifyOrFail($_POST['csrf_token'] ?? '');
        $id = (int)($_POST['user_id'] ?? 0);
        if ($id > 0 && $id !== (int)User::current()['id']) {
            $full = $this->userModel->findFullById($id);
            if ($full && empty($full['is_banned'])) {
                User::startImpersonation($id, [
                    'id' => (int)$full['id'],
                    'name' => $full['name'],
                    'email' => $full['email'],
                    'role' => $full['role'],
                    'avatar_url' => $full['avatar_url'],
                ]);
                $_SESSION['toast'] = ['type' => 'info', 'message' => "Вы вошли как {$full['name']}. Не забудьте вернуться."];
                header('Location: index.php');
                exit;
            }
        }
        header('Location: index.php?page=admin&tab=users');
        exit;
    }

    /** Return to the admin account after impersonation. */
    public function stopImpersonate(): void {
        User::stopImpersonation();
        $_SESSION['toast'] = ['type' => 'success', 'message' => 'Вы вернулись в аккаунт администратора.'];
        header('Location: index.php?page=admin');
        exit;
    }

    /** Update track ordering after drag-and-drop. */
    public function reorderTracks(): void {
        header('Content-Type: application/json');
        Csrf::verifyOrFail($_POST['csrf_token'] ?? '');
        $productId = (int)($_POST['product_id'] ?? 0);
        $order = array_map('intval', (array)($_POST['track_order'] ?? []));

        if ($productId <= 0 || !$order) {
            echo json_encode(['ok' => false, 'error' => 'bad request']);
            return;
        }

        // track_order: comma-separated track ids in the new visual order
        $ids = array_filter($order, fn($i) => $i > 0);
        $stmt = $this->db->prepare("UPDATE tracks SET track_number = ? WHERE id = ? AND product_id = ?");
        $num = 1;
        foreach ($ids as $trackId) {
            $stmt->execute([$num++, $trackId, $productId]);
        }
        echo json_encode(['ok' => true, 'updated' => count($ids)]);
    }

    public function updateOrderStatus(): void {
        Csrf::verifyOrFail($_POST['csrf_token'] ?? '');
        $orderId = (int)($_POST['order_id'] ?? 0);
        $status = $_POST['status'] ?? 'pending';

        if ($orderId > 0 && in_array($status, ['pending', 'paid', 'shipped', 'cancelled'], true)) {
            $stmt = $this->db->prepare("UPDATE orders SET status = ? WHERE id = ?");
            $stmt->execute([$status, $orderId]);
            $_SESSION['toast'] = ['type' => 'success', 'message' => "Статус заказа #{$orderId} изменен на «{$status}»."];
        }
        header('Location: index.php?page=admin&tab=orders');
        exit;
    }

    /** Bulk order status update. */
    public function bulkOrderStatus(): void {
        Csrf::verifyOrFail($_POST['csrf_token'] ?? '');
        $status = $_POST['status'] ?? '';
        $ids = array_filter(array_map('intval', (array)($_POST['order_ids'] ?? [])));

        if ($ids && in_array($status, ['pending', 'paid', 'shipped', 'cancelled'], true)) {
            $placeholders = implode(',', array_fill(0, count($ids), '?'));
            $stmt = $this->db->prepare("UPDATE orders SET status = ? WHERE id IN ($placeholders)");
            $stmt->execute(array_merge([$status], $ids));
            $_SESSION['toast'] = ['type' => 'success', 'message' => 'Статус обновлен для заказов: ' . count($ids)];
        }
        header('Location: index.php?page=admin&tab=orders');
        exit;
    }
}
