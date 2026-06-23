<div id="task-<?= (int) $task->task_id; ?>" class="task-modal">
    <a class="task-modal-backdrop" href="#"></a>
    <div class="task-modal-content">
        <a class="task-modal-close" href="#">Schließen</a>

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

        <?php
        $is_assigned_user = (int) $task->assigned_user_id === (int) Session::get('user_id');
        $is_tester_user = (int) $task->tester_user_id === (int) Session::get('user_id');
        $can_update_status = $is_assigned_user || $is_tester_user;
        ?>

        <?php if ($this->is_admin) { ?>
            <form class="task-form" method="post" action="<?= Config::get('URL'); ?>task/update">
                <input type="hidden" name="task_id" value="<?= (int) $task->task_id; ?>">

                <div class="task-form-row">
                    <label>Titel</label>
                    <input type="text" name="task_title" value="<?= $this->encodeHTML($task->task_title); ?>" required autocomplete="off">
                </div>

                <div class="task-form-row">
                    <label>Beschreibung</label>
                    <textarea name="task_description" rows="4"><?= $this->encodeHTML($task->task_description); ?></textarea>
                </div>

                <div class="task-form-grid">
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
                        <label>Zugewiesen</label>
                        <select name="assigned_user_id" required>
                            <?php foreach ($this->users as $user) { ?>
                                <option value="<?= (int) $user->user_id; ?>" <?php if ($user->user_id == $task->assigned_user_id) { ?> selected <?php } ?>>
                                    <?= $this->encodeHTML($user->user_name); ?>
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
                </div>

                <div class="task-form-actions">
                    <input type="submit" value="Änderungen speichern" autocomplete="off">
                    <a class="task-delete-link" href="<?= Config::get('URL'); ?>task/delete/<?= (int) $task->task_id; ?>">Löschen</a>
                </div>
            </form>
        <?php } else if ($can_update_status) { ?>
            <form class="task-form" method="post" action="<?= Config::get('URL'); ?>task/updateStatus">
                <input type="hidden" name="task_id" value="<?= (int) $task->task_id; ?>">

                <div class="task-form-grid">
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

                    <?php if ($is_assigned_user) { ?>
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
                    <?php } ?>
                </div>

                <input type="submit" value="Status speichern" autocomplete="off">
            </form>
        <?php } else { ?>
            <p class="task-readonly-note">Du kannst diesen Task nur ansehen.</p>
        <?php } ?>
    </div>
</div>
