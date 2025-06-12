<?php
session_start();
include 'includes/db_connect.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    exit(json_encode(['success' => false, 'error' => 'Требуется авторизация']));
}

$review_id = filter_input(INPUT_POST, 'review_id', FILTER_VALIDATE_INT);
$action = filter_input(INPUT_POST, 'action', FILTER_SANITIZE_STRING);

if (!$review_id || !in_array($action, ['like', 'dislike'])) {
    http_response_code(400);
    exit(json_encode(['success' => false, 'error' => 'Неверные параметры']));
}

try {
    $rating = ($action === 'like') ? 1 : -1;
    $user_id = $_SESSION['user_id'];
    
    // Проверяем существующую оценку
    $stmt = $conn->prepare("SELECT id, rating FROM review_ratings WHERE review_id = ? AND user_id = ?");
    $stmt->execute([$review_id, $user_id]);
    $existing = $stmt->fetch();
    
    if ($existing) {
        if ($existing['rating'] == $rating) {
            // Удаляем оценку, если клик на ту же кнопку
            $stmt = $conn->prepare("DELETE FROM review_ratings WHERE id = ?");
            $stmt->execute([$existing['id']]);
            $user_rating = 0;
        } else {
            // Меняем оценку на противоположную
            $stmt = $conn->prepare("UPDATE review_ratings SET rating = ? WHERE id = ?");
            $stmt->execute([$rating, $existing['id']]);
            $user_rating = $rating;
        }
    } else {
        // Добавляем новую оценку
        $stmt = $conn->prepare("INSERT INTO review_ratings (review_id, user_id, rating) VALUES (?, ?, ?)");
        $stmt->execute([$review_id, $user_id, $rating]);
        $user_rating = $rating;
    }
    
    // Получаем обновленные счетчики
    $stmt = $conn->prepare("SELECT 
        SUM(CASE WHEN rating = 1 THEN 1 ELSE 0 END) as likes,
        SUM(CASE WHEN rating = -1 THEN 1 ELSE 0 END) as dislikes
        FROM review_ratings WHERE review_id = ?");
    $stmt->execute([$review_id]);
    $counts = $stmt->fetch();
    
    echo json_encode([
        'success' => true,
        'likes' => $counts['likes'] ?? 0,
        'dislikes' => $counts['dislikes'] ?? 0,
        'user_rating' => $user_rating
    ]);
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Ошибка базы данных: ' . $e->getMessage()]);
}
?>