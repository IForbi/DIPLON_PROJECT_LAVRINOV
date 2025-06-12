<?php
session_start();
include 'includes/db_connect.php';
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Отзывы на все случаи жизни. RevByYou.</title>
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body>
    <!-- Блок для уведомлений -->
    <div id="notification" class="notification">
        <span id="notification-message"></span>
    </div>
    <header>
        <div class="header-content">
            <div><img src="needs/logo.png" alt="logo" class="logo" /></div>
            <!-- Кнопка "Главная" -->
            <a href="index.php" class="btn">Главная</a>

            <!-- Поиск с иконкой внутри поля -->
            <div class="header-search">
                <form action="search.php" method="get" class="quick-search-form">
                    <input type="text" name="query" placeholder="Поиск отзывов..." value="<?= htmlspecialchars($_GET['query'] ?? '') ?>">
                    <button type="submit"><i class="fas fa-search"></i></button>
                </form>
            </div>
            
            <li class="category-search-li"><a href="category_search.php" class="category-search-link">Поиск по категориям</a></li>

            <!-- Кнопки в правой части шапки -->
            <div class="header-buttons">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <!-- Если пользователь авторизован -->
                    <?php if (isAdmin($_SESSION['user_id'], $conn)): ?>
                        <a href="admin_panel.php" class="btn admin-btn"><i class="fas fa-cog"></i> Админ</a>
                    <?php endif; ?>
                    <a href="profile.php" class="btn"><i class="fas fa-user"></i> Профиль</a>
                <?php else: ?>
                    <!-- Если пользователь не авторизован -->
                    <a href="auth.php" class="btn"><i class="fas fa-sign-in-alt"></i> Авторизация</a>
                <?php endif; ?>
            </div>
        </div>
    </header>