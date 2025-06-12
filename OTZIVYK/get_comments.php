<?php
include 'includes/db_connect.php';

$review_id = filter_input(INPUT_GET, 'review_id', FILTER_VALIDATE_INT);
$page = filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT) ?? 1;
$per_page = filter_input(INPUT_GET, 'per_page', FILTER_VALIDATE_INT) ?? 5;
$offset = ($page - 1) * $per_page;

if (!$review_id) {
    http_response_code(400);
    exit(json_encode(['success' => false, 'error' => 'Неверный ID отзыва']));
}

try {
    // Получаем общее количество комментариев
    $stmt = $conn->prepare("SELECT COUNT(*) FROM review_comments WHERE review_id = ?");
    $stmt->execute([$review_id]);
    $total_count = $stmt->fetchColumn();
    
    // Получаем комментарии для текущей страницы
    $stmt = $conn->prepare("
        SELECT rc.*, u.username 
        FROM review_comments rc
        JOIN users u ON rc.user_id = u.id
        WHERE rc.review_id = :review_id
        ORDER BY rc.created_at DESC
        LIMIT :limit OFFSET :offset
    ");
    
    // Явно указываем типы параметров
    $stmt->bindValue(':review_id', $review_id, PDO::PARAM_INT);
    $stmt->bindValue(':limit', $per_page, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    
    $comments = $stmt->fetchAll();
    
    // Форматируем результат
    $result = [
        'success' => true,
        'comments' => array_map(function($comment) {
            return [
                'username' => htmlspecialchars($comment['username']),
                'comment' => nl2br(htmlspecialchars($comment['comment'])),
                'created_at' => date('d.m.Y H:i', strtotime($comment['created_at']))
            ];
        }, $comments),
        'total_count' => $total_count
    ];
    
    header('Content-Type: application/json');
    echo json_encode($result);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'error' => 'Ошибка базы данных: ' . $e->getMessage()
    ]);
}