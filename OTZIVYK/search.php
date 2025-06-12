<?php
include 'includes/db_connect.php';
include 'includes/header.php';

// Получаем список категорий для фильтра
$categories = $conn->query("SELECT id, name FROM categories ORDER BY name")->fetchAll();

$search_results = [];
$total_pages = 1;
$current_page = 1;
$per_page = 10;
$search_query = '';
$category_filter = '';

if (isset($_GET['query'])) {
    $search_query = trim($_GET['query']);
    $category_filter = $_GET['category'] ?? '';
    
    if (!empty($search_query)) {
        $current_page = max(1, isset($_GET['page']) ? (int)$_GET['page'] : 1);
        $offset = ($current_page - 1) * $per_page;
        
        // Базовый SQL запрос
        $sql = "
            SELECT r.*, c.name AS category_name, u.username,
                   MATCH(r.title, r.description) AGAINST(:query IN NATURAL LANGUAGE MODE) AS relevance
            FROM reviews r
            JOIN categories c ON r.category_id = c.id
            JOIN users u ON r.user_id = u.id
            WHERE MATCH(r.title, r.description) AGAINST(:query IN NATURAL LANGUAGE MODE)
        ";
        
        $params = [':query' => $search_query];
        
        // Добавляем фильтр по категории (если выбран)
        if (!empty($category_filter)) {
            $sql .= " AND r.category_id = :category_id";
            $params[':category_id'] = $category_filter;
        }
        
        // Получаем общее количество результатов (для пагинации)
        $count_sql = "SELECT COUNT(*) FROM ($sql) AS total";
        $count_stmt = $conn->prepare($count_sql);
        $count_stmt->execute($params);
        $total_results = $count_stmt->fetchColumn();
        $total_pages = ceil($total_results / $per_page);
        
        // Добавляем сортировку и пагинацию к основному запросу
        $sql .= " ORDER BY relevance DESC LIMIT :limit OFFSET :offset";
        $params[':limit'] = $per_page;
        $params[':offset'] = $offset;
        
        // Выполняем основной запрос
        $stmt = $conn->prepare($sql);
        foreach ($params as $key => $value) {
            $param_type = is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR;
            $stmt->bindValue($key, $value, $param_type);
        }
        $stmt->execute();
        $search_results = $stmt->fetchAll();
    }
}
?>

<main class="search-page">
    <div class="container">
        <h2>Поиск отзывов</h2>
        
        <div class="search-form">
            <form action="search.php" method="get">
                <div class="form-row">
                    <input type="text" name="query" placeholder="Введите слова для поиска..." 
                           value="<?= htmlspecialchars($search_query) ?>">
                    <div class="custom-select">
                        <select name="category">
                            <option value="">Все категории</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?= $category['id'] ?>" 
                                    <?= $category_filter == $category['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($category['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <span class="custom-arrow">▼</span>
                    </div>
                    <button type="submit">Искать</button>
                </div>
            </form>
        </div>
        <div class="category-search-link">
            <a href="category_search.php">Поиск по категориям</a>
        </div>

        <?php if (!empty($search_query)): ?>
            <div class="search-results">
                <h3>
                    <?php if (!empty($search_results)): ?>
                        Результаты поиска по запросу "<?= htmlspecialchars($search_query) ?>" (<?= $total_results ?>)
                    <?php else: ?>
                        Ничего не найдено по запросу "<?= htmlspecialchars($search_query) ?>"
                    <?php endif; ?>
                </h3>
                
                <?php if (!empty($search_results)): ?>
                    <div class="reviews-list">
                        <?php foreach ($search_results as $review): ?>
                            <div class="review">
                                <?php if ($review['image']): ?>
                                    <div class="review-thumbnail">
                                        <img src="<?= htmlspecialchars($review['image']) ?>" alt="Превью отзыва">
                                    </div>
                                <?php endif; ?>
                                <div class="review-content">
                                    <div class="review-header">
                                        <h4><?= highlight_words(htmlspecialchars($review['title']), $search_query) ?></h4>
                                        <p class="meta">
                                            <span class="author"><?= htmlspecialchars($review['username']) ?></span>
                                            <span class="category"><?= htmlspecialchars($review['category_name']) ?></span>
                                            <span class="date"><?= date('d.m.Y H:i', strtotime($review['created_at'])) ?></span>
                                        </p>
                                    </div>
                                    <p class="description">
                                        <?= nl2br(highlight_words(htmlspecialchars(mb_substr($review['description'], 0, 200) . (mb_strlen($review['description']) > 200 ? '...' : '')), $search_query)) ?>
                                    </p>
                                    <a href="review.php?id=<?= $review['id'] ?>" class="btn">Читать полностью</a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <?php if ($total_pages > 1): ?>
                        <div class="pagination">
                            <?php if ($current_page > 1): ?>
                                <a href="?query=<?= urlencode($search_query) ?>&category=<?= $category_filter ?>&page=<?= $current_page - 1 ?>" 
                                   class="page-link">Назад</a>
                            <?php endif; ?>
                            
                            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                <?php if ($i == $current_page): ?>
                                    <span class="page-link current"><?= $i ?></span>
                                <?php else: ?>
                                    <a href="?query=<?= urlencode($search_query) ?>&category=<?= $category_filter ?>&page=<?= $i ?>" 
                                       class="page-link"><?= $i ?></a>
                                <?php endif; ?>
                            <?php endfor; ?>
                            
                            <?php if ($current_page < $total_pages): ?>
                                <a href="?query=<?= urlencode($search_query) ?>&category=<?= $category_filter ?>&page=<?= $current_page + 1 ?>" 
                                   class="page-link">Вперед</a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
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

<?php 
function highlight_words($text, $words) {
    $words = preg_split('/\s+/', $words);
    foreach ($words as $word) {
        if (strlen($word) > 2) { // Подсвечиваем только слова длиннее 2 символов
            $text = preg_replace("/\b($word)\b/iu", '<span class="highlight">$1</span>', $text);
        }
    }
    return $text;
}

include 'includes/footer.php'; 
?>