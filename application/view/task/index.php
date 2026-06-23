<div class="container">
    <h1>Kanban Board</h1>
    <div class="box">

        <?php $this->renderFeedbackMessages(); ?>

        <?php
        $tasks_by_status = array();

        foreach ($this->task_statuses as $status) {
            $tasks_by_status[$status->task_status_id] = array();
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

        <div class="task-board">
            <?php foreach ($this->task_statuses as $status) { ?>
                <div class="task-column">
                    <div class="task-column-header">
                        <h2><?= $this->encodeHTML($status->task_status_text); ?></h2>
                        <span><?= count($tasks_by_status[$status->task_status_id]); ?></span>
                    </div>

                    <?php if (!empty($tasks_by_status[$status->task_status_id])) { ?>
                        <?php foreach ($tasks_by_status[$status->task_status_id] as $task) { ?>
                            <div class="task-card">
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
