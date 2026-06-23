<?php if ($this->is_admin) { ?>
    <form class="task-create-form" method="post" action="<?= Config::get('URL'); ?>task/create">
        <h3>Admin-Bereich: Neue Aufgabe erstellen</h3>

        <div class="task-form-grid">
            <div class="task-form-row">
                <label>Titel</label>
                <input type="text" name="task_title" required autocomplete="off">
            </div>

            <div class="task-form-row">
                <label>Status</label>
                <select name="task_status_id">
                    <?php foreach ($this->task_statuses as $task_status) { ?>
                        <option value="<?= (int) $task_status->task_status_id; ?>">
                            <?= $this->encodeHTML($task_status->task_status_text); ?>
                        </option>
                    <?php } ?>
                </select>
            </div>

            <div class="task-form-row">
                <label>Zugewiesen</label>
                <select name="assigned_user_id" required>
                    <option value="">Bitte wählen</option>
                    <?php foreach ($this->users as $user) { ?>
                        <option value="<?= (int) $user->user_id; ?>">
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
                        <option value="<?= (int) $user->user_id; ?>">
                            <?= $this->encodeHTML($user->user_name); ?>
                        </option>
                    <?php } ?>
                </select>
            </div>
        </div>

        <div class="task-form-row">
            <label>Beschreibung</label>
            <textarea name="task_description" rows="3"></textarea>
        </div>

        <input type="submit" value="Aufgabe erstellen" autocomplete="off">
    </form>
<?php } ?>
