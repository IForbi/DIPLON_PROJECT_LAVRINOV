<?php
session_start();
include 'includes/db_connect.php';
include 'includes/functions.php';

$review_id = (int)$_GET['id'];

// Получаем информацию об отзыве
$stmt = $conn->prepare("
    SELECT r.*, u.username, u.id as user_id, c.name as category_name
    FROM reviews r
    JOIN users u ON r.user_id = u.id
    LEFT JOIN categories c ON r.category_id = c.id
    WHERE r.id = ?
");
$stmt->execute([$review_id]);
$review = $stmt->fetch();

if (!$review) {
    header('Location: index.php');
    exit;
}

// Проверка прав на просмотр
$can_view = false;
if ($review['status'] === 'approved') {
    $can_view = true;
} elseif (isset($_SESSION['user_id'])) {
    if ($_SESSION['user_id'] == $review['user_id'] || isAdmin($_SESSION['user_id'], $conn)) {
        $can_view = true;
    }
}

if (!$can_view) {
    $_SESSION['error'] = 'У вас нет прав для просмотра этого отзыва';
    header('Location: index.php');
    exit;
}

// Получаем дополнительные изображения
$images = $conn->prepare("SELECT image_path FROM review_images WHERE review_id = ?");
$images->execute([$review_id]);
$additional_images = $images->fetchAll();

// Получаем информацию о модерации, если есть
$moderation_info = null;
if (isAdmin($_SESSION['user_id'] ?? 0, $conn)) {
    $mod_info = $conn->prepare("
        SELECT pr.action, pr.action_date, u.username as admin_name
        FROM pending_reviews pr
        JOIN users u ON pr.admin_id = u.id
        WHERE pr.review_id = ?
        ORDER BY pr.action_date DESC
        LIMIT 1
    ");
    $mod_info->execute([$review_id]);
    $moderation_info = $mod_info->fetch();
}

include 'includes/header.php';
?>

<main>
    <div class="content-container">
        <h2>Просмотр отзыва</h2>
        
        <?php if ($review['status'] === 'pending'): ?>
            <div class="alert alert-warning">Этот отзыв ожидает модерации</div>
        <?php elseif ($review['status'] === 'rejected'): ?>
            <div class="alert alert-danger">Этот отзыв был отклонен</div>
        <?php endif; ?>
        
        <?php if ($moderation_info): ?>
            <div class="moderation-info">
                <p>Статус: <?= $moderation_info['action'] === 'approved' ? 'Одобрен' : 'Отклонен' ?></p>
                <p>Модератор: <?= htmlspecialchars($moderation_info['admin_name']) ?></p>
                <p>Дата: <?= date('d.m.Y H:i', strtotime($moderation_info['action_date'])) ?></p>
            </div>
        <?php endif; ?>
        
        <div class="review-full">
            <?php if ($review['image']): ?>
                <div class="review-main-image">
                    <img src="<?= htmlspecialchars($review['image']) ?>" alt="Обложка отзыва">
                </div>
            <?php endif; ?>
            
            <div class="review-content">
                <h3><?= htmlspecialchars($review['title']) ?></h3>
                <div class="review-meta">
                    <span class="author"><i class="fas fa-user"></i> <?= htmlspecialchars($review['username']) ?></span>
                    <span class="category"><i class="fas fa-tag"></i> <?= htmlspecialchars($review['category_name']) ?></span>
                    <span class="date"><i class="fas fa-calendar-alt"></i> <?= date('d.m.Y H:i', strtotime($review['created_at'])) ?></span>
                    <span class="status badge <?= $review['status'] === 'approved' ? 'badge-success' : ($review['status'] === 'rejected' ? 'badge-danger' : 'badge-warning') ?>">
                        <?= $review['status'] === 'approved' ? 'Одобрен' : ($review['status'] === 'rejected' ? 'Отклонен' : 'На модерации') ?>
                    </span>
                </div>
                <div class="review-text">
                    <?= nl2br(htmlspecialchars($review['description'])) ?>
                </div>
                
                <?php if (!empty($additional_images)): ?>
                    <div class="additional-images">
                        <h4>Дополнительные изображения</h4>
                        <div class="images-grid">
                            <?php foreach ($additional_images as $image): ?>
                                <div class="image-item">
                                    <img src="<?= htmlspecialchars($image['image_path']) ?>" alt="Дополнительное изображение">
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <?php if (isAdmin($_SESSION['user_id'] ?? 0, $conn) && $review['status'] === 'pending'): ?>
            <div class="moderation-actions">
                <form method="post" action="admin_moderate_reviews.php" class="inline-form">
                    <input type="hidden" name="review_id" value="<?= $review['id'] ?>">
                    <input type="hidden" name="action" value="approved">
                    <button type="submit" class="btn btn-success">Одобрить</button>
                </form>
                <form method="post" action="admin_moderate_reviews.php" class="inline-form">
                    <input type="hidden" name="review_id" value="<?= $review['id'] ?>">
                    <input type="hidden" name="action" value="rejected">
                    <button type="submit" class="btn btn-danger">Отклонить</button>
                </form>
                <a href="admin_moderate_reviews.php" class="btn btn-primary">Вернуться к списку</a>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $review['user_id']): ?>
            <div class="user-actions">
                <?php if ($review['status'] === 'rejected'): ?>
                    <a href="edit_review.php?id=<?= $review['id'] ?>" class="btn btn-warning">Редактировать и отправить снова</a>
                <?php endif; ?>
                <a href="profile.php" class="btn btn-primary">Вернуться в профиль</a>
            </div>
        <?php endif; ?>
    </div>
</main>

<?php include 'includes/footer.php'; ?>