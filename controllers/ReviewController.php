<?php
declare(strict_types=1);

class ReviewController {
    public function add(): void {
        Csrf::verifyOrFail($_POST['csrf_token'] ?? '');
        UploadService::clearUploadedFiles();
        $user = User::current();
        $productId = (int)($_POST['product_id'] ?? 0);

        if (!$user) {
            UploadService::cleanupUploadedFiles();
            $_SESSION['auth_error'] = 'Для публикации отзыва необходимо войти в аккаунт.';
            header("Location: index.php?page=login&return_to=" . urlencode("index.php?page=product&id=$productId#reviews"));
            exit;
        }

        if ($productId <= 0) {
            UploadService::cleanupUploadedFiles();
            header('Location: index.php');
            exit;
        }

        $rating = (int)($_POST['rating'] ?? 5);
        $title = trim($_POST['title'] ?? '');
        $comment = trim($_POST['comment'] ?? '');

        if ($title === '' || $comment === '') {
            UploadService::cleanupUploadedFiles();
            $_SESSION['toast'] = ['type' => 'error', 'message' => 'Пожалуйста, заполните заголовок и текст отзыва.'];
            header("Location: index.php?page=product&id=$productId#review-form");
            exit;
        }

        // Handle photo upload or optional URL
        $photoUrl = null;
        try {
            if (!empty($_FILES['photo']['name'])) {
                $photoUrl = UploadService::handleImageUpload($_FILES['photo'], 'reviews');
            } elseif (!empty($_POST['photo_url'])) {
                $photoUrl = trim($_POST['photo_url']);
            }
        } catch (Exception $e) {
            UploadService::cleanupUploadedFiles();
            $_SESSION['toast'] = ['type' => 'error', 'message' => 'Ошибка загрузки фото: ' . $e->getMessage()];
            header("Location: index.php?page=product&id=$productId#review-form");
            exit;
        }

        try {
            $reviewModel = new Review(Database::connection());
            // New reviews enter the moderation queue; admins see them immediately in the panel.
            $reviewModel->create($productId, (int)$user['id'], $rating, $title, $comment, $photoUrl, 'pending');
        } catch (Throwable $e) {
            UploadService::cleanupUploadedFiles();
            throw $e;
        }

        $_SESSION['toast'] = ['type' => 'success', 'message' => 'Спасибо! Отзыв отправлен на модерацию и появится после проверки.'];
        header("Location: index.php?page=product&id=$productId#reviews");
        exit;
    }
}
