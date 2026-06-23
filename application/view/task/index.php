<div class="container">
    <h1>Kanban Board</h1>
    <div class="box">

        <?php $this->renderFeedbackMessages(); ?>

        <?php
        $tasks_by_status = array();
        $task_status_names = array();
        $user_names = array();

        foreach ($this->task_statuses as $status) {
            $tasks_by_status[$status->task_status_id] = array();
            $task_status_names[$status->task_status_id] = $status->task_status_text;
        }

        foreach ($this->users as $user) {
            $user_names[$user->user_id] = $user->user_name;
        }

        foreach ($this->tasks as $task) {
            $tasks_by_status[$task->task_status_id][] = $task;
        }
        ?>

        <div class="task-board-header">
            <div>
                <h2>Aufgabenübersicht</h2>
                <p><?= $this->is_admin ? 'Alle Aufgaben im Projekt' : 'Deine zugewiesenen Aufgaben'; ?></p>
            </div>
        </div>

        <div class="task-board" data-update-status-url="<?php echo Config::get('URL'); ?>task/updateStatus">
            <?php foreach ($this->task_statuses as $status) { ?>
                <div class="task-column" data-status-id="<?= (int) $status->task_status_id; ?>">
                    <div class="task-column-header">
                        <h2><?= $this->encodeHTML($status->task_status_text); ?></h2>
                        <span><?= count($tasks_by_status[$status->task_status_id]); ?></span>
                    </div>

                    <?php if (!empty($tasks_by_status[$status->task_status_id])) { ?>
                        <?php foreach ($tasks_by_status[$status->task_status_id] as $task) { ?>
                            <div class="task-card" data-task-id="<?= (int) $task->task_id; ?>" draggable="true">
                                <a class="task-card-link" href="#task-<?= (int) $task->task_id; ?>">
                                    <span class="task-card-status"><?= $this->encodeHTML($task->task_status_text); ?></span>

                                    <strong><?= $this->encodeHTML($task->task_title); ?></strong>
                                    <span><?= $task->assigned_user_name ? $this->encodeHTML($task->assigned_user_name) : 'Nicht zugewiesen'; ?></span>
                                </a>
                            </div>

                            <?php require Config::get('PATH_VIEW') . 'task/taskModal.php'; ?>
                        <?php } ?>
                    <?php } else { ?>
                        <div class="task-empty">Keine Aufgaben</div>
                    <?php } ?>
                </div>
            <?php } ?>
        </div>

        <?php require Config::get('PATH_VIEW') . 'task/createForm.php'; ?>
    </div>
</div>

<script src="<?php echo Config::get('URL'); ?>js/task.js"></script>
