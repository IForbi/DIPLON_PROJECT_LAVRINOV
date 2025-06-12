-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Хост: 127.0.0.1:3306
-- Время создания: Июн 12 2025 г., 13:54
-- Версия сервера: 8.0.30
-- Версия PHP: 7.2.34

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- База данных: `reviews_db`
--

-- --------------------------------------------------------

--
-- Структура таблицы `categories`
--

CREATE TABLE `categories` (
  `id` int NOT NULL,
  `name` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Дамп данных таблицы `categories`
--

INSERT INTO `categories` (`id`, `name`) VALUES
(158, 'Автотовары'),
(163, 'Аксессуары'),
(160, 'Бытовая техника'),
(159, 'Дом и сад'),
(153, 'Еда'),
(154, 'Игрушки'),
(171, 'Игры и консоли'),
(167, 'Канцелярия'),
(151, 'Книги'),
(161, 'Компьютеры и ноутбуки'),
(156, 'Красота и здоровье'),
(155, 'Мебель'),
(168, 'Музыкальные инструменты'),
(164, 'Обувь'),
(152, 'Одежда'),
(169, 'Подарки и сувениры'),
(162, 'Смартфоны и гаджеты'),
(157, 'Спорт и отдых'),
(173, 'Строительство и ремонт'),
(177, 'Товары для ванной'),
(174, 'Товары для детей'),
(166, 'Товары для животных'),
(176, 'Товары для кухни'),
(178, 'Товары для офиса'),
(179, 'Товары для сада'),
(172, 'Туризм и путешествия'),
(170, 'Фото и видео'),
(175, 'Хобби и творчество'),
(150, 'Электроника'),
(165, 'Ювелирные изделия');

-- --------------------------------------------------------

--
-- Структура таблицы `reviews`
--

CREATE TABLE `reviews` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `title` varchar(100) NOT NULL,
  `description` text NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `category_id` int DEFAULT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Дамп данных таблицы `reviews`
--

INSERT INTO `reviews` (`id`, `user_id`, `title`, `description`, `image`, `created_at`, `category_id`, `status`) VALUES
(1, 5, 'Yaso', 'Одинокий странник', 'uploads/6840d477bf6b9_yaso.jpeg', '2025-06-04 23:19:19', 171, 'approved'),
(2, 5, 'Шаурма, наверное', 'Странный вкус, очень долго отсиживался в туалете', 'uploads/6840d4f1b0e0f_dadadad.jpg', '2025-06-04 23:21:21', 153, 'approved'),
(3, 5, 'Warhammer', 'Warhammer 40,000 — вымышленная вселенная, которая лежит в основе серии настольных и компьютерных игр, а также других произведений.\r\nДействие происходит в далёком будущем, где человечество колонизировало миллионы планет и погрязло в военных конфликтах. \r\nВселенная Warhammer 40,000 была придумана в 1987 году Риком Пристли и Энди Чамберсом из компании Games Workshop. Они решили переработать уже существующую вселенную Warhammer Fantasy в научно-фантастическом сеттинге.\r\nНекоторые фракции и персонажи вселенной Warhammer 40,000:\r\nИмпериум Человечества — человеческое религиозное государство, которое колонизировало множество планет. Правит Империумом Император, которого считают богом.\r\nКосмодесантники (Адептус Астартес) — элитные генно-модифицированные суперсолдаты, основная ударная сила Империума.\r\nОрки — одна из фракций, с которыми воюет Империум. Агрессивные орки проводят жизнь на поле битвы, а после гибели оставляют споры, из которых вырастают новые воины.\r\nХаос — демоническая сила из параллельного измерения Варпа, порождает четырёх богов-разрушителей (Кхорн, Нургл, Тзинч, Слаанеш).', 'uploads/6840d5991fa6b_maxresdefault (5).jpg', '2025-06-04 23:24:09', 171, 'approved');

-- --------------------------------------------------------

--
-- Структура таблицы `review_comments`
--

CREATE TABLE `review_comments` (
  `id` int NOT NULL,
  `review_id` int NOT NULL,
  `user_id` int NOT NULL,
  `comment` varchar(2000) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Дамп данных таблицы `review_comments`
--

INSERT INTO `review_comments` (`id`, `review_id`, `user_id`, `comment`, `created_at`) VALUES
(1, 3, 5, 'as', '2025-06-12 08:34:35'),
(2, 3, 5, 'as', '2025-06-12 08:34:35'),
(3, 3, 5, 'Ф', '2025-06-12 09:20:26'),
(4, 3, 5, 'Ф', '2025-06-12 09:20:26'),
(5, 3, 5, 'ФЫ', '2025-06-12 09:20:27');

-- --------------------------------------------------------

--
-- Структура таблицы `review_images`
--

CREATE TABLE `review_images` (
  `id` int NOT NULL,
  `review_id` int NOT NULL,
  `image_path` varchar(255) NOT NULL,
  `is_main` tinyint(1) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Дамп данных таблицы `review_images`
--

INSERT INTO `review_images` (`id`, `review_id`, `image_path`, `is_main`) VALUES
(1, 8, 'uploads/683c3eb0a743d_RobloxScreenShot20250406_135706215.png', 1),
(2, 8, 'uploads/683c3eb0a7d1d_RobloxScreenShot20250326_163752547.png', 0),
(3, 9, 'uploads/683c3fa863188_RobloxScreenShot20250410_154157685.png', 1),
(4, 9, 'uploads/683c3fa8637e9_RobloxScreenShot20250303_004326008.png', 0),
(5, 10, 'uploads/683c43820929e_RobloxScreenShot20250312_152819974.png', 1),
(6, 10, 'uploads/683c438209b48_RobloxScreenShot20250312_152819974.png', 0),
(7, 11, 'uploads/683c44959886a_RobloxScreenShot20250326_185735934.png', 1),
(8, 13, 'uploads/683c44a659909_RobloxScreenShot20250406_135706215.png', 1),
(9, 14, 'uploads/683c44acee3b7_RobloxScreenShot20250512_210729011.png', 1),
(14, 17, 'uploads/684096e0af7d4_RobloxScreenShot20250326_192413165.png', 1),
(15, 33, 'uploads/6840caf31125d_Frame 144.png', 0),
(16, 1, 'uploads/6840d477bfe4a_Background 5.png', 0),
(17, 3, 'uploads/6840d5992031b_maxresdefault.jpg', 0);

-- --------------------------------------------------------

--
-- Структура таблицы `review_ratings`
--

CREATE TABLE `review_ratings` (
  `id` int NOT NULL,
  `review_id` int NOT NULL,
  `user_id` int NOT NULL,
  `rating` tinyint NOT NULL COMMENT '1 - like, -1 - dislike',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Дамп данных таблицы `review_ratings`
--

INSERT INTO `review_ratings` (`id`, `review_id`, `user_id`, `rating`, `created_at`) VALUES
(1, 13, 5, -1, '2025-06-02 22:03:46'),
(2, 14, 5, 1, '2025-06-02 22:04:00'),
(13, 13, 12, 1, '2025-06-04 20:34:29'),
(15, 1, 5, 1, '2025-06-04 23:19:28'),
(16, 2, 5, 1, '2025-06-04 23:21:45'),
(17, 3, 5, -1, '2025-06-12 09:28:03');

-- --------------------------------------------------------

--
-- Структура таблицы `users`
--

CREATE TABLE `users` (
  `id` int NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `phone` varchar(15) NOT NULL,
  `role` varchar(25) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Дамп данных таблицы `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `phone`, `role`, `created_at`) VALUES
(2, 'ВФЫЫВ', 'vasya@gmail.com', '$2y$10$1YGaJTaSKUvzh6zFlbZ7be7AD2h5fItV8yHscdJSO2sNFfdAU5op.', '9850520356', '', '2025-02-27 20:04:42'),
(3, 'ФАФАФАФА', 'sdada@gmail.com', '$2y$10$7aH00lUo4JuKlUY4kMbJhucnea0J2p07UtA9NZxMxu59eQ2BCh5My', '9850520356', 'user', '2025-02-27 20:05:58'),
(4, 'dasd', 'vasyssa@gmail.com', '$2y$10$ohYfSx4wiqTNZs.vVqMRkuXolfLxCltFX5cHU6ulIhq.a48WLoPiu', '9850520356', 'user', '2025-03-13 09:29:18'),
(5, 'metadon', 'tasherhddx@gmail.com', '$2y$10$rc1kBOWLY5d8N3OyK5d7me5NO2HAOmPQkTVZ3OBGr//reczZiytgG', '9850520356', 'admin', '2025-03-13 10:50:41'),
(6, 'Карина', 'kirab@gmail.com', '$2y$10$ctOqOYqwUi1DJmQr5uW5quCfQaKg4benWXNaa1hV2lztMH3TOqt46', '9850520356', 'user', '2025-03-13 11:41:03'),
(7, 'Бобрито бандито', 'burdel.brazilia1973@mail.ru', '$2y$10$T1Lnz9B37GmFSNyK0XV9leFAmGh2ZPlX6UaqpS9NBPETRBHZYLrY6', '9850520356', 'user', '2025-03-13 15:51:04'),
(8, 'dsadasdas', 'burdel.bdrazilia1973@mail.ru', '$2y$10$X8Cgc.629OCrT2sjIs0Jgulua845XKb8hnYwQy2z0SgceCCdd5Iwe', '9850520356', 'user', '2025-03-13 16:51:44'),
(9, 'dasdas', 'burdel.brazsilia1973@mail.ru', '$2y$10$rc1kBOWLY5d8N3OyK5d7me5NO2HAOmPQkTVZ3OBGr//reczZiytgG', '9850520356', 'user', '2025-03-13 16:54:20'),
(11, 'ваыва', 'burdefl.brazilia1973@mail.ru', '$2y$10$cNqgBq3NkbbLihC991snU.IxAAhwfAz.RWogh2TjQQx0VCsrA7Wg6', '9850520356f', 'user', '2025-03-16 22:17:18'),
(12, 'Анастасия', 'anastasiya.lavrinova@yandex.ru', '$2y$10$my4j8xMogtA5OyqRN4veO./f28uHG7A17mfRygLx36LPGDpXObR1C', '+79162294378', 'admin', '2025-06-04 18:53:21'),
(13, 'живагно', 'bassov@gmail.com', '$2y$10$RFV3smK5sIQp88uvSSe5C.4fgdQunRl6ccYdgkPXrVzybTE/JbCSe', '79850520356', 'user', '2025-06-12 08:02:50'),
(14, 'asda', 'basssov@gmail.com', '$2y$10$587tHMmo0R3jGj05s68q3uuLlGw3JPyDtYAAiI9I35jLZIiDiUc5O', '99850520356', 'user', '2025-06-12 08:03:11'),
(15, 'dasdasds', 'asda@gmail.com', '$2y$10$n9Qk3XLH88hLwNG9YNORGe9D/zyDfH.yS7IYR3FpuToyEQQ42m4Fa', '89850520354', 'user', '2025-06-12 08:29:34'),
(16, 'живагнер', 'basszov@gmail.com', '$2y$10$ibgdmtYSQSOFSKbRHscJb.mkHeq3I9RdknFFNm3mmQ3tcS10Q3qN6', '72312323123', 'user', '2025-06-12 09:50:44');

--
-- Индексы сохранённых таблиц
--

--
-- Индексы таблицы `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Индексы таблицы `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `fk_category` (`category_id`);
ALTER TABLE `reviews` ADD FULLTEXT KEY `title` (`title`,`description`);

--
-- Индексы таблицы `review_comments`
--
ALTER TABLE `review_comments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `review_id` (`review_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Индексы таблицы `review_images`
--
ALTER TABLE `review_images`
  ADD PRIMARY KEY (`id`),
  ADD KEY `review_id` (`review_id`);

--
-- Индексы таблицы `review_ratings`
--
ALTER TABLE `review_ratings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_rating` (`review_id`,`user_id`),
  ADD KEY `review_ratings_ibfk_2` (`user_id`);

--
-- Индексы таблицы `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT для сохранённых таблиц
--

--
-- AUTO_INCREMENT для таблицы `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=180;

--
-- AUTO_INCREMENT для таблицы `reviews`
--
ALTER TABLE `reviews`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT для таблицы `review_comments`
--
ALTER TABLE `review_comments`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT для таблицы `review_images`
--
ALTER TABLE `review_images`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT для таблицы `review_ratings`
--
ALTER TABLE `review_ratings`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT для таблицы `users`
--
ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- Ограничения внешнего ключа сохраненных таблиц
--

--
-- Ограничения внешнего ключа таблицы `reviews`
--
ALTER TABLE `reviews`
  ADD CONSTRAINT `fk_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`),
  ADD CONSTRAINT `reviews_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `review_comments`
--
ALTER TABLE `review_comments`
  ADD CONSTRAINT `review_comments_ibfk_1` FOREIGN KEY (`review_id`) REFERENCES `reviews` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `review_comments_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `review_images`
--
ALTER TABLE `review_images`
  ADD CONSTRAINT `review_images_ibfk_1` FOREIGN KEY (`review_id`) REFERENCES `reviews` (`id`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `review_ratings`
--
ALTER TABLE `review_ratings`
  ADD CONSTRAINT `review_ratings_ibfk_1` FOREIGN KEY (`review_id`) REFERENCES `reviews` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `review_ratings_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
