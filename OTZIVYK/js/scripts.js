// Обработка кнопки "Читать далее"
function initReadMoreButtons() {
    document.querySelectorAll('.description-container').forEach(container => {
        const description = container.querySelector('.description');
        const btn = container.querySelector('.read-more-btn');
        
        if (!btn) return;

        // Проверяем, нужно ли показывать кнопку
        if (description.scrollHeight <= 100) {
            btn.style.display = 'none';
            container.style.maxHeight = 'none';
            return;
        }
        
        btn.style.display = 'block';
        
        btn.addEventListener('click', function() {
            if (container.classList.contains('expanded')) {
                container.style.maxHeight = '100px';
                this.textContent = 'Читать далее';
            } else {
                container.style.maxHeight = container.scrollHeight + 'px';
                this.textContent = 'Свернуть';
            }
            container.classList.toggle('expanded');
        });
    });
}

// Инициализация при загрузке
document.addEventListener('DOMContentLoaded', function() {
    initReadMoreButtons();
    
    // Обработка лайков/дизлайков
    document.querySelectorAll('.like-btn, .dislike-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            if (!IS_LOGGED_IN) {
                window.location.href = 'auth.php';
                return;
            }
            
            const reviewId = this.dataset.reviewId;
            const rating = this.classList.contains('like-btn') ? 1 : -1;
            
            fetch('rate_review.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `review_id=${reviewId}&rating=${rating}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const likeBtn = document.querySelector(`.like-btn[data-review-id="${reviewId}"]`);
                    const dislikeBtn = document.querySelector(`.dislike-btn[data-review-id="${reviewId}"]`);
                    
                    likeBtn.querySelector('.count').textContent = data.likes;
                    dislikeBtn.querySelector('.count').textContent = data.dislikes;
                    
                    likeBtn.classList.toggle('active', data.user_rating === 1);
                    dislikeBtn.classList.toggle('active', data.user_rating === -1);
                }
            });
        });
    });
    
    // Обработка комментариев
    document.querySelectorAll('.post-comment').forEach(btn => {
        btn.addEventListener('click', function() {
            const reviewId = this.dataset.reviewId;
            const textarea = this.previousElementSibling;
            const commentText = textarea.value.trim();
            
            if (!commentText) {
                alert('Введите текст комментария');
                return;
            }
            
            fetch('add_comment.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `review_id=${reviewId}&comment=${encodeURIComponent(commentText)}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // [Остальная обработка комментариев]
                }
            });
        });
    });
});

// Для динамически загружаемого контента
new MutationObserver(initReadMoreButtons).observe(document.body, {
    childList: true,
    subtree: true
});