<div class="container">
    <h1>Admin/index</h1>

    <div class="box">

        <!-- echo out the system feedback (error and success messages) -->
        <?php $this->renderFeedbackMessages(); ?>

        <h3>What happens here ?</h3>

        <div>
            This controller/action/view shows a list of all users in the system. with the ability to soft delete a user
            or suspend a user.
        </div>
        <div>
            <table class="overview-table js-data-table display">
                <thead>
                <tr>
                    <td>Id</td>
                    <td>Avatar</td>
                    <td>Username</td>
                    <td>User's email</td>
                    <td>Role</td>
                    <td>Activated ?</td>
                    <td>Link to user's profile</td>
                    <td>suspension Time in days</td>
                    <td>Soft delete</td>
                    <td>Submit</td>
                </tr>
                </thead>
                <?php foreach ($this->users as $user) { ?>
                    <?php $form_id = 'admin-user-form-' . $user->user_id; ?>
                    <tr class="<?= ($user->user_active == 0 ? 'inactive' : 'active'); ?>">
                        <td><?= $user->user_id; ?></td>
                        <td class="avatar">
                            <?php if (isset($user->user_avatar_link)) { ?>
                                <img src="<?= $user->user_avatar_link; ?>"/>
                            <?php } ?>
                        </td>
                        <td><?= $user->user_name; ?></td>
                        <td><?= $user->user_email; ?></td>
                        <td>
                            <select name="userRole" form="<?= $form_id; ?>">
                                <?php foreach ($this->roles as $role) { ?>
                                    <option value="<?= $role->role_id; ?>" <?php if ($role->role_id == $user->user_account_type) { ?> selected <?php } ?>>
                                        <?= $role->role_name; ?>
                                    </option>
                                <?php } ?>
                            </select>
                        </td>
                        <td><?= ($user->user_active == 0 ? 'No' : 'Yes'); ?></td>
                        <td>
                            <a href="<?= Config::get('URL') . 'profile/showProfile/' . $user->user_id; ?>">Profile</a>
                        </td>
                        <td><input type="number" name="suspension" form="<?= $form_id; ?>" /></td>
                        <td><input type="checkbox" name="softDelete" form="<?= $form_id; ?>" <?php if ($user->user_deleted) { ?> checked <?php } ?> /></td>
                        <td>
                            <form id="<?= $form_id; ?>" action="<?= config::get("URL"); ?>admin/actionAccountSettings" method="post">
                                <input type="hidden" name="user_id" value="<?= $user->user_id; ?>" />
                                <input type="submit" />
                            </form>
                        </td>
                    </tr>
                <?php } ?>
            </table>
        </div>
    </div>
</div>
