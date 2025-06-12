<?php include 'includes/header.php'; ?>

<main class="auth-page">
    <div class="auth-container">
        <h2 class="auth-title">Авторизация</h2>
        
        <!-- Кнопки вкладок (Вход и Регистрация) -->
        <div class="auth-tabs">
            <button onclick="showLogin()" id="loginTab" class="active">Вход</button>
            <button onclick="showRegister()" id="registerTab">Регистрация</button>
        </div>

        <!-- Форма входа -->
        <form id="loginForm" action="login_process.php" method="post" class="auth-form">
            <div class="form-group">
                <input type="text" name="username" placeholder="Имя пользователя" required>
            </div>
            <div class="form-group">
                <input type="password" name="password" placeholder="Пароль" required>
            </div>
            <button type="submit" class="auth-button">Войти</button>
        </form>

        <!-- Форма регистрации -->
        <form id="registerForm" action="register_process.php" method="post" class="auth-form" style="display: none;">
            <div class="form-group">
                <input type="text" name="username" placeholder="Имя пользователя" required>
            </div>
            <div class="form-group">
                <input type="email" name="email" placeholder="Электронная почта" required>
            </div>
            <div class="form-group">
                <input type="password" name="password" placeholder="Пароль" required>
            </div>
            <div class="form-group">
                <input type="tel" id="phoneInput" name="phone" placeholder="+7(___)___-__-__" required>
            </div>
            <button type="submit" class="auth-button">Зарегистрироваться</button>
        </form>
    </div>
</main>

<script>
function showLogin() {
    document.getElementById('loginForm').style.display = 'block';
    document.getElementById('registerForm').style.display = 'none';
    document.getElementById('loginTab').classList.add('active');
    document.getElementById('registerTab').classList.remove('active');
}

function showRegister() {
    document.getElementById('registerForm').style.display = 'block';
    document.getElementById('loginForm').style.display = 'none';
    document.getElementById('registerTab').classList.add('active');
    document.getElementById('loginTab').classList.remove('active');
}

// Функция для форматирования номера телефона
document.addEventListener('DOMContentLoaded', function() {
    const phoneInput = document.getElementById('phoneInput');
    
    phoneInput.addEventListener('input', function(e) {
        // Удаляем все нецифровые символы
        let value = this.value.replace(/\D/g, '');
        
        // Если первый символ не 7, добавляем его
        if (value.length > 0 && value[0] !== '7') {
            value = '7' + value;
        }
        
        // Ограничиваем длину номера (10 цифр без +7)
        if (value.length > 11) {
            value = value.substring(0, 11);
        }
        
        // Форматируем номер
        let formattedValue = '+7(';
        if (value.length > 1) {
            formattedValue += value.substring(1, 4);
        }
        if (value.length >= 4) {
            formattedValue += ')';
        }
        if (value.length >= 5) {
            formattedValue += value.substring(4, 7);
        }
        if (value.length >= 7) {
            formattedValue += '-';
        }
        if (value.length >= 8) {
            formattedValue += value.substring(7, 9);
        }
        if (value.length >= 9) {
            formattedValue += '-';
        }
        if (value.length >= 10) {
            formattedValue += value.substring(9, 11);
        }
        
        // Заполняем оставшиеся символы подчеркиваниями
        const remainingChars = 11 - value.length;
        if (remainingChars > 0) {
            if (value.length <= 1) {
                formattedValue += '___';
            }
            if (value.length <= 4) {
                for (let i = value.length; i < 4 && i <= 3; i++) {
                    formattedValue += '_';
                }
                if (value.length <= 3) formattedValue += ')';
            }
            if (value.length <= 7) {
                for (let i = value.length; i < 7 && i <= 6; i++) {
                    if (i === 4) formattedValue += ')';
                    formattedValue += '_';
                }
                if (value.length <= 6) formattedValue += '-';
            }
            if (value.length <= 9) {
                for (let i = value.length; i < 9 && i <= 8; i++) {
                    if (i === 7) formattedValue += '-';
                    formattedValue += '_';
                }
                if (value.length <= 8) formattedValue += '-';
            }
            if (value.length <= 11) {
                for (let i = value.length; i < 11 && i <= 10; i++) {
                    if (i === 9) formattedValue += '-';
                    formattedValue += '_';
                }
            }
        }
        
        this.value = formattedValue;
        
        // Устанавливаем курсор в правильное положение
        const rawLength = value.length;
        let cursorPos = 0;
        
        if (rawLength === 0) cursorPos = 3; // После +7(
        else if (rawLength <= 1) cursorPos = 3 + rawLength; // После +7(
        else if (rawLength <= 4) cursorPos = 3 + rawLength; // В области xxx)
        else if (rawLength <= 7) cursorPos = 3 + 4 + (rawLength - 4); // В области xxx-
        else if (rawLength <= 9) cursorPos = 3 + 4 + 3 + 1 + (rawLength - 7); // В области xx-
        else cursorPos = 3 + 4 + 3 + 1 + 2 + 1 + (rawLength - 9); // В области xx
        
        this.setSelectionRange(cursorPos, cursorPos);
    });
    
    // Обработка удаления символов
    phoneInput.addEventListener('keydown', function(e) {
        if (e.key === 'Backspace') {
            const selectionStart = this.selectionStart;
            const value = this.value;
            
            // Если пытаемся удалить фиксированный символ (например, +, 7, (, ), -)
            if (selectionStart > 0 && ['+', '7', '(', ')', '-'].includes(value[selectionStart - 1])) {
                e.preventDefault();
                // Находим предыдущую цифру
                let pos = selectionStart - 2;
                while (pos >= 0 && !/\d/.test(value[pos])) {
                    pos--;
                }
                if (pos >= 0) {
                    // Удаляем цифру
                    const newValue = value.substring(0, pos) + '_' + value.substring(pos + 1);
                    this.value = newValue;
                    this.setSelectionRange(pos, pos);
                }
            }
        }
    });
});
</script>

<?php include 'includes/footer.php'; ?>