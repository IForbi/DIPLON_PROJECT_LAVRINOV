<?php
include 'includes/db_connect.php';
include 'includes/header.php';

if (!isset($_GET['id'])) {
    header('Location: index.php');
    exit;
}

$review_id = (int)$_GET['id'];

// Получаем основной отзыв
$stmt = $conn->prepare("
    SELECT r.*, c.name AS category_name, u.username
    FROM reviews r
    JOIN categories c ON r.category_id = c.id
    JOIN users u ON r.user_id = u.id
    WHERE r.id = ?
");
$stmt->execute([$review_id]);
$review = $stmt->fetch();

if (!$review) {
    header('Location: index.php');
    exit;
}

// Получаем дополнительные изображения
$stmt = $conn->prepare("SELECT image_path FROM review_images WHERE review_id = ?");
$stmt->execute([$review_id]);
$additional_images = $stmt->fetchAll(PDO::FETCH_COLUMN, 0);

// Получаем рейтинг отзыва
$stmt = $conn->prepare("
    SELECT 
        SUM(CASE WHEN rating = 1 THEN 1 ELSE 0 END) as likes,
        SUM(CASE WHEN rating = -1 THEN 1 ELSE 0 END) as dislikes
    FROM review_ratings 
    WHERE review_id = ?
");
$stmt->execute([$review_id]);
$ratings = $stmt->fetch();
$likes = $ratings['likes'] ?? 0;
$dislikes = $ratings['dislikes'] ?? 0;

// Получаем текущую оценку пользователя (если авторизован)
$user_rating = 0;
if (isset($_SESSION['user_id'])) {
    $stmt = $conn->prepare("SELECT rating FROM review_ratings WHERE review_id = ? AND user_id = ?");
    $stmt->execute([$review_id, $_SESSION['user_id']]);
    $user_rating = $stmt->fetchColumn() ?? 0;
}

// Получаем все комментарии
$stmt = $conn->prepare("
    SELECT rc.*, u.username 
    FROM review_comments rc
    JOIN users u ON rc.user_id = u.id
    WHERE rc.review_id = ?
    ORDER BY rc.created_at DESC
");
$stmt->execute([$review_id]);
$comments = $stmt->fetchAll();

$comment_count = count($comments);
?>

<main class="review-page">
    <div class="container">
        <div class="review-container">
            <h2><?= htmlspecialchars($review['title']) ?></h2>
            <div class="review-meta">
                <span>Автор: <?= htmlspecialchars($review['username']) ?></span>
                <span>Категория: <?= htmlspecialchars($review['category_name']) ?></span>
                <span>Дата: <?= date('d.m.Y H:i', strtotime($review['created_at'])) ?></span>
            </div>
            
            <?php if ($review['image']): ?>
                <div class="main-image">
                    <img src="<?= htmlspecialchars($review['image']) ?>" alt="Основное изображение">
                </div>
            <?php endif; ?>
            
            <div class="review-content">
                <p><?= nl2br(htmlspecialchars($review['description'])) ?></p>
            </div>
            
            <?php if (!empty($additional_images)): ?>
                <div class="additional-images">
                    <h3>Дополнительные изображения</h3>
                    <div class="images-grid">
                        <?php foreach ($additional_images as $image): ?>
                            <div class="image-item">
                                <img src="<?= htmlspecialchars($image) ?>" alt="Дополнительное изображение">
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
            
            <!-- Блок рейтинга -->
            <div class="review-rating">
                <h3>Рейтинг отзыва</h3>
                <div class="rating">
                    <button class="like-btn <?= $user_rating == 1 ? 'active' : '' ?>" data-review-id="<?= $review_id ?>">
                        <span class="icon">👍</span>
                        <span class="count"><?= $likes ?></span>
                    </button>
                    <button class="dislike-btn <?= $user_rating == -1 ? 'active' : '' ?>" data-review-id="<?= $review_id ?>">
                        <span class="icon">👎</span>
                        <span class="count"><?= $dislikes ?></span>
                    </button>
                </div>
            </div>
            
            <!-- Блок комментариев -->
            <div class="comments-section">
                <h3>Комментарии (<?= $comment_count ?>)</h3>
                
                <?php if (isset($_SESSION['user_id'])): ?>
                    <div class="add-comment">
                        <textarea placeholder="Оставьте комментарий..." rows="3"></textarea>
                        <button class="btn post-comment" data-review-id="<?= $review_id ?>">Отправить</button>
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
</main>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Обработка лайков/дизлайков
    document.addEventListener('click', async function(e) {
        if (e.target.closest('.like-btn, .dislike-btn')) {
            const btn = e.target.closest('.like-btn, .dislike-btn');
            const reviewId = btn.dataset.reviewId;
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
                    const likeBtn = document.querySelector(`.like-btn[data-review-id="${reviewId}"]`);
                    const dislikeBtn = document.querySelector(`.dislike-btn[data-review-id="${reviewId}"]`);
                    
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
            const reviewId = btn.dataset.reviewId;
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
                    const commentsList = document.querySelector('.comments-list');
                    const commentTitle = document.querySelector('.comments-section h3');
                    
                    const commentDiv = document.createElement('div');
                    commentDiv.className = 'comment';
                    commentDiv.innerHTML = `
                        <div class="comment-header">
                            <span class="username">${data.comment.username}</span>
                            <span class="date">${data.comment.created_at}</span>
                        </div>
                        <div class="comment-text">${data.comment.comment}</div>
                    `;
                    
                    if (commentsList.children.length > 0) {
                        commentsList.insertBefore(commentDiv, commentsList.firstChild);
                    } else {
                        commentsList.appendChild(commentDiv);
                    }
                    
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
        if (e.target.matches('.main-image img, .image-item img')) {
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