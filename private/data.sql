SET NAMES utf8mb4;

DROP TABLE IF EXISTS `ticket_comments`;
DROP TABLE IF EXISTS `tickets`;

CREATE TABLE `tickets` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID заявки',
  `version` int unsigned NOT NULL DEFAULT '1' COMMENT 'Версия',
  `title` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_ru_0900_ai_ci NOT NULL COMMENT 'Название',
  `description` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_ru_0900_ai_ci NOT NULL COMMENT 'Описание',
  `author_email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_ru_0900_ai_ci NOT NULL COMMENT 'Email автора',
  `status` enum('new','in_progress','done','closed') COLLATE utf8mb4_ru_0900_ai_ci NOT NULL DEFAULT 'new' COMMENT 'Статус',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Дата создания',
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Дата обновления',
  PRIMARY KEY (`id`),
  KEY `idx_tickets_status_created` (`status`,`created_at`),
  KEY `idx_tickets_updated` (`updated_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_ru_0900_ai_ci COMMENT='Заявки';

INSERT INTO `tickets` (`id`, `version`, `title`, `description`, `author_email`, `status`, `created_at`, `updated_at`)
VALUES
	(1,4,'Не работает отчёт','При открытии 500 ошибка','ivan@company.local','done','2026-02-22 12:00:00','2026-02-22 16:58:48'),
	(2,2,'Не открывается страница','Выдаёт ошибку 404','pavel@company.local','in_progress','2026-02-22 12:00:00','2026-02-22 16:59:54'),
	(3,3,'На странице с Обратной связью указан старый номер телефона','Нужно заменить на +7 (926) 123-45-67','elena@company.local','closed','2026-02-22 12:00:00','2026-02-22 17:02:15');

CREATE TABLE `ticket_comments` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID комментария',
  `ticket_id` int unsigned NOT NULL COMMENT 'ID заявки',
  `author` varchar(100) COLLATE utf8mb4_ru_0900_ai_ci NOT NULL COMMENT 'Автор',
  `message` longtext COLLATE utf8mb4_ru_0900_ai_ci NOT NULL COMMENT 'Сообщение',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Дата создания',
  PRIMARY KEY (`id`),
  KEY `IDX_DAF76AAB700047D2` (`ticket_id`),
  KEY `idx_comments_ticket_created` (`ticket_id`,`created_at`),
  CONSTRAINT `FK_DAF76AAB700047D2` FOREIGN KEY (`ticket_id`) REFERENCES `tickets` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_ru_0900_ai_ci COMMENT='Комментарии к заявкам';

INSERT INTO `ticket_comments` (`id`, `ticket_id`, `author`, `message`, `created_at`)
VALUES
	(1,1,'Александр','Пришлите ссылку на отчёт, пожалуйста.','2026-02-22 16:56:36'),
	(2,1,'Иван','Вот тут не работает: https://company.local/report.pdf','2026-02-22 16:57:09'),
	(3,1,'Александр','Исправили, проверьте.','2026-02-22 16:58:48'),
	(4,2,'Кирилл','Пришлите, пожалуйста, URL.','2026-02-22 16:59:54'),
	(5,3,'Владимир','Исправили, проверьте.','2026-02-22 17:00:54'),
	(6,3,'Елена','Да, всё правильно. Спасибо!','2026-02-22 17:02:15');
