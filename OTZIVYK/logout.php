<?php
session_start(); // Начинаем сессию
session_unset(); // Очищаем все данные сессии
session_destroy(); // Уничтожаем сессию
header('Location: index.php'); // Перенаправляем на главную страницу
exit;
?>