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
        $history_field_names = array(
            'task_status_id' => 'Status',
            'assigned_user_id' => 'Zuweisung',
            'tester_user_id' => 'Tester'
        );
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

            <div class="task-history">
                <h3>Änderungsverlauf</h3>

                <?php if (empty($task->change_history)) { ?>
                    <p>Noch keine Änderungen vorhanden.</p>
                <?php } else { ?>
                    <ul>
                        <?php foreach ($task->change_history as $history_entry) { ?>
                            <?php
                            $field_name = isset($history_field_names[$history_entry->changed_field]) ? $history_field_names[$history_entry->changed_field] : $history_entry->changed_field;
                            $old_value = $history_entry->old_value;
                            $new_value = $history_entry->new_value;

                            if ($history_entry->changed_field === 'task_status_id') {
                                $old_value = isset($task_status_names[$history_entry->old_value]) ? $task_status_names[$history_entry->old_value] : $old_value;
                                $new_value = isset($task_status_names[$history_entry->new_value]) ? $task_status_names[$history_entry->new_value] : $new_value;
                            }

                            if ($history_entry->changed_field === 'assigned_user_id' || $history_entry->changed_field === 'tester_user_id') {
                                $old_value = isset($user_names[$history_entry->old_value]) ? $user_names[$history_entry->old_value] : $old_value;
                                $new_value = isset($user_names[$history_entry->new_value]) ? $user_names[$history_entry->new_value] : $new_value;
                            }

                            $old_value = $old_value ? $old_value : '-';
                            $new_value = $new_value ? $new_value : '-';
                            ?>

                            <li>
                                <strong><?= $this->encodeHTML($field_name); ?>:</strong>
                                <?= $this->encodeHTML($old_value); ?> &rarr; <?= $this->encodeHTML($new_value); ?>
                                <span>
                                    <?= $this->encodeHTML($history_entry->changed_by_user_name); ?>,
                                    <?= date('d.m.Y H:i', strtotime($history_entry->changed_at)); ?>
                                </span>
                            </li>
                        <?php } ?>
                    </ul>
                <?php } ?>
            </div>
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
