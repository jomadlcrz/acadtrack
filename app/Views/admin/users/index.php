<?php
$pageTitle = 'Manage Users';
ob_start();
?>

<div class="page-header">
    <h2>Users</h2>
    <a href="/admin/users/create" class="btn btn-primary">Add User</a>
</div>

<div class="filter-bar">
    <form method="GET" action="/admin/users">
        <select name="role" onchange="this.form.submit()">
            <option value="">All Roles</option>
            <option value="Admin" <?= ($currentRole ?? '') === 'Admin' ? 'selected' : '' ?>>Admin</option>
            <option value="Dean" <?= ($currentRole ?? '') === 'Dean' ? 'selected' : '' ?>>Dean</option>
            <option value="Faculty" <?= ($currentRole ?? '') === 'Faculty' ? 'selected' : '' ?>>Faculty</option>
            <option value="Student" <?= ($currentRole ?? '') === 'Student' ? 'selected' : '' ?>>Student</option>
        </select>
    </form>
</div>

<table class="table">
    <thead>
        <tr>
            <th>Name</th>
            <th>Email</th>
            <th>Role</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($users['data'] ?? [] as $user): ?>
        <tr>
            <td><?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?></td>
            <td><?= htmlspecialchars($user['email']) ?></td>
            <td><span class="badge badge-<?= strtolower($user['role']) ?>"><?= htmlspecialchars($user['role']) ?></span></td>
            <td>
                <a href="/admin/users/<?= $user['id'] ?>/edit" class="btn btn-sm">Edit</a>
                <form method="POST" action="/admin/users/<?= $user['id'] ?>/delete" style="display:inline">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">Delete</button>
                </form>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?php if (!empty($users['data'])): ?>
    <?php include __DIR__ . '/../../components/pagination.php'; ?>
<?php endif; ?>

<?php
$content = ob_get_clean();
$pageTitle = 'Manage Users';
include __DIR__ . '/../../layouts/dashboard.php';
?>
