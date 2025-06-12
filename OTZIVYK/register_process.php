<?php
session_start();
include 'includes/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    
    // Обработка телефонного номера
    $phone = preg_replace('/\D/', '', $_POST['phone']);
    // Убедимся, что номер начинается с 7 (без +7)
    if (strlen($phone) > 0 && $phone[0] === '7') {
        $phone = '7' . substr($phone, 1);
    }
    
    $role = 'user'; // Добавляем значение по умолчанию для поля role

    // Проверка на уникальность имени пользователя и email
    $stmt = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ? OR phone = ?");
    $stmt->execute([$username, $email, $phone]);
    $user = $stmt->fetch();

    if ($user) {
        echo "<script>showNotification('Пользователь с таким именем, email или телефоном уже существует.', true);</script>";
    } else {
        // Вставка данных в базу данных (теперь включаем поле role)
        $stmt = $conn->prepare("INSERT INTO users (username, email, password, phone, role) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$username, $email, $password, $phone, $role]);

        // Уведомление об успешной регистрации
        $_SESSION['registration_success'] = true;
        header('Location: auth.php'); // Перенаправляем на страницу входа
        exit;
    }
}
?>