<?php

/**
 * ChatModel
 * Handles all the chat stuff
 */
class ChatModel
{
    const DEFAULT_GROUP_NAME = 'General Group Chat';

    /**
     * @param int $user_id The user's id
     * @return array The chat with a user
     */
    public static function getChatWithUser($user_id)
    {
        $database = DatabaseFactory::getFactory()->getConnection();

        $sql = "SELECT user_id, user_name, user_email, user_active, user_has_avatar, user_deleted
                FROM users WHERE user_id = :user_id";
        $query = $database->prepare($sql);
        $query->execute(array(':user_id' => $user_id));
        $chat = $query->fetch();

        if (!$chat) {
            return false;
        }

        $chat->is_group = false;
        $chat->messages = array();

        $chat->messages = self::fetchAllProcedure($database, "CALL chat_get_direct_messages(:user_id, :current_user_id)", array(
            ':user_id' => $user_id,
            ':current_user_id' => Session::get('user_id')
        ));

        return $chat;
    }

    /**
     * @return object The fixed default group chat
     */
    public static function getDefaultGroupChat()
    {
        $database = DatabaseFactory::getFactory()->getConnection();
        $conversation = self::fetchProcedure($database, "CALL chat_get_default_group_id()");

        $messages = self::fetchAllProcedure($database, "CALL chat_get_default_group_messages(:current_user_id)", array(
            ':current_user_id' => Session::get('user_id')
        ));

        $chat = new stdClass();
        $chat->is_group = true;
        $chat->name = self::DEFAULT_GROUP_NAME;
        $chat->conversation_id = $conversation->conversation_id;
        $chat->messages = $messages;

        return $chat;
    }

    /**
     * @return object The fixed default group chat for the overview
     */
    public static function getDefaultGroupChatOverview()
    {
        $group_chat = new stdClass();
        $group_chat->name = self::DEFAULT_GROUP_NAME;
        $group_chat->unread_messages = self::getDefaultGroupUnreadMessagesCount();

        return $group_chat;
    }

    /**
     * @param int $user_id The other user's id
     * @return int Number of unread messages from this user
     */
    public static function getUnreadMessagesCount($user_id)
    {
        $database = DatabaseFactory::getFactory()->getConnection();

        $result = self::fetchProcedure($database, "CALL chat_count_direct_unread(:user_id, :current_user_id)", array(
            ':user_id' => $user_id,
            ':current_user_id' => Session::get('user_id')
        ));

        return $result->unread_messages;
    }

    /**
     * @return int Number of unread group messages
     */
    public static function getDefaultGroupUnreadMessagesCount()
    {
        $database = DatabaseFactory::getFactory()->getConnection();

        $result = self::fetchProcedure($database, "CALL chat_count_default_group_unread(:current_user_id)", array(
            ':current_user_id' => Session::get('user_id')
        ));

        return $result->unread_messages;
    }

    /**
     * @param $user_id int id the the user
     * @return 
     */
    public static function saveNewMessage($user_id, $message)
    {   
        $database = DatabaseFactory::getFactory()->getConnection();

        self::executeProcedure($database, "CALL chat_save_direct_message(:current_user_id, :user_id, :message)", array(
            ':current_user_id' => Session::get('user_id'),
            ':user_id' => $user_id,
            ':message' => $message
        ));
    }

    /**
     * @param string $message The message text
     */
    public static function saveNewGroupMessage($message)
    {
        if (!$message) {
            return;
        }

        $database = DatabaseFactory::getFactory()->getConnection();
        self::executeProcedure($database, "CALL chat_save_default_group_message(:current_user_id, :message)", array(
            ':current_user_id' => Session::get('user_id'),
            ':message' => $message
        ));
    }

    /**
     * @param int $user_id The user's id
     */
    public static function addUserToDefaultGroup($user_id)
    {
        $database = DatabaseFactory::getFactory()->getConnection();
        self::executeProcedure($database, "CALL chat_add_user_to_default_group(:user_id)", array(
            ':user_id' => $user_id
        ));
    }

    /**
     * @param PDO $database
     * @param string $sql
     * @param array $parameters
     * @return bool
     */
    private static function executeProcedure($database, $sql, $parameters = array())
    {
        $query = $database->prepare($sql);
        $result = $query->execute($parameters);
        $query->closeCursor();

        return $result;
    }

    /**
     * @param PDO $database
     * @param string $sql
     * @param array $parameters
     * @return object
     */
    private static function fetchProcedure($database, $sql, $parameters = array())
    {
        $query = $database->prepare($sql);
        $query->execute($parameters);
        $result = $query->fetch();
        $query->closeCursor();

        return $result;
    }

    /**
     * @param PDO $database
     * @param string $sql
     * @param array $parameters
     * @return array
     */
    private static function fetchAllProcedure($database, $sql, $parameters = array())
    {
        $query = $database->prepare($sql);
        $query->execute($parameters);
        $result = $query->fetchAll();
        $query->closeCursor();

        return $result;
    }
}
