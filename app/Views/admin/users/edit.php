<?php
$pageTitle = 'Edit User';
ob_start();
?>

<div class="page-header">
    <h2>Edit User</h2>
    <a href="/admin/users" class="btn">Back to Users</a>
</div>

<form method="POST" action="/admin/users/<?= $user['id'] ?>" class="form">
    <?= csrf_field() ?>

    <div class="form-row">
        <div class="form-group">
            <label for="first_name">First Name</label>
            <input type="text" id="first_name" name="first_name" value="<?= htmlspecialchars($user['first_name']) ?>" required>
        </div>
        <div class="form-group">
            <label for="last_name">Last Name</label>
            <input type="text" id="last_name" name="last_name" value="<?= htmlspecialchars($user['last_name']) ?>" required>
        </div>
    </div>

    <div class="form-group">
        <label for="email">Email</label>
        <input type="email" id="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>
    </div>

    <div class="form-group">
        <label for="password">Password (leave blank to keep current)</label>
        <input type="password" id="password" name="password">
    </div>

    <div class="form-group">
        <label for="role">Role</label>
        <select id="role" name="role" required>
            <option value="Student" <?= $user['role'] === 'Student' ? 'selected' : '' ?>>Student</option>
            <option value="Faculty" <?= $user['role'] === 'Faculty' ? 'selected' : '' ?>>Faculty</option>
            <option value="Dean" <?= $user['role'] === 'Dean' ? 'selected' : '' ?>>Dean</option>
            <option value="Admin" <?= $user['role'] === 'Admin' ? 'selected' : '' ?>>Admin</option>
        </select>
    </div>

    <button type="submit" class="btn btn-primary">Update User</button>
</form>

<?php
$content = ob_get_clean();
$pageTitle = 'Edit User';
include __DIR__ . '/../../layouts/dashboard.php';
?>
