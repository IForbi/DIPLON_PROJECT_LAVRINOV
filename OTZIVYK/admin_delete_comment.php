<?php
session_start();
include 'includes/db_connect.php';

// Проверка прав администратора
if (!isset($_SESSION['user_id']) || !isAdmin($_SESSION['user_id'], $conn)) {
    header('Location: index.php');
    exit;
}

// Удаление комментария
if (isset($_POST['delete_comment'])) {
    $comment_id = (int)$_POST['comment_id'];
    
    try {
        $conn->prepare("DELETE FROM review_comments WHERE id = ?")->execute([$comment_id]);
        $_SESSION['admin_message'] = 'Комментарий успешно удален';
    } catch (Exception $e) {
        $_SESSION['admin_error'] = 'Ошибка при удалении комментария: ' . $e->getMessage();
    }
    header('Location: admin_delete_comment.php');
    exit;
}

// Получаем список комментариев (исправленный запрос согласно вашей БД)
$comments = $conn->query("
    SELECT c.id, c.comment, c.created_at, u.username as author, 
           u.id as user_id, r.title as review_title, r.id as review_id
    FROM review_comments c
    JOIN users u ON c.user_id = u.id
    JOIN reviews r ON c.review_id = r.id
    ORDER BY c.created_at DESC
")->fetchAll();

include 'includes/header.php';
?>

<main class="admin-panel">
    <div class="container">
        <h2>Управление комментариями</h2>
        
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
                        <th>Комментарий</th>
                        <th>Автор</th>
                        <th>Отзыв</th>
                        <th>Дата</th>
                        <th>Действия</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($comments as $comment): ?>
                        <tr>
                            <td><?= $comment['id'] ?></td>
                            <td><?= htmlspecialchars(substr($comment['comment'], 0, 50)) ?>...</td>
                            <td>
                                <a href="user_profile.php?id=<?= $comment['user_id'] ?>">
                                    <?= htmlspecialchars($comment['author']) ?>
                                </a>
                            </td>
                            <td>
                                <a href="review.php?id=<?= $comment['review_id'] ?>">
                                    <?= htmlspecialchars($comment['review_title']) ?>
                                </a>
                            </td>
                            <td><?= date('d.m.Y H:i', strtotime($comment['created_at'])) ?></td>
                            <td class="actions">
                                <form method="post" class="inline-form" onsubmit="return confirm('Вы уверены, что хотите удалить этот комментарий?');">
                                    <input type="hidden" name="comment_id" value="<?= $comment['id'] ?>">
                                    <button type="submit" name="delete_comment" class="btn btn-danger">Удалить</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>

<?php include 'includes/footer.php'; ?>