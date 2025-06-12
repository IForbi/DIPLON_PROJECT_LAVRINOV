<?php include 'includes/header.php'; ?>
<?php include 'includes/db_connect.php'; ?>

<main>
    <h2>Вход</h2>
    <form action="login_process.php" method="post">
        <input type="text" name="username" placeholder="Имя пользователя" required>
        <input type="password" name="password" placeholder="Пароль" required>
        <button type="submit">Войти</button>
    </form>
</main>

<?php include 'includes/footer.php'; ?>