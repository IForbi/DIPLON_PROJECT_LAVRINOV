<?php
$host = 'localhost';
$dbname = 'reviews_db';
$username = 'root';
$password = '';

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Ошибка подключения к базе данных: " . $e->getMessage());
}

if (!function_exists('isAdmin')) {
    function isAdmin($user_id, $conn) {
        try {
            $stmt = $conn->prepare("SELECT role FROM users WHERE id = ?");
            $stmt->execute([$user_id]);
            $user = $stmt->fetch();
            return $user && $user['role'] === 'admin';
        } catch(PDOException $e) {
            error_log("Ошибка проверки прав администратора: " . $e->getMessage());
            return false;
        }
    }
}
?>