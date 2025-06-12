<?php
session_start();
include 'includes/db_connect.php';

// Проверка прав администратора
if (!isset($_SESSION['user_id']) || !isAdmin($_SESSION['user_id'], $conn)) {
    header('Location: index.php');
    exit;
}

// Удаление пользователя
if (isset($_POST['delete_user'])) {
    $user_id = (int)$_POST['user_id'];
    if ($user_id != $_SESSION['user_id']) { // Нельзя удалить себя
        // Удаляем все связанные данные пользователя
        $conn->beginTransaction();
        try {
            // Удаляем комментарии пользователя
            $conn->prepare("DELETE FROM review_comments WHERE user_id = ?")->execute([$user_id]);
            // Удаляем оценки пользователя
            $conn->prepare("DELETE FROM review_ratings WHERE user_id = ?")->execute([$user_id]);
            // Удаляем отзывы пользователя (и связанные изображения)
            $stmt = $conn->prepare("SELECT id FROM reviews WHERE user_id = ?");
            $stmt->execute([$user_id]);
            $reviews = $stmt->fetchAll();
            foreach ($reviews as $review) {
                $conn->prepare("DELETE FROM review_images WHERE review_id = ?")->execute([$review['id']]);
                $conn->prepare("DELETE FROM review_comments WHERE review_id = ?")->execute([$review['id']]);
                $conn->prepare("DELETE FROM review_ratings WHERE review_id = ?")->execute([$review['id']]);
            }
            $conn->prepare("DELETE FROM reviews WHERE user_id = ?")->execute([$user_id]);
            // Удаляем самого пользователя
            $conn->prepare("DELETE FROM users WHERE id = ?")->execute([$user_id]);
            $conn->commit();
            $_SESSION['admin_message'] = 'Пользователь и все связанные данные успешно удалены';
        } catch (Exception $e) {
            $conn->rollBack();
            $_SESSION['admin_error'] = 'Ошибка при удалении пользователя: ' . $e->getMessage();
        }
    } else {
        $_SESSION['admin_error'] = 'Вы не можете удалить себя';
    }
    header('Location: admin_users.php');
    exit;
}

// Назначение/снятие прав администратора
if (isset($_POST['toggle_admin'])) {
    $user_id = (int)$_POST['user_id'];
    if ($user_id != $_SESSION['user_id']) { // Нельзя изменить свои права
        $stmt = $conn->prepare("UPDATE users SET role = IF(role='admin', 'user', 'admin') WHERE id = ?");
        $stmt->execute([$user_id]);
        $_SESSION['admin_message'] = 'Права пользователя успешно изменены';
    } else {
        $_SESSION['admin_error'] = 'Вы не можете изменить свои права';
    }
    header('Location: admin_users.php');
    exit;
}

// Получаем список пользователей
$users = $conn->query("SELECT id, username, email, role, created_at FROM users ORDER BY created_at DESC")->fetchAll();

include 'includes/header.php';
?>

<main class="admin-panel">
    <div class="container">
        <h2>Управление пользователями</h2>
        
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
                        <th>Имя пользователя</th>
                        <th>Email</th>
                        <th>Роль</th>
                        <th>Дата регистрации</th>
                        <th>Действия</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td><?= $user['id'] ?></td>
                            <td><?= htmlspecialchars($user['username']) ?></td>
                            <td><?= htmlspecialchars($user['email']) ?></td>
                            <td><?= $user['role'] === 'admin' ? 'Администратор' : 'Пользователь' ?></td>
                            <td><?= date('d.m.Y H:i', strtotime($user['created_at'])) ?></td>
                            <td class="actions">
                                <form method="post" class="inline-form" onsubmit="return confirm('Вы уверены?');">
                                    <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                    <button type="submit" name="delete_user" class="btn btn-danger">Удалить</button>
                                </form>
                                <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                    <form method="post" class="inline-form">
                                        <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                        <button type="submit" name="toggle_admin" class="btn btn-admin">
                                            <?= $user['role'] === 'admin' ? 'Снять права' : 'Назначить админом' ?>
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>

<?php include 'includes/footer.php'; ?>