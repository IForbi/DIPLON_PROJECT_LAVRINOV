<?php
session_start();
include 'includes/db_connect.php';
include 'includes/functions.php';

// Проверка прав администратора
if (!isset($_SESSION['user_id']) || !isAdmin($_SESSION['user_id'], $conn)) {
    header('Location: index.php');
    exit;
}

// Обработка действий модерации
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $review_id = (int)$_POST['review_id'];
    $action = $_POST['action'];
    $admin_id = $_SESSION['user_id'];

    try {
        $conn->beginTransaction();
        
        // Обновляем статус отзыва
        $stmt = $conn->prepare("UPDATE reviews SET status = ? WHERE id = ?");
        $stmt->execute([$action, $review_id]);
        
        // Записываем действие модерации
        $stmt = $conn->prepare("INSERT INTO pending_reviews (review_id, admin_id, action, action_date) VALUES (?, ?, ?, NOW())");
        $stmt->execute([$review_id, $admin_id, $action]);
        
        $conn->commit();
        
        if ($action === 'approved') {
            $_SESSION['admin_message'] = 'Отзыв успешно одобрен и опубликован';
        } else {
            $_SESSION['admin_message'] = 'Отзыв отклонен';
        }
    } catch (PDOException $e) {
        $conn->rollBack();
        $_SESSION['admin_error'] = 'Ошибка при обработке отзыва: ' . $e->getMessage();
    }
    
    header('Location: admin_moderate_reviews.php');
    exit;
}

// Определяем текущий статус для фильтрации
$status = isset($_GET['status']) ? $_GET['status'] : 'pending';
$valid_statuses = ['pending', 'approved', 'rejected'];
if (!in_array($status, $valid_statuses)) {
    $status = 'pending';
}

// Получаем отзывы для модерации
$pending_reviews = $conn->prepare("
    SELECT r.id, r.title, r.description, r.created_at, r.image, 
           u.username, u.id as user_id, c.name as category_name
    FROM reviews r
    JOIN users u ON r.user_id = u.id
    LEFT JOIN categories c ON r.category_id = c.id
    WHERE r.status = ?
    ORDER BY r.created_at DESC
");
$pending_reviews->execute([$status]);
$reviews = $pending_reviews->fetchAll();

include 'includes/header.php';
?>

<main class="admin-panel">
    <div class="container">
        <h2>Модерация отзывов</h2>
        
        <?php if (isset($_SESSION['admin_message'])): ?>
            <div class="alert alert-success"><?= $_SESSION['admin_message'] ?></div>
            <?php unset($_SESSION['admin_message']); ?>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['admin_error'])): ?>
            <div class="alert alert-danger"><?= $_SESSION['admin_error'] ?></div>
            <?php unset($_SESSION['admin_error']); ?>
        <?php endif; ?>
        
        <div class="status-tabs">
            <a href="?status=pending" class="<?= $status === 'pending' ? 'active' : '' ?>">Ожидают модерации</a>
            <a href="?status=approved" class="<?= $status === 'approved' ? 'active' : '' ?>">Одобренные</a>
            <a href="?status=rejected" class="<?= $status === 'rejected' ? 'active' : '' ?>">Отклоненные</a>
        </div>
        
        <?php if (empty($reviews)): ?>
            <div class="alert alert-info">Нет отзывов с выбранным статусом</div>
        <?php else: ?>
            <div class="admin-table-container">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Заголовок</th>
                            <th>Автор</th>
                            <th>Категория</th>
                            <th>Дата создания</th>
                            <th>Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($reviews as $review): ?>
                            <tr>
                                <td><?= $review['id'] ?></td>
                                <td><?= htmlspecialchars($review['title']) ?></td>
                                <td>
                                    <a href="user_profile.php?id=<?= $review['user_id'] ?>">
                                        <?= htmlspecialchars($review['username']) ?>
                                    </a>
                                </td>
                                <td><?= htmlspecialchars($review['category_name'] ?? 'Без категории') ?></td>
                                <td><?= date('d.m.Y H:i', strtotime($review['created_at'])) ?></td>
                                <td class="actions">
                                    <div class="moderation-actions">
                                        <?php if ($status === 'pending'): ?>
                                            <form method="post" class="inline-form">
                                                <input type="hidden" name="review_id" value="<?= $review['id'] ?>">
                                                <input type="hidden" name="action" value="approved">
                                                <button type="submit" class="btn btn-success">Одобрить</button>
                                            </form>
                                            <form method="post" class="inline-form">
                                                <input type="hidden" name="review_id" value="<?= $review['id'] ?>">
                                                <input type="hidden" name="action" value="rejected">
                                                <button type="submit" class="btn btn-danger">Отклонить</button>
                                            </form>
                                        <?php endif; ?>
                                        <a href="review_preview.php?id=<?= $review['id'] ?>" class="btn btn-primary">Просмотреть</a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</main>

<?php include 'includes/footer.php'; ?>