<?php

/**
 * TaskController
 * Connects the Kanban task routes with the TaskModel.
 */
class TaskController extends Controller
{
    const TESTING_STATUS_ID = 4;

    private $is_admin;

    public function __construct()
    {
        parent::__construct();
        Auth::checkAuthentication();
        $this->is_admin = (int) Session::get('user_account_type') === 7;
    }

    /**
     * Loads all data the Kanban view needs later.
     */
    public function index()
    {
        $this->View->render('task/index', array(
            'tasks' => $this->getVisibleTasks(),
            'task_statuses' => TaskModel::getAllTaskStatuses(),
            'users' => UserModel::getPublicProfilesOfAllUsers(),
            'is_admin' => $this->is_admin
        ));
    }

    /**
     * Loads one task with comments and admin-only history data.
     * @param int $task_id id of the task
     */
    public function show($task_id)
    {
        $task = TaskModel::getTask($task_id);

        if (!$this->canUseTask($task)) {
            Redirect::to('task');
            return;
        }

        $this->View->render('task/show', array(
            'task' => $task,
            'comments' => TaskModel::getCommentsForTask($task_id),
            'change_history' => $this->is_admin ? TaskModel::getChangeHistoryForTask($task_id) : array(),
            'task_statuses' => TaskModel::getAllTaskStatuses(),
            'users' => UserModel::getPublicProfilesOfAllUsers(),
            'is_admin' => $this->is_admin
        ));
    }

    /**
     * Creates a new task from form data.
     */
    public function create()
    {
        if (!$this->is_admin) {
            self::redirectToTask();
        }

        TaskModel::createTask(
            Request::post('task_title'),
            Request::post('task_description'),
            Request::post('task_status_id') ?: 1,
            Request::post('assigned_user_id'),
            Request::post('tester_user_id')
        );

        self::redirectToTask();
    }

    /**
     * Updates all editable task data from form data.
     */
    public function update()
    {
        if (!$this->is_admin) {
            self::redirectToTask();
        }

        TaskModel::updateTask(
            Request::post('task_id'),
            Request::post('task_title'),
            Request::post('task_description'),
            Request::post('task_status_id') ?: 1,
            Request::post('assigned_user_id'),
            Request::post('tester_user_id')
        );

        self::redirectToTask();
    }

    /**
     * Updates only the task status and keeps the other task data.
     */
    public function updateStatus()
    {
        $task = TaskModel::getTask(Request::post('task_id'));

        if ($this->canUseTask($task)) {
            TaskModel::updateTask(
                $task->task_id,
                $task->task_title,
                $task->task_description,
                Request::post('task_status_id') ?: $task->task_status_id,
                $task->assigned_user_id,
                $this->getTesterUserIdForStatusUpdate($task)
            );
        }

        self::redirectToTask();
    }

    /**
     * Updates the assigned user and optionally the tester user.
     */
    public function assignUser()
    {
        $task = TaskModel::getTask(Request::post('task_id'));

        if ($this->canAssignUser($task)) {
            TaskModel::updateTask(
                $task->task_id,
                $task->task_title,
                $task->task_description,
                $task->task_status_id,
                $this->is_admin ? Request::post('assigned_user_id') : $task->assigned_user_id,
                Request::post('tester_user_id') ?: $task->tester_user_id
            );
        }

        self::redirectToTask();
    }

    /**
     * Adds a comment to a task.
     */
    public function addComment()
    {
        $task_id = Request::post('task_id');
        $task = TaskModel::getTask($task_id);

        if ($task) {
            TaskModel::createComment(
                $task_id,
                Request::post('comment_text')
            );
        }

        Redirect::to('task/show/' . $task_id);
    }

    /**
     * Returns comments for later frontend use.
     * @param int $task_id id of the task
     */
    public function comments($task_id)
    {
        if (!TaskModel::getTask($task_id)) {
            $this->View->renderJSON(array());
            return;
        }

        $this->View->renderJSON(TaskModel::getCommentsForTask($task_id));
    }

    /**
     * Returns change history only for administrators.
     * @param int $task_id id of the task
     */
    public function history($task_id)
    {
        if (!$this->is_admin) {
            $this->View->renderJSON(array());
            return;
        }

        $this->View->renderJSON(TaskModel::getChangeHistoryForTask($task_id));
    }

    /**
     * Deletes a task.
     * @param int $task_id id of the task
     */
    public function delete($task_id)
    {
        if (!$this->is_admin) {
            self::redirectToTask();
        }

        TaskModel::deleteTask($task_id);
        self::redirectToTask();
    }

    /**
     * Gets all visible tasks for the current user.
     * @return array
     */
    private function getVisibleTasks()
    {
        if ($this->is_admin) {
            return TaskModel::getAllTasks();
        }

        return TaskModel::getTasksForUser(Session::get('user_id'));
    }

    /**
     * Checks if the current user can access a task.
     * @param object|bool $task task data
     * @return bool
     */
    private function canUseTask($task)
    {
        if (!$task) {
            return false;
        }

        if ($this->is_admin) {
            return true;
        }

        $user_id = (int) Session::get('user_id');

        return (int) $task->assigned_user_id === $user_id || (int) $task->tester_user_id === $user_id;
    }

    /**
     * Checks if the current user can assign a user to a task.
     * @param object|bool $task task data
     * @return bool
     */
    private function canAssignUser($task)
    {
        if (!$task) {
            return false;
        }

        if ($this->is_admin) {
            return true;
        }

        return $this->canUseTask($task) && (int) $task->task_status_id === self::TESTING_STATUS_ID;
    }

    /**
     * Gets the tester user id for a status update.
     * @param object $task task data
     * @return int|null
     */
    private function getTesterUserIdForStatusUpdate($task)
    {
        $user_id = (int) Session::get('user_id');
        $task_status_id = Request::post('task_status_id') ?: $task->task_status_id;

        if (!$this->is_admin && (int) $task->tester_user_id === $user_id) {
            return null;
        }

        if ((int) $task_status_id === self::TESTING_STATUS_ID && Request::post('tester_user_id')) {
            return Request::post('tester_user_id');
        }

        return $task->tester_user_id;
    }

    /**
     * Redirects back to the task overview.
     */
    private static function redirectToTask()
    {
        Redirect::to('task');
        exit();
    }
}
