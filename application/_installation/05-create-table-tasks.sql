CREATE TABLE IF NOT EXISTS `huge`.`task_statuses` (
 `task_status_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
 `task_status_text` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
 PRIMARY KEY (`task_status_id`),
 UNIQUE KEY `task_status_text` (`task_status_text`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='available task statuses';

INSERT IGNORE INTO `huge`.`task_statuses` (`task_status_id`, `task_status_text`) VALUES
  (1, 'Offen'),
  (2, 'In Bearbeitung'),
  (3, 'Erledigt'),
  (4, 'Testing');

CREATE TABLE IF NOT EXISTS `huge`.`tasks` (
 `task_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
 `task_title` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
 `task_description` text COLLATE utf8_unicode_ci,
 `task_status_id` int(11) unsigned NOT NULL DEFAULT '1',
 `assigned_user_id` int(11) DEFAULT NULL,
 `tester_user_id` int(11) DEFAULT NULL,
 PRIMARY KEY (`task_id`),
 KEY `task_status_id` (`task_status_id`),
 KEY `assigned_user_id` (`assigned_user_id`),
 KEY `tester_user_id` (`tester_user_id`),
 CONSTRAINT `tasks_status_fk` FOREIGN KEY (`task_status_id`) REFERENCES `huge`.`task_statuses` (`task_status_id`) ON UPDATE CASCADE,
 CONSTRAINT `tasks_assigned_user_fk` FOREIGN KEY (`assigned_user_id`) REFERENCES `huge`.`users` (`user_id`) ON DELETE SET NULL ON UPDATE CASCADE,
 CONSTRAINT `tasks_tester_user_fk` FOREIGN KEY (`tester_user_id`) REFERENCES `huge`.`users` (`user_id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='tasks assigned to users';

INSERT IGNORE INTO `huge`.`tasks` (`task_id`, `task_title`, `task_description`, `task_status_id`, `assigned_user_id`, `tester_user_id`) VALUES
  (1, 'Datenbankstruktur anlegen', 'Aufgabentabelle mit Titel, Beschreibung, Status und Benutzerzuweisung erstellen.', 3, 1, NULL),
  (2, 'Backend vorbereiten', 'Datenbank ist für die spätere Backend-Anbindung vorbereitet.', 2, 1, NULL),
  (3, 'Testdaten prüfen', 'Prüfen, ob Aufgaben mit Status und Benutzerzuweisung gespeichert werden können.', 1, 2, NULL),
  (4, 'Testing vorbereiten', 'Aufgabe ist bereit zum Testen und einem Tester zugewiesen.', 4, 1, 2);

CREATE TABLE IF NOT EXISTS `huge`.`task_comments` (
 `task_comment_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
 `task_id` int(11) unsigned NOT NULL,
 `user_id` int(11) NOT NULL,
 `comment_text` text COLLATE utf8_unicode_ci NOT NULL,
 `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
 PRIMARY KEY (`task_comment_id`),
 KEY `task_id` (`task_id`),
 KEY `user_id` (`user_id`),
 CONSTRAINT `task_comments_task_fk` FOREIGN KEY (`task_id`) REFERENCES `huge`.`tasks` (`task_id`) ON DELETE CASCADE ON UPDATE CASCADE,
 CONSTRAINT `task_comments_user_fk` FOREIGN KEY (`user_id`) REFERENCES `huge`.`users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='comments for tasks';

CREATE TABLE IF NOT EXISTS `huge`.`task_change_history` (
 `task_change_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
 `task_id` int(11) unsigned NOT NULL,
 `changed_field` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
 `old_value` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
 `new_value` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
 `changed_by_user_id` int(11) NOT NULL,
 `changed_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
 PRIMARY KEY (`task_change_id`),
 KEY `task_id` (`task_id`),
 KEY `changed_by_user_id` (`changed_by_user_id`),
 CONSTRAINT `task_change_history_task_fk` FOREIGN KEY (`task_id`) REFERENCES `huge`.`tasks` (`task_id`) ON DELETE CASCADE ON UPDATE CASCADE,
 CONSTRAINT `task_change_history_user_fk` FOREIGN KEY (`changed_by_user_id`) REFERENCES `huge`.`users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='status and user assignment changes for tasks';
