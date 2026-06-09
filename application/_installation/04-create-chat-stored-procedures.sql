USE `huge`;

DELIMITER //

DROP PROCEDURE IF EXISTS `chat_get_direct_messages`//
CREATE PROCEDURE `chat_get_direct_messages`(
    IN p_user_id INT,
    IN p_current_user_id INT
)
BEGIN
    DECLARE v_conversation_id INT DEFAULT NULL;

    SELECT cm.conversation_id
    INTO v_conversation_id
    FROM conversation_members cm
    INNER JOIN conversations c ON c.ID = cm.conversation_id
    WHERE cm.user_id IN (p_user_id, p_current_user_id)
        AND c.type = 0
    GROUP BY cm.conversation_id
    HAVING COUNT(*) = 2
    LIMIT 1;

    IF v_conversation_id IS NOT NULL THEN
        UPDATE messages
        SET is_read = 1
        WHERE conversation_id = v_conversation_id
            AND sender_id != p_current_user_id;
    END IF;

    SELECT m.ID, m.conversation_id, m.sender_id, m.message, m.is_read, u.user_name
    FROM messages m
    INNER JOIN users u ON u.user_id = m.sender_id
    WHERE m.conversation_id = v_conversation_id
    ORDER BY m.ID ASC;
END//

DROP PROCEDURE IF EXISTS `chat_get_default_group_id`//
CREATE PROCEDURE `chat_get_default_group_id`()
BEGIN
    DECLARE v_conversation_id INT DEFAULT NULL;

    SELECT ID
    INTO v_conversation_id
    FROM conversations
    WHERE type = 1
    ORDER BY ID ASC
    LIMIT 1;

    IF v_conversation_id IS NULL THEN
        INSERT INTO conversations (type) VALUES (1);
        SET v_conversation_id = LAST_INSERT_ID();
    END IF;

    INSERT IGNORE INTO conversation_members (conversation_id, user_id)
    SELECT v_conversation_id, user_id FROM users;

    SELECT v_conversation_id AS conversation_id;
END//

DROP PROCEDURE IF EXISTS `chat_get_default_group_messages`//
CREATE PROCEDURE `chat_get_default_group_messages`(
    IN p_current_user_id INT
)
BEGIN
    DECLARE v_conversation_id INT DEFAULT NULL;

    SELECT ID
    INTO v_conversation_id
    FROM conversations
    WHERE type = 1
    ORDER BY ID ASC
    LIMIT 1;

    IF v_conversation_id IS NULL THEN
        INSERT INTO conversations (type) VALUES (1);
        SET v_conversation_id = LAST_INSERT_ID();
    END IF;

    INSERT IGNORE INTO conversation_members (conversation_id, user_id)
    SELECT v_conversation_id, user_id FROM users;

    UPDATE messages
    SET is_read = 1
    WHERE conversation_id = v_conversation_id
        AND sender_id != p_current_user_id;

    SELECT m.ID, m.conversation_id, m.sender_id, m.message, m.is_read, u.user_name
    FROM messages m
    INNER JOIN users u ON u.user_id = m.sender_id
    WHERE m.conversation_id = v_conversation_id
    ORDER BY m.ID ASC;
END//

DROP PROCEDURE IF EXISTS `chat_count_direct_unread`//
CREATE PROCEDURE `chat_count_direct_unread`(
    IN p_user_id INT,
    IN p_current_user_id INT
)
BEGIN
    SELECT COUNT(m.ID) AS unread_messages
    FROM messages m
    INNER JOIN conversations c ON c.ID = m.conversation_id
    INNER JOIN conversation_members cm1 ON cm1.conversation_id = c.ID
    INNER JOIN conversation_members cm2 ON cm2.conversation_id = c.ID
    WHERE c.type = 0
        AND cm1.user_id = p_user_id
        AND cm2.user_id = p_current_user_id
        AND m.sender_id = p_user_id
        AND m.is_read = 0;
END//

DROP PROCEDURE IF EXISTS `chat_count_default_group_unread`//
CREATE PROCEDURE `chat_count_default_group_unread`(
    IN p_current_user_id INT
)
BEGIN
    DECLARE v_conversation_id INT DEFAULT NULL;

    SELECT ID
    INTO v_conversation_id
    FROM conversations
    WHERE type = 1
    ORDER BY ID ASC
    LIMIT 1;

    IF v_conversation_id IS NULL THEN
        INSERT INTO conversations (type) VALUES (1);
        SET v_conversation_id = LAST_INSERT_ID();
    END IF;

    INSERT IGNORE INTO conversation_members (conversation_id, user_id)
    SELECT v_conversation_id, user_id FROM users;

    SELECT COUNT(ID) AS unread_messages
    FROM messages
    WHERE conversation_id = v_conversation_id
        AND sender_id != p_current_user_id
        AND is_read = 0;
END//

DROP PROCEDURE IF EXISTS `chat_save_direct_message`//
CREATE PROCEDURE `chat_save_direct_message`(
    IN p_current_user_id INT,
    IN p_user_id INT,
    IN p_message TEXT
)
BEGIN
    DECLARE v_conversation_id INT DEFAULT NULL;

    SELECT cm.conversation_id
    INTO v_conversation_id
    FROM conversation_members cm
    INNER JOIN conversations c ON c.ID = cm.conversation_id
    WHERE cm.user_id IN (p_user_id, p_current_user_id)
        AND c.type = 0
    GROUP BY cm.conversation_id
    HAVING COUNT(*) = 2
    LIMIT 1;

    IF v_conversation_id IS NULL THEN
        INSERT INTO conversations (type) VALUES (0);
        SET v_conversation_id = LAST_INSERT_ID();

        INSERT INTO conversation_members (conversation_id, user_id)
        VALUES (v_conversation_id, p_current_user_id),
               (v_conversation_id, p_user_id);
    END IF;

    INSERT INTO messages (conversation_id, sender_id, message)
    VALUES (v_conversation_id, p_current_user_id, p_message);
END//

DROP PROCEDURE IF EXISTS `chat_save_default_group_message`//
CREATE PROCEDURE `chat_save_default_group_message`(
    IN p_current_user_id INT,
    IN p_message TEXT
)
BEGIN
    DECLARE v_conversation_id INT DEFAULT NULL;

    SELECT ID
    INTO v_conversation_id
    FROM conversations
    WHERE type = 1
    ORDER BY ID ASC
    LIMIT 1;

    IF v_conversation_id IS NULL THEN
        INSERT INTO conversations (type) VALUES (1);
        SET v_conversation_id = LAST_INSERT_ID();
    END IF;

    INSERT IGNORE INTO conversation_members (conversation_id, user_id)
    SELECT v_conversation_id, user_id FROM users;

    INSERT INTO messages (conversation_id, sender_id, message)
    VALUES (v_conversation_id, p_current_user_id, p_message);
END//

DROP PROCEDURE IF EXISTS `chat_add_user_to_default_group`//
CREATE PROCEDURE `chat_add_user_to_default_group`(
    IN p_user_id INT
)
BEGIN
    DECLARE v_conversation_id INT DEFAULT NULL;

    SELECT ID
    INTO v_conversation_id
    FROM conversations
    WHERE type = 1
    ORDER BY ID ASC
    LIMIT 1;

    IF v_conversation_id IS NULL THEN
        INSERT INTO conversations (type) VALUES (1);
        SET v_conversation_id = LAST_INSERT_ID();
    END IF;

    INSERT IGNORE INTO conversation_members (conversation_id, user_id)
    VALUES (v_conversation_id, p_user_id);
END//

DROP PROCEDURE IF EXISTS `chat_sync_default_group_members`//
CREATE PROCEDURE `chat_sync_default_group_members`(
    IN p_conversation_id INT
)
BEGIN
    INSERT IGNORE INTO conversation_members (conversation_id, user_id)
    SELECT p_conversation_id, user_id FROM users;
END//

DELIMITER ;
