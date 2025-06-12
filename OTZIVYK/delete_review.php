<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: auth.php'); // Перенаправляем на страницу авторизации, если пользователь не авторизован
    exit;
}

include 'includes/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['review_id'])) {
    $review_id = $_POST['review_id'];
    $user_id = $_SESSION['user_id'];

    // Проверяем, что отзыв принадлежит текущему пользователю
    $stmt = $conn->prepare("SELECT id, image FROM reviews WHERE id = ? AND user_id = ?");
    $stmt->execute([$review_id, $user_id]);
    $review = $stmt->fetch();

    if ($review) {
        // Удаляем изображение отзыва, если оно есть
        if ($review['image'] && file_exists($review['image'])) {
            unlink($review['image']); // Удаляем файл изображения
        }

        // Удаляем отзыв из базы данных
        $stmt = $conn->prepare("DELETE FROM reviews WHERE id = ?");
        $stmt->execute([$review_id]);
        echo "Отзыв успешно удален.";
    } else {
        echo "Ошибка: вы не можете удалить этот отзыв.";
    }
}

// Перенаправляем обратно на страницу профиля
header('Location: profile.php');
exit;
?>