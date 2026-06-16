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
              ORDER BY task_status_id ASC";
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
}
