<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: auth.php');
    exit;
}

include 'includes/db_connect.php';

// Получаем информацию о пользователе
$stmt = $conn->prepare("SELECT username, email, created_at, role FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

// Получаем отзывы пользователя
$stmt = $conn->prepare("
    SELECT reviews.id, reviews.title, reviews.description, reviews.created_at, reviews.image, categories.name AS category_name 
    FROM reviews 
    JOIN categories ON reviews.category_id = categories.id 
    WHERE reviews.user_id = ? 
    ORDER BY reviews.created_at DESC
");
$stmt->execute([$_SESSION['user_id']]);
$reviews = $stmt->fetchAll();

// Получаем категории
$categories = $conn->query("SELECT id, name FROM categories ORDER BY name")->fetchAll();

include 'includes/header.php';
?>

<body>
    <main>
        <div class="content-container profile-container">
            <h2><i class="fas fa-user"></i> Профиль пользователя</h2>
            
            <div class="profile-info">
                <p><strong>Имя пользователя:</strong> <?= htmlspecialchars($user['username']) ?></p>
                <p><strong>Email:</strong> <?= htmlspecialchars($user['email']) ?></p>
                <p><strong>Дата регистрации:</strong> <?= date('d.m.Y H:i', strtotime($user['created_at'])) ?></p>
                <p><strong>Роль:</strong> <?= $user['role'] === 'admin' ? 'Администратор' : 'Пользователь' ?></p>
                
                <?php if ($user['role'] === 'admin'): ?>
                    <a href="admin_panel.php" class="btn admin-btn">
                        <i class="fas fa-cog"></i> Панель администратора
                    </a>
                <?php endif; ?>
                
                <form action="logout.php" method="post" class="logout-form">
                    <button type="submit" class="btn logout-btn">
                        <i class="fas fa-sign-out-alt"></i> Выйти
                    </button>
                </form>
            </div>

            <div class="section">
                <h3><i class="fas fa-edit"></i> Опубликовать отзыв</h3>
                <form action="submit_review.php" method="post" class="review-form" enctype="multipart/form-data">
                    <div class="form-grid">
                        <div class="image-upload">
                            <label for="image-upload-input" class="upload-label">
                                <div class="image-preview" id="image-preview">
                                    <i class="fas fa-camera"></i>
                                    <span>Основное фото</span>
                                </div>
                            </label>
                            <input type="file" id="image-upload-input" name="main_image" accept="image/*">
                            
                            <div class="additional-images">
                                <label for="additional-images-input" class="upload-label">
                                    <i class="fas fa-images"></i> Доп. изображения (макс. 1)
                                </label>
                                <input type="file" id="additional-images-input" name="additional_images[]" accept="image/*" multiple>
                                <div class="additional-images-preview" id="additional-images-preview"></div>
                            </div>
                        </div>
                        
                        <div class="form-fields">
                            <input type="text" name="title" placeholder="Название продукта" required>
                            <select name="category" required>
                                <option value="">Выберите категорию</option>
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?= $category['id'] ?>"><?= htmlspecialchars($category['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <textarea name="description" placeholder="Ваш отзыв..." required></textarea>
                            <button type="submit" class="btn submit-btn">
                                <i class="fas fa-paper-plane"></i> Опубликовать
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <div class="section">
                <h3><i class="fas fa-list"></i> Мои отзывы</h3>
                <?php if (!empty($reviews)): ?>
                    <div class="reviews-grid">
                        <?php foreach ($reviews as $review): ?>
                            <div class="review-card">
                                <?php if ($review['image']): ?>
                                    <div class="review-image">
                                        <img src="<?= htmlspecialchars($review['image']) ?>" alt="Обложка отзыва">
                                    </div>
                                <?php endif; ?>
                                
                                <div class="review-content">
                                    <h4><?= htmlspecialchars($review['title']) ?></h4>
                                    <div class="review-meta">
                                        <span class="category"><i class="fas fa-tag"></i> <?= htmlspecialchars($review['category_name']) ?></span>
                                        <span class="date"><i class="fas fa-calendar-alt"></i> <?= date('d.m.Y', strtotime($review['created_at'])) ?></span>
                                    </div>
                                    <p class="review-text"><?= nl2br(htmlspecialchars($review['description'])) ?></p>
                                    
                                    <div class="review-actions">
                                        <a href="review.php?id=<?= $review['id'] ?>" class="btn"><i class="fas fa-eye"></i> Просмотр</a>
                                        <form action="delete_review.php" method="post" class="delete-form">
                                            <input type="hidden" name="review_id" value="<?= $review['id'] ?>">
                                            <button type="submit" class="btn delete-btn">
                                                <i class="fas fa-trash-alt"></i> Удалить
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p class="no-reviews">У вас пока нет опубликованных отзывов.</p>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <script>
    // Обработка превью изображений
    document.getElementById('image-upload-input').addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('image-preview').innerHTML = 
                    `<img src="${e.target.result}" alt="Превью">`;
            };
            reader.readAsDataURL(file);
        }
    });

    document.getElementById('additional-images-input').addEventListener('change', function(e) {
        const preview = document.getElementById('additional-images-preview');
        preview.innerHTML = '';
        
        Array.from(e.target.files).slice(0, 1).forEach(file => {
            if (file.type.match('image.*')) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const previewItem = document.createElement('div');
                    previewItem.className = 'preview-item';
                    previewItem.innerHTML = `
                        <img src="${e.target.result}" alt="Превью">
                        <button type="button" class="remove-btn">&times;</button>
                    `;
                    previewItem.querySelector('.remove-btn').addEventListener('click', () => previewItem.remove());
                    preview.appendChild(previewItem);
                };
                reader.readAsDataURL(file);
            }
        });
    });

    // Подтверждение удаления
    document.querySelectorAll('.delete-form').forEach(form => {
        form.addEventListener('submit', function(e) {
            if (!confirm('Вы уверены, что хотите удалить этот отзыв?')) {
                e.preventDefault();
            }
        });
    });
    </script>

    <?php include 'includes/footer.php'; ?>
</body>
</html> 