<?php include 'includes/header.php'; ?>
<?php include 'includes/db_connect.php'; ?>

<main>
    <h1 class="welcome_text">Добро пожаловать на RewByYou!<br>На этом сайте вы найдёте множество отзывов на все сферы вашей жизни.</h1>
    <div class="reviews-section">
        <div class="reviews-container">
            <h2>Последние отзывы</h2>
            <div id="reviews">
                <?php
                $per_page = 10;
                $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
                $offset = ($page - 1) * $per_page;
                
                $total_stmt = $conn->query("SELECT COUNT(*) FROM reviews");
                $total_reviews = $total_stmt->fetchColumn();
                $total_pages = ceil($total_reviews / $per_page);
                
                $stmt = $conn->prepare("
                    SELECT reviews.*, categories.name AS category_name, users.username
                    FROM reviews 
                    JOIN categories ON reviews.category_id = categories.id
                    JOIN users ON reviews.user_id = users.id
                    ORDER BY reviews.created_at DESC 
                    LIMIT :limit OFFSET :offset
                ");
                $stmt->bindValue(':limit', $per_page, PDO::PARAM_INT);
                $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
                $stmt->execute();
                $reviews = $stmt->fetchAll();
                
                foreach ($reviews as $row) {
                    $stmt = $conn->prepare("SELECT image_path FROM review_images WHERE review_id = ?");
                    $stmt->execute([$row['id']]);
                    $additional_images = $stmt->fetchAll(PDO::FETCH_COLUMN, 0);
                    
                    $stmt = $conn->prepare("
                        SELECT 
                            SUM(CASE WHEN rating = 1 THEN 1 ELSE 0 END) as likes,
                            SUM(CASE WHEN rating = -1 THEN 1 ELSE 0 END) as dislikes
                        FROM review_ratings 
                        WHERE review_id = ?
                    ");
                    $stmt->execute([$row['id']]);
                    $ratings = $stmt->fetch();
                    $likes = $ratings['likes'] ?? 0;
                    $dislikes = $ratings['dislikes'] ?? 0;
                    
                    $user_rating = 0;
                    if (isset($_SESSION['user_id'])) {
                        $stmt = $conn->prepare("SELECT rating FROM review_ratings WHERE review_id = ? AND user_id = ?");
                        $stmt->execute([$row['id'], $_SESSION['user_id']]);
                        $user_rating = $stmt->fetchColumn() ?? 0;
                    }
                    
                    $stmt = $conn->prepare("
                        SELECT rc.*, u.username 
                        FROM review_comments rc
                        JOIN users u ON rc.user_id = u.id
                        WHERE rc.review_id = ?
                        ORDER BY rc.created_at DESC
                        LIMIT 3
                    ");
                    $stmt->execute([$row['id']]);
                    $comments = $stmt->fetchAll();
                    
                    $stmt = $conn->prepare("SELECT COUNT(*) FROM review_comments WHERE review_id = ?");
                    $stmt->execute([$row['id']]);
                    $comment_count = $stmt->fetchColumn();
                    ?>
                    
                    <div class="review" data-review-id="<?= $row['id'] ?>">
                        <div class="review-images">
                            <?php if ($row['image']): ?>
                                <div class="main-image">
                                    <a href="review.php?id=<?= $row['id'] ?>">
                                        <img src="<?= htmlspecialchars($row['image']) ?>" alt="Основное изображение">
                                    </a>
                                </div>
                            <?php endif; ?>
                            
                            <?php if (!empty($additional_images)): ?>
                                <div class="additional-images-grid">
                                    <?php foreach ($additional_images as $image): ?>
                                        <div class="additional-image">
                                            <a href="review.php?id=<?= $row['id'] ?>">
                                                <img src="<?= htmlspecialchars($image) ?>" alt="Дополнительное изображение">
                                            </a>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="review-content">
                            <h3><a href="review.php?id=<?= $row['id'] ?>"><?= htmlspecialchars($row['title']) ?></a></h3>
                            <p class="author">Автор: <?= htmlspecialchars($row['username']) ?></p>
                            <p class="category">Категория: <?= htmlspecialchars($row['category_name']) ?></p>
                            
                            <div class="description-container">
                                <p class="description"><?= nl2br(htmlspecialchars(mb_substr($row['description'], 0, 200))) ?>...</p>
                                <a href="review.php?id=<?= $row['id'] ?>" class="read-more-btn">Читать далее</a>
                            </div>
                            
                            <div class="review-actions">
                                <div class="rating">
                                    <button class="like-btn <?= $user_rating == 1 ? 'active' : '' ?>" data-review-id="<?= $row['id'] ?>">
                                        <span class="icon">👍</span>
                                        <span class="count"><?= $likes ?></span>
                                    </button>
                                    <button class="dislike-btn <?= $user_rating == -1 ? 'active' : '' ?>" data-review-id="<?= $row['id'] ?>">
                                        <span class="icon">👎</span>
                                        <span class="count"><?= $dislikes ?></span>
                                    </button>
                                </div>
                            </div>
                            
                            <div class="comments-section">
                                <h4>Комментарии (<?= $comment_count ?>)</h4>
                                
                                <?php if (isset($_SESSION['user_id'])): ?>
                                    <div class="add-comment">
                                        <textarea placeholder="Оставьте комментарий..." rows="2"></textarea>
                                        <button class="btn post-comment" data-review-id="<?= $row['id'] ?>">Отправить</button>
                                    </div>
                                <?php else: ?>
                                    <p class="auth-notice">Чтобы оставить комментарий, <a href="auth.php">войдите</a> в аккаунт.</p>
                                <?php endif; ?>
                                
                                <?php if (!empty($comments)): ?>
                                    <div class="comments-list">
                                        <?php foreach ($comments as $comment): ?>
                                            <div class="comment">
                                                <div class="comment-header">
                                                    <span class="username"><?= htmlspecialchars($comment['username']) ?></span>
                                                    <span class="date"><?= date('d.m.Y H:i', strtotime($comment['created_at'])) ?></span>
                                                </div>
                                                <div class="comment-text"><?= nl2br(htmlspecialchars($comment['comment'])) ?></div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php
                }
                
                if ($total_pages > 1): ?>
                    <div class="pagination-container">
                        <div class="pagination">
                            <?php if ($page > 1): ?>
                                <a href="?page=<?= $page - 1 ?>" class="page-link">Назад</a>
                            <?php endif; ?>
                            
                            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                <?php if ($i == $page): ?>
                                    <span class="page-link current"><?= $i ?></span>
                                <?php else: ?>
                                    <a href="?page=<?= $i ?>" class="page-link"><?= $i ?></a>
                                <?php endif; ?>
                            <?php endfor; ?>
                            
                            <?php if ($page < $total_pages): ?>
                                <a href="?page=<?= $page + 1 ?>" class="page-link">Вперед</a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</main>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Обработка лайков/дизлайков
    document.addEventListener('click', async function(e) {
        if (e.target.closest('.like-btn, .dislike-btn')) {
            const btn = e.target.closest('.like-btn, .dislike-btn');
            const review = btn.closest('.review');
            const reviewId = review.dataset.reviewId;
            const isLike = btn.classList.contains('like-btn');
            
            if (!<?= isset($_SESSION['user_id']) ? 'true' : 'false' ?>) {
                window.location.href = 'auth.php';
                return;
            }
            
            try {
                const response = await fetch('rate_review.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `review_id=${reviewId}&action=${isLike ? 'like' : 'dislike'}`
                });
                
                const data = await response.json();
                
                if (data.success) {
                    const likeBtn = review.querySelector('.like-btn');
                    const dislikeBtn = review.querySelector('.dislike-btn');
                    
                    likeBtn.querySelector('.count').textContent = data.likes;
                    dislikeBtn.querySelector('.count').textContent = data.dislikes;
                    
                    likeBtn.classList.toggle('active', data.user_rating === 1);
                    dislikeBtn.classList.toggle('active', data.user_rating === -1);
                } else {
                    alert('Ошибка: ' + (data.error || 'Не удалось обновить рейтинг'));
                }
            } catch (error) {
                console.error('Ошибка:', error);
                alert('Произошла ошибка при отправке оценки');
            }
        }
    });
    
    // Обработка комментариев
    document.addEventListener('click', async function(e) {
        if (e.target.classList.contains('post-comment')) {
            const btn = e.target;
            const review = btn.closest('.review');
            const reviewId = review.dataset.reviewId;
            const textarea = btn.previousElementSibling;
            const commentText = textarea.value.trim();
            
            if (!commentText) {
                alert('Введите текст комментария');
                return;
            }
            
            try {
                const formData = new FormData();
                formData.append('review_id', reviewId);
                formData.append('comment', commentText);
                
                const response = await fetch('add_comment.php', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.success) {
                    textarea.value = '';
                    const commentsSection = review.querySelector('.comments-section');
                    let commentsList = commentsSection.querySelector('.comments-list');
                    
                    if (!commentsList) {
                        commentsList = document.createElement('div');
                        commentsList.className = 'comments-list';
                        commentsSection.appendChild(commentsList);
                    }
                    
                    const commentDiv = document.createElement('div');
                    commentDiv.className = 'comment';
                    commentDiv.innerHTML = `
                        <div class="comment-header">
                            <span class="username">${data.comment.username}</span>
                            <span class="date">${data.comment.created_at}</span>
                        </div>
                        <div class="comment-text">${data.comment.comment}</div>
                    `;
                    
                    commentsList.prepend(commentDiv);
                    
                    const commentTitle = commentsSection.querySelector('h4');
                    const currentCount = parseInt(commentTitle.textContent.match(/\d+/)[0]) || 0;
                    commentTitle.textContent = `Комментарии (${currentCount + 1})`;
                } else {
                    alert('Ошибка: ' + (data.error || 'Не удалось добавить комментарий'));
                }
            } catch (error) {
                console.error('Ошибка:', error);
                alert('Произошла ошибка при отправке комментария');
            }
        }
    });
    
    // Просмотр изображений
    document.addEventListener('click', function(e) {
        if (e.target.matches('.main-image img, .additional-image img')) {
            // Отменяем переход по ссылке при клике на изображение
            e.preventDefault();
            
            const imgSrc = e.target.src;
            const overlay = document.createElement('div');
            overlay.className = 'image-overlay';
            overlay.innerHTML = `
                <div class="image-modal">
                    <img src="${imgSrc}" alt="Увеличенное изображение">
                    <button class="close-btn">&times;</button>
                </div>
            `;
            
            document.body.appendChild(overlay);
            document.body.style.overflow = 'hidden';
            
            overlay.querySelector('.close-btn').addEventListener('click', function() {
                document.body.removeChild(overlay);
                document.body.style.overflow = '';
            });
        }
    });
});
</script>

<?php include 'includes/footer.php'; ?>