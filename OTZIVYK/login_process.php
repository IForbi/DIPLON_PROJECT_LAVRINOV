<?php
session_start();
include 'includes/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    // Проверяем, существует ли пользователь
    $stmt = $conn->prepare("SELECT id, username, password, role FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        // Успешный вход
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role']; // Сохраняем роль в сессии

        // Перенаправляем на главную страницу
        header('Location: index.php');
        exit;
    } else {
        // Неверные данные
        $_SESSION['login_error'] = 'Неверное имя пользователя или пароль';
        header('Location: auth.php');
        exit;
    }
} else {
    header('Location: auth.php');
    exit;
}
?>