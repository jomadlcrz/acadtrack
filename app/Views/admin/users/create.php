<?php
$pageTitle = 'Add User';
ob_start();
?>

<div class="page-header">
    <h2>Add New User</h2>
    <a href="/admin/users" class="btn">Back to Users</a>
</div>

<form method="POST" action="/admin/users" class="form">
    <?= csrf_field() ?>

    <div class="form-row">
        <div class="form-group">
            <label for="first_name">First Name</label>
            <input type="text" id="first_name" name="first_name" required>
        </div>
        <div class="form-group">
            <label for="last_name">Last Name</label>
            <input type="text" id="last_name" name="last_name" required>
        </div>
    </div>

    <div class="form-group">
        <label for="email">Email</label>
        <input type="email" id="email" name="email" required>
    </div>

    <div class="form-group">
        <label for="password">Password</label>
        <input type="password" id="password" name="password" required>
    </div>

    <div class="form-group">
        <label for="role">Role</label>
        <select id="role" name="role" required>
            <option value="Student">Student</option>
            <option value="Faculty">Faculty</option>
            <option value="Dean">Dean</option>
            <option value="Admin">Admin</option>
        </select>
    </div>

    <div class="form-group">
        <label for="student_number">Student Number (if applicable)</label>
        <input type="text" id="student_number" name="student_number">
    </div>

    <button type="submit" class="btn btn-primary">Create User</button>
</form>

<?php
$content = ob_get_clean();
$pageTitle = 'Add User';
include __DIR__ . '/../../layouts/dashboard.php';
?>
