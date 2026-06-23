<?php

/**
 * TaskModel
 * Handles database access for Kanban tasks.
 */
class TaskModel
{
    /**
     * Get all tasks with status and assigned user data.
     * @return array
     */
    public static function getAllTasks()
    {
        $database = DatabaseFactory::getFactory()->getConnection();

        $sql = "SELECT t.task_id, t.task_title, t.task_description, t.task_status_id,
                       s.task_status_text,
                       t.assigned_user_id, assigned.user_name AS assigned_user_name,
                       t.tester_user_id, tester.user_name AS tester_user_name
                  FROM tasks t
            INNER JOIN task_statuses s ON t.task_status_id = s.task_status_id
             LEFT JOIN users assigned ON t.assigned_user_id = assigned.user_id
             LEFT JOIN users tester ON t.tester_user_id = tester.user_id
              ORDER BY t.task_id ASC";
        $query = $database->prepare($sql);
        $query->execute();

        return $query->fetchAll();
    }

    /**
     * Get all tasks assigned to a user or marked for testing by this user.
     * @param int $user_id id of the user
     * @return array
     */
    public static function getTasksForUser($user_id)
    {
        $database = DatabaseFactory::getFactory()->getConnection();

        $sql = "SELECT t.task_id, t.task_title, t.task_description, t.task_status_id,
                       s.task_status_text,
                       t.assigned_user_id, assigned.user_name AS assigned_user_name,
                       t.tester_user_id, tester.user_name AS tester_user_name
                  FROM tasks t
            INNER JOIN task_statuses s ON t.task_status_id = s.task_status_id
             LEFT JOIN users assigned ON t.assigned_user_id = assigned.user_id
             LEFT JOIN users tester ON t.tester_user_id = tester.user_id
                 WHERE t.assigned_user_id = :user_id
                    OR t.tester_user_id = :user_id
              ORDER BY t.task_id ASC";
        $query = $database->prepare($sql);
        $query->execute(array(':user_id' => (int) $user_id));

        return $query->fetchAll();
    }

    /**
     * Get a single task.
     * @param int $task_id id of the task
     * @return object|bool
     */
    public static function getTask($task_id)
    {
        if (!$task_id) {
            return false;
        }

        $database = DatabaseFactory::getFactory()->getConnection();

        $sql = "SELECT t.task_id, t.task_title, t.task_description, t.task_status_id,
                       s.task_status_text,
                       t.assigned_user_id, assigned.user_name AS assigned_user_name,
                       t.tester_user_id, tester.user_name AS tester_user_name
                  FROM tasks t
            INNER JOIN task_statuses s ON t.task_status_id = s.task_status_id
             LEFT JOIN users assigned ON t.assigned_user_id = assigned.user_id
             LEFT JOIN users tester ON t.tester_user_id = tester.user_id
                 WHERE t.task_id = :task_id
                 LIMIT 1";
        $query = $database->prepare($sql);
        $query->execute(array(':task_id' => (int) $task_id));

        return $query->fetch();
    }

    /**
     * Get all available task statuses.
     * @return array
     */
    public static function getAllTaskStatuses()
    {
        $database = DatabaseFactory::getFactory()->getConnection();

        $sql = "SELECT task_status_id, task_status_text
                  FROM task_statuses
              ORDER BY FIELD(task_status_id, 1, 2, 4, 3)";
        $query = $database->prepare($sql);
        $query->execute();

        return $query->fetchAll();
    }

    /**
     * Create a new task.
     * @param string $task_title
     * @param string $task_description
     * @param int $task_status_id
     * @param int $assigned_user_id
     * @param int|null $tester_user_id
     * @return bool
     */
    public static function createTask($task_title, $task_description = null, $task_status_id = 1, $assigned_user_id = null, $tester_user_id = null)
    {
        $task_title = trim((string) $task_title);
        $assigned_user_id = (int) $assigned_user_id;

        if (!$task_title || !$assigned_user_id) {
            Session::add('feedback_negative', 'Task could not be created.');
            return false;
        }

        $tester_user_id = (int) $tester_user_id > 0 ? (int) $tester_user_id : null;

        $database = DatabaseFactory::getFactory()->getConnection();

        $sql = "INSERT INTO tasks (task_title, task_description, task_status_id, assigned_user_id, tester_user_id)
                VALUES (:task_title, :task_description, :task_status_id, :assigned_user_id, :tester_user_id)";
        $query = $database->prepare($sql);
        $query->execute(array(
            ':task_title' => $task_title,
            ':task_description' => $task_description,
            ':task_status_id' => (int) $task_status_id,
            ':assigned_user_id' => $assigned_user_id,
            ':tester_user_id' => $tester_user_id
        ));

        if ($query->rowCount() === 1) {
            return true;
        }

        Session::add('feedback_negative', 'Task could not be created.');
        return false;
    }

    /**
     * Update an existing task.
     * @param int $task_id
     * @param string $task_title
     * @param string $task_description
     * @param int $task_status_id
     * @param int $assigned_user_id
     * @param int|null $tester_user_id
     * @return bool
     */
    public static function updateTask($task_id, $task_title, $task_description = null, $task_status_id = 1, $assigned_user_id = null, $tester_user_id = null)
    {
        $task_id = (int) $task_id;
        $task_title = trim((string) $task_title);
        $assigned_user_id = (int) $assigned_user_id;

        if (!$task_id || !$task_title || !$assigned_user_id) {
            Session::add('feedback_negative', 'Task could not be updated.');
            return false;
        }

        $tester_user_id = (int) $tester_user_id > 0 ? (int) $tester_user_id : null;
        $old_task = self::getTask($task_id);

        if (!$old_task) {
            Session::add('feedback_negative', 'Task could not be updated.');
            return false;
        }

        $database = DatabaseFactory::getFactory()->getConnection();

        $sql = "UPDATE tasks
                   SET task_title = :task_title,
                       task_description = :task_description,
                       task_status_id = :task_status_id,
                       assigned_user_id = :assigned_user_id,
                       tester_user_id = :tester_user_id
                 WHERE task_id = :task_id
                 LIMIT 1";
        $query = $database->prepare($sql);
        $query->execute(array(
            ':task_id' => $task_id,
            ':task_title' => $task_title,
            ':task_description' => $task_description,
            ':task_status_id' => (int) $task_status_id,
            ':assigned_user_id' => $assigned_user_id,
            ':tester_user_id' => $tester_user_id
        ));

        if (self::getTask($task_id)) {
            self::createChangeHistory($old_task, (int) $task_status_id, $assigned_user_id, $tester_user_id);
            return true;
        }

        Session::add('feedback_negative', 'Task could not be updated.');
        return false;
    }

    /**
     * Delete a task.
     * @param int $task_id
     * @return bool
     */
    public static function deleteTask($task_id)
    {
        if (!$task_id) {
            return false;
        }

        $database = DatabaseFactory::getFactory()->getConnection();

        $sql = "DELETE FROM tasks WHERE task_id = :task_id LIMIT 1";
        $query = $database->prepare($sql);
        $query->execute(array(':task_id' => (int) $task_id));

        if ($query->rowCount() === 1) {
            return true;
        }

        Session::add('feedback_negative', 'Task could not be deleted.');
        return false;
    }

    /**
     * Get all comments for a task. Comments are available for logged-in users via the controller.
     * @param int $task_id
     * @return array
     */
    public static function getCommentsForTask($task_id)
    {
        $database = DatabaseFactory::getFactory()->getConnection();

        $sql = "SELECT c.task_comment_id, c.task_id, c.user_id, u.user_name, c.comment_text, c.created_at
                  FROM task_comments c
            INNER JOIN users u ON c.user_id = u.user_id
                 WHERE c.task_id = :task_id
              ORDER BY c.created_at ASC, c.task_comment_id ASC";
        $query = $database->prepare($sql);
        $query->execute(array(':task_id' => (int) $task_id));

        return $query->fetchAll();
    }

    /**
     * Create a comment for a task.
     * @param int $task_id
     * @param string $comment_text
     * @return bool
     */
    public static function createComment($task_id, $comment_text)
    {
        $task_id = (int) $task_id;
        $comment_text = trim((string) $comment_text);
        $user_id = (int) Session::get('user_id');

        if (!$task_id || !$comment_text || !$user_id) {
            Session::add('feedback_negative', 'Comment could not be created.');
            return false;
        }

        $database = DatabaseFactory::getFactory()->getConnection();

        $sql = "INSERT INTO task_comments (task_id, user_id, comment_text)
                VALUES (:task_id, :user_id, :comment_text)";
        $query = $database->prepare($sql);
        $query->execute(array(
            ':task_id' => $task_id,
            ':user_id' => $user_id,
            ':comment_text' => $comment_text
        ));

        if ($query->rowCount() === 1) {
            return true;
        }

        Session::add('feedback_negative', 'Comment could not be created.');
        return false;
    }

    /**
     * Get change history for a task. Non-admin users receive no history data.
     * @param int $task_id
     * @return array
     */
    public static function getChangeHistoryForTask($task_id)
    {
        if ((int) Session::get('user_account_type') !== 7) {
            return array();
        }

        $database = DatabaseFactory::getFactory()->getConnection();

        $sql = "SELECT h.task_change_id, h.task_id, h.changed_field, h.old_value, h.new_value,
                       h.changed_by_user_id, u.user_name AS changed_by_user_name, h.changed_at
                  FROM task_change_history h
            INNER JOIN users u ON h.changed_by_user_id = u.user_id
                 WHERE h.task_id = :task_id
              ORDER BY h.changed_at DESC, h.task_change_id DESC";
        $query = $database->prepare($sql);
        $query->execute(array(':task_id' => (int) $task_id));

        return $query->fetchAll();
    }

    private static function createChangeHistory($old_task, $new_status_id, $new_assigned_user_id, $new_tester_user_id)
    {
        $changed_by_user_id = (int) Session::get('user_id');

        if (!$changed_by_user_id) {
            return;
        }

        self::createHistoryEntryIfChanged(
            $old_task->task_id,
            'task_status_id',
            $old_task->task_status_id,
            $new_status_id,
            $changed_by_user_id
        );

        self::createHistoryEntryIfChanged(
            $old_task->task_id,
            'assigned_user_id',
            $old_task->assigned_user_id,
            $new_assigned_user_id,
            $changed_by_user_id
        );

        self::createHistoryEntryIfChanged(
            $old_task->task_id,
            'tester_user_id',
            $old_task->tester_user_id,
            $new_tester_user_id,
            $changed_by_user_id
        );
    }

    private static function createHistoryEntryIfChanged($task_id, $changed_field, $old_value, $new_value, $changed_by_user_id)
    {
        if ((string) $old_value === (string) $new_value) {
            return;
        }

        $database = DatabaseFactory::getFactory()->getConnection();

        $sql = "INSERT INTO task_change_history (task_id, changed_field, old_value, new_value, changed_by_user_id)
                VALUES (:task_id, :changed_field, :old_value, :new_value, :changed_by_user_id)";
        $query = $database->prepare($sql);
        $query->execute(array(
            ':task_id' => (int) $task_id,
            ':changed_field' => $changed_field,
            ':old_value' => $old_value === null ? null : (string) $old_value,
            ':new_value' => $new_value === null ? null : (string) $new_value,
            ':changed_by_user_id' => (int) $changed_by_user_id
        ));
    }
}
