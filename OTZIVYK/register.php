<?php include 'includes/header.php'; ?>

<main>
    <h2>Регистрация</h2>
    <form action="register_process.php" method="post">
        <input type="text" name="username" placeholder="Имя пользователя" required>
        <input type="email" name="email" placeholder="Электронная почта" required>
        <input type="password" name="password" placeholder="Пароль" required>
        <input type="tel" name="phone" id="phone" placeholder="+7 (___) ___-__-__" required
               pattern="\+7 \(\d{3}\) \d{3}-\d{2}-\d{2}">
        <button type="submit">Зарегистрироваться</button>
    </form>
</main>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const phoneInput = document.getElementById('phone');
    
    phoneInput.addEventListener('input', function(e) {
        // Сохраняем позицию курсора
        const cursorPosition = this.selectionStart;
        const initialLength = this.value.length;
        
        // Удаляем все нецифровые символы, кроме +
        let value = this.value.replace(/[^\d+]/g, '');
        
        // Если номер начинается с 7 или 8 без +, добавляем +
        if (/^[78]/.test(value)) {
            value = '+7' + value.substring(1);
        }
        // Если номер начинается с 9, добавляем +7
        else if (/^9/.test(value)) {
            value = '+7' + value;
        }
        // Если номер пустой, оставляем только +
        else if (value === '') {
            value = '+';
        }
        
        // Форматируем номер
        let formattedValue = value.substring(0, 2); // +7
        
        if (value.length > 2) {
            formattedValue += ' (' + value.substring(2, 5);
        }
        if (value.length > 5) {
            formattedValue += ') ' + value.substring(5, 8);
        }
        if (value.length > 8) {
            formattedValue += '-' + value.substring(8, 10);
        }
        if (value.length > 10) {
            formattedValue += '-' + value.substring(10, 12);
        }
        
        // Обрезаем лишние символы (если введено больше 12 цифр)
        if (value.length > 12) {
            formattedValue = formattedValue.substring(0, 18); // Максимальная длина форматированного номера
        }
        
        this.value = formattedValue;
        
        // Восстанавливаем позицию курсора с учетом добавленных символов
        let newCursorPosition = cursorPosition;
        const addedChars = this.value.length - initialLength;
        
        // Если курсор был после изменяемой позиции, корректируем его положение
        if (cursorPosition === 4 && value.length > 2) newCursorPosition += 2; // После +7
        if (cursorPosition === 7 && value.length > 5) newCursorPosition += 2; // После (
        if (cursorPosition === 11 && value.length > 8) newCursorPosition += 1; // После )
        if (cursorPosition === 15 && value.length > 10) newCursorPosition += 1; // После -
        
        this.setSelectionRange(newCursorPosition, newCursorPosition);
    });
    
    // Обработка удаления символов (backspace)
    phoneInput.addEventListener('keydown', function(e) {
        if (e.key === 'Backspace') {
            const cursorPosition = this.selectionStart;
            const staticChars = [2, 3, 7, 8, 12, 13]; // Позиции статических символов (пробелы, скобки, дефисы)
            
            // Если курсор находится сразу после статического символа
            if (staticChars.includes(cursorPosition - 1)) {
                e.preventDefault();
                // Перемещаем курсор перед статическим символом
                this.setSelectionRange(cursorPosition - 1, cursorPosition - 1);
            }
        }
    });
});
</script>

<?php include 'includes/footer.php'; ?>