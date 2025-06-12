<?php
session_start();
include 'includes/db_connect.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    exit(json_encode(['success' => false, 'error' => 'Unauthorized']));
}

$review_id = filter_input(INPUT_POST, 'review_id', FILTER_VALIDATE_INT);
$comment = trim(filter_input(INPUT_POST, 'comment', FILTER_SANITIZE_STRING));

if (!$review_id || empty($comment)) {
    http_response_code(400);
    exit(json_encode(['success' => false, 'error' => 'Invalid input']));
}

try {
    $conn->beginTransaction();
    
    // Добавляем комментарий
    $stmt = $conn->prepare("INSERT INTO review_comments (review_id, user_id, comment) VALUES (?, ?, ?)");
    $stmt->execute([$review_id, $_SESSION['user_id'], $comment]);
    $comment_id = $conn->lastInsertId();
    
    // Получаем данные для ответа
    $stmt = $conn->prepare("
        SELECT rc.*, u.username 
        FROM review_comments rc
        JOIN users u ON rc.user_id = u.id
        WHERE rc.id = ?
    ");
    $stmt->execute([$comment_id]);
    $comment_data = $stmt->fetch();
    
    $conn->commit();
    
    echo json_encode([
        'success' => true,
        'comment' => [
            'id' => $comment_data['id'],
            'username' => $comment_data['username'],
            'comment' => nl2br(htmlspecialchars($comment_data['comment'])),
            'created_at' => date('d.m.Y H:i', strtotime($comment_data['created_at']))
        ]
    ]);
} catch (PDOException $e) {
    $conn->rollBack();
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>