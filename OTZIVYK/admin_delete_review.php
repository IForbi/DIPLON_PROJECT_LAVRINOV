<?php
session_start();
include 'includes/db_connect.php';

// Проверка прав администратора
if (!isset($_SESSION['user_id']) || !isAdmin($_SESSION['user_id'], $conn)) {
    header('Location: index.php');
    exit;
}

// Удаление отзыва
if (isset($_POST['delete_review'])) {
    $review_id = (int)$_POST['review_id'];
    
    $conn->beginTransaction();
    try {
        // Удаляем изображения отзыва
        $stmt = $conn->prepare("SELECT image_path FROM review_images WHERE review_id = ?");
        $stmt->execute([$review_id]);
        $images = $stmt->fetchAll();
        
        foreach ($images as $image) {
            if (file_exists($image['image_path'])) {
                unlink($image['image_path']);
            }
        }
        $conn->prepare("DELETE FROM review_images WHERE review_id = ?")->execute([$review_id]);
        
        // Удаляем комментарии к отзыву
        $conn->prepare("DELETE FROM review_comments WHERE review_id = ?")->execute([$review_id]);
        
        // Удаляем оценки отзыва
        $conn->prepare("DELETE FROM review_ratings WHERE review_id = ?")->execute([$review_id]);
        
        // Удаляем сам отзыв
        $conn->prepare("DELETE FROM reviews WHERE id = ?")->execute([$review_id]);
        
        $conn->commit();
        $_SESSION['admin_message'] = 'Отзыв успешно удален';
    } catch (Exception $e) {
        $conn->rollBack();
        $_SESSION['admin_error'] = 'Ошибка при удалении отзыва: ' . $e->getMessage();
    }
    header('Location: admin_delete_review.php');
    exit;
}

// Получаем список отзывов (исправленный запрос согласно вашей БД)
$reviews = $conn->query("
    SELECT r.id, r.title, r.description, r.created_at, u.username, u.id as user_id, c.name as category_name
    FROM reviews r
    JOIN users u ON r.user_id = u.id
    LEFT JOIN categories c ON r.category_id = c.id
    ORDER BY r.created_at DESC
")->fetchAll();

include 'includes/header.php';
?>

<main class="admin-panel">
    <div class="container">
        <h2>Управление отзывами</h2>
        
        <?php if (isset($_SESSION['admin_message'])): ?>
            <div class="alert success"><?= $_SESSION['admin_message'] ?></div>
            <?php unset($_SESSION['admin_message']); ?>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['admin_error'])): ?>
            <div class="alert error"><?= $_SESSION['admin_error'] ?></div>
            <?php unset($_SESSION['admin_error']); ?>
        <?php endif; ?>
        
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
                                <form method="post" class="inline-form" onsubmit="return confirm('Вы уверены, что хотите удалить этот отзыв и все связанные данные?');">
                                    <input type="hidden" name="review_id" value="<?= $review['id'] ?>">
                                    <button type="submit" name="delete_review" class="btn btn-danger">Удалить</button>
                                </form>
                                <a href="review.php?id=<?= $review['id'] ?>" class="btn btn-view">Просмотреть</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>

<?php include 'includes/footer.php'; ?>