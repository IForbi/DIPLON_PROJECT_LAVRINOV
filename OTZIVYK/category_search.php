<?php
include 'includes/db_connect.php';
include 'includes/header.php';

// Получаем список всех категорий
$categories = $conn->query("SELECT id, name FROM categories ORDER BY name")->fetchAll();

// Получаем выбранную категорию
$selected_category_id = isset($_GET['category_id']) ? (int)$_GET['category_id'] : null;

// Настройки пагинации
$per_page = 10;
$current_page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($current_page - 1) * $per_page;

// Получаем отзывы для выбранной категории
$reviews = [];
$total_reviews = 0;
$selected_category_name = '';

if ($selected_category_id) {
    // Получаем название выбранной категории
    $stmt = $conn->prepare("SELECT name FROM categories WHERE id = ?");
    $stmt->execute([$selected_category_id]);
    $selected_category = $stmt->fetch();
    $selected_category_name = $selected_category ? $selected_category['name'] : '';
    
    // Получаем общее количество отзывов в категории
    $stmt = $conn->prepare("SELECT COUNT(*) FROM reviews WHERE category_id = ?");
    $stmt->execute([$selected_category_id]);
    $total_reviews = $stmt->fetchColumn();
    
    // Получаем отзывы с пагинацией (исправленный запрос)
    $stmt = $conn->prepare("
        SELECT r.*, u.username, c.name AS category_name
        FROM reviews r
        JOIN users u ON r.user_id = u.id
        JOIN categories c ON r.category_id = c.id
        WHERE r.category_id = :category_id
        ORDER BY r.created_at DESC
        LIMIT :limit OFFSET :offset
    ");
    
    // Привязываем параметры
    $stmt->bindParam(':category_id', $selected_category_id, PDO::PARAM_INT);
    $stmt->bindParam(':limit', $per_page, PDO::PARAM_INT);
    $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
    
    $stmt->execute();
    $reviews = $stmt->fetchAll();
}

$total_pages = ceil($total_reviews / $per_page);
?>
<main class="category-search-page">
    <div class="container">
        <h2>Поиск по категориям</h2>
        
        <div class="category-search-form">
            <form method="get" action="category_search.php">
                <div class="custom-select">
                    <select name="category_id" onchange="this.form.submit()">
                        <option value="">Выберите категорию</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?php echo $category['id']; ?>" 
                                <?php echo $selected_category_id == $category['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($category['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <span class="custom-arrow">▼</span>
                </div>
            </form>
        </div>
        
        <?php if ($selected_category_id): ?>
            <div class="search-results">
                <h3>
                    <?php echo $total_reviews > 0 ? "Отзывы в категории \"{$selected_category_name}\" ({$total_reviews})" : 
                       "Нет отзывов в категории \"{$selected_category_name}\""; ?>
                </h3>
                
                <?php if (!empty($reviews)): ?>
                    <div class="reviews-list">
                        <?php foreach ($reviews as $review): ?>
                            <div class="review">
                                <?php if ($review['image']): ?>
                                    <div class="review-thumbnail">
                                        <img src="<?php echo htmlspecialchars($review['image']); ?>" alt="Превью отзыва">
                                    </div>
                                <?php endif; ?>
                                <div class="review-content">
                                    <div class="review-header">
                                        <h4><?php echo htmlspecialchars($review['title']); ?></h4>
                                        <p class="meta">
                                            <span class="author"><?php echo htmlspecialchars($review['username']); ?></span>
                                            <span class="date"><?php echo date('d.m.Y H:i', strtotime($review['created_at'])); ?></span>
                                        </p>
                                    </div>
                                    <p class="description">
                                        <?php echo nl2br(htmlspecialchars(mb_substr($review['description'], 0, 200) . (mb_strlen($review['description']) > 200 ? '...' : ''))); ?>
                                    </p>
                                    <a href="review.php?id=<?php echo $review['id']; ?>" class="btn">Читать полностью</a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <?php if ($total_pages > 1): ?>
                        <div class="pagination">
                            <?php if ($current_page > 1): ?>
                                <a href="category_search.php?category_id=<?php echo $selected_category_id; ?>&page=<?php echo $current_page - 1; ?>" 
                                   class="page-link">Назад</a>
                            <?php endif; ?>
                            
                            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                <?php if ($i == $current_page): ?>
                                    <span class="page-link current"><?php echo $i; ?></span>
                                <?php else: ?>
                                    <a href="category_search.php?category_id=<?php echo $selected_category_id; ?>&page=<?php echo $i; ?>" 
                                       class="page-link"><?php echo $i; ?></a>
                                <?php endif; ?>
                            <?php endfor; ?>
                            
                            <?php if ($current_page < $total_pages): ?>
                                <a href="category_search.php?category_id=<?php echo $selected_category_id; ?>&page=<?php echo $current_page + 1; ?>" 
                                   class="page-link">Вперед</a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <div class="no-category-selected">
                <p>Пожалуйста, выберите категорию из списка</p>
            </div>
        <?php endif; ?>
    </div>
</main>

<script>
// Закрытие выпадающего списка при клике вне его
document.addEventListener('click', function(event) {
    const selects = document.querySelectorAll('.custom-select');
    selects.forEach(function(select) {
        if (!select.contains(event.target)) {
            select.classList.remove('open');
        }
    });
});

// Открытие/закрытие выпадающего списка
document.querySelectorAll('.custom-select').forEach(function(select) {
    const selectElement = select.querySelector('select');
    const arrow = select.querySelector('.custom-arrow');
    
    arrow.addEventListener('click', function(e) {
        e.stopPropagation();
        select.classList.toggle('open');
    });
    
    selectElement.addEventListener('click', function(e) {
        e.stopPropagation();
        select.classList.toggle('open');
    });
});
</script>

<?php include 'includes/footer.php'; ?>