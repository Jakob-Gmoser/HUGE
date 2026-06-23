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

                            <div id="task-<?= (int) $task->task_id; ?>" class="task-modal">
                                <a class="task-modal-backdrop" href="#"></a>
                                <div class="task-modal-content">
                                    <a class="task-modal-close" href="#">Schliessen</a>

                                    <span class="task-card-status"><?= $this->encodeHTML($task->task_status_text); ?></span>
                                    <h2><?= $this->encodeHTML($task->task_title); ?></h2>

                                    <?php if ($task->task_description) { ?>
                                        <p><?= nl2br($this->encodeHTML($task->task_description)); ?></p>
                                    <?php } else { ?>
                                        <p>Keine Beschreibung vorhanden.</p>
                                    <?php } ?>

                                    <dl class="task-meta">
                                        <dt>Zugewiesen</dt>
                                        <dd><?= $task->assigned_user_name ? $this->encodeHTML($task->assigned_user_name) : '-'; ?></dd>

                                        <dt>Tester</dt>
                                        <dd><?= $task->tester_user_name ? $this->encodeHTML($task->tester_user_name) : '-'; ?></dd>

                                        <dt>Status</dt>
                                        <dd><?= $this->encodeHTML($task->task_status_text); ?></dd>
                                    </dl>

                                    <form class="task-form" method="post" action="<?= Config::get('URL'); ?>task/updateStatus">
                                        <input type="hidden" name="task_id" value="<?= (int) $task->task_id; ?>">

                                        <div class="task-form-row">
                                            <label>Status</label>
                                            <select name="task_status_id">
                                                <?php foreach ($this->task_statuses as $task_status) { ?>
                                                    <option value="<?= (int) $task_status->task_status_id; ?>" <?php if ($task_status->task_status_id == $task->task_status_id) { ?> selected <?php } ?>>
                                                        <?= $this->encodeHTML($task_status->task_status_text); ?>
                                                    </option>
                                                <?php } ?>
                                            </select>
                                        </div>

                                        <div class="task-form-row">
                                            <label>Tester</label>
                                            <select name="tester_user_id">
                                                <option value="">-</option>
                                                <?php foreach ($this->users as $user) { ?>
                                                    <option value="<?= (int) $user->user_id; ?>" <?php if ($user->user_id == $task->tester_user_id) { ?> selected <?php } ?>>
                                                        <?= $this->encodeHTML($user->user_name); ?>
                                                    </option>
                                                <?php } ?>
                                            </select>
                                        </div>

                                        <input type="submit" value="Speichern" autocomplete="off">
                                    </form>
                                </div>
                            </div>
                        <?php } ?>
                    <?php } else { ?>
                        <div class="task-empty">Keine Aufgaben</div>
                    <?php } ?>
                </div>
            <?php } ?>
        </div>
    </div>
</div>
