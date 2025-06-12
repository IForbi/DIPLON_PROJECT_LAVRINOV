<?php
session_start();
include 'includes/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'];
    $category_id = $_POST['category'];
    $description = $_POST['description'];
    $user_id = $_SESSION['user_id'];

    // Обработка загрузки основного изображения
    $image_path = null;
    if (isset($_FILES['main_image']) && $_FILES['main_image']['error'] === UPLOAD_ERR_OK) {
        $image_name = $_FILES['main_image']['name'];
        $image_tmp = $_FILES['main_image']['tmp_name'];
        $upload_dir = 'uploads/';

        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }

        $unique_name = uniqid() . '_' . basename($image_name);
        $image_path = $upload_dir . $unique_name;

        if (!move_uploaded_file($image_tmp, $image_path)) {
            $_SESSION['error'] = 'Ошибка при загрузке изображения';
            header('Location: profile.php');
            exit;
        }
    }

    try {
        $conn->beginTransaction();
        
        // Сохраняем отзыв в базу данных со статусом "на модерации"
        $stmt = $conn->prepare("INSERT INTO reviews (user_id, category_id, title, description, image, status) VALUES (?, ?, ?, ?, ?, 'pending')");
        $stmt->execute([$user_id, $category_id, $title, $description, $image_path]);
        $review_id = $conn->lastInsertId();

        // Обработка дополнительных изображений
        if (!empty($_FILES['additional_images'])) {
            foreach ($_FILES['additional_images']['tmp_name'] as $key => $tmp_name) {
                if ($_FILES['additional_images']['error'][$key] === UPLOAD_ERR_OK) {
                    $additional_name = $_FILES['additional_images']['name'][$key];
                    $unique_name = uniqid() . '_' . basename($additional_name);
                    $additional_path = $upload_dir . $unique_name;

                    if (move_uploaded_file($tmp_name, $additional_path)) {
                        $stmt = $conn->prepare("INSERT INTO review_images (review_id, image_path) VALUES (?, ?)");
                        $stmt->execute([$review_id, $additional_path]);
                    }
                }
            }
        }

        $conn->commit();
        $_SESSION['success'] = 'Отзыв отправлен на модерацию! После проверки администратором он будет опубликован.';
        header('Location: profile.php');
        exit;
    } catch (PDOException $e) {
        $conn->rollBack();
        $_SESSION['error'] = 'Ошибка при сохранении отзыва: ' . $e->getMessage();
        header('Location: profile.php');
        exit;
    }
}
?>