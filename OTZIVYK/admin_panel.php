<?php
session_start();
include 'includes/db_connect.php';

// Проверка прав администратора
if (!isset($_SESSION['user_id']) || !isAdmin($_SESSION['user_id'], $conn)) {
    header('Location: index.php');
    exit;
}

// Получаем статистику
$stats = [
    'users' => $conn->query("SELECT COUNT(*) FROM users")->fetchColumn(),
    'reviews' => $conn->query("SELECT COUNT(*) FROM reviews")->fetchColumn(),
    'comments' => $conn->query("SELECT COUNT(*) FROM review_comments")->fetchColumn(),
    'categories' => $conn->query("SELECT COUNT(*) FROM categories")->fetchColumn()
];

// Получаем последние действия
$latestActions = $conn->query("
    SELECT 'review' AS type, id, title, created_at FROM reviews
    UNION ALL
    SELECT 'comment' AS type, id, comment AS title, created_at FROM review_comments
    UNION ALL
    SELECT 'user' AS type, id, username AS title, created_at FROM users
    ORDER BY created_at DESC
    LIMIT 10
")->fetchAll();

include 'includes/header.php';
?>

<main class="admin-panel">
    <div class="container">
        <h2>Панель администратора</h2>
        
        <div class="admin-nav">
            <a href="admin_users.php" class="btn">Управление пользователями</a>
            <a href="admin_delete_review.php" class="btn">Управление отзывами</a>
            <a href="admin_delete_comment.php" class="btn">Управление комментариями</a>
        </div>
        
        <div class="admin-stats">
            <div class="stat-card">
                <h3>Пользователи</h3>
                <p><?= $stats['users'] ?></p>
            </div>
            <div class="stat-card">
                <h3>Отзывы</h3>
                <p><?= $stats['reviews'] ?></p>
            </div>
            <div class="stat-card">
                <h3>Комментарии</h3>
                <p><?= $stats['comments'] ?></p>
            </div>
            <div class="stat-card">
                <h3>Категории</h3>
                <p><?= $stats['categories'] ?></p>
            </div>
        </div>
        
        <div class="latest-actions">
            <h3>Последние действия</h3>
            <ul>
                <?php foreach ($latestActions as $action): ?>
                    <li>
                        [<?= strtoupper($action['type']) ?>] 
                        <?= htmlspecialchars(mb_substr($action['title'], 0, 50)) ?>
                        <small><?= date('d.m.Y H:i', strtotime($action['created_at'])) ?></small>
                         </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
</main>

<?php include 'includes/footer.php'; ?>

<?php
function isAdmin($user_id, $conn) {
    $stmt = $conn->prepare("SELECT role FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();
    return $user && $user['role'] === 'admin';
}
?>