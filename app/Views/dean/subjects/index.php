<?php
$pageTitle = 'Subjects';
ob_start();
?>

<div class="page-header">
    <h2>Manage Subjects</h2>
</div>

<form method="POST" action="/dean/subjects" class="form-inline">
    <?= csrf_field() ?>

    <div class="form-group">
        <label for="code">Code</label>
        <input type="text" id="code" name="code" required placeholder="e.g., CS101">
    </div>

    <div class="form-group">
        <label for="name">Name</label>
        <input type="text" id="name" name="name" required placeholder="Subject name">
    </div>

    <div class="form-group">
        <label for="year_level">Year Level</label>
        <select id="year_level" name="year_level" required>
            <option value="1">1st Year</option>
            <option value="2">2nd Year</option>
            <option value="3">3rd Year</option>
            <option value="4">4th Year</option>
        </select>
    </div>

    <div class="form-group">
        <label for="semester">Semester</label>
        <select id="semester" name="semester" required>
            <option value="1">1st Semester</option>
            <option value="2">2nd Semester</option>
        </select>
    </div>

    <button type="submit" class="btn btn-primary">Add Subject</button>
</form>

<table class="table">
    <thead>
        <tr>
            <th>Code</th>
            <th>Name</th>
            <th>Year Level</th>
            <th>Semester</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($subjects as $subject): ?>
        <tr>
            <td><?= htmlspecialchars($subject['code']) ?></td>
            <td><?= htmlspecialchars($subject['name']) ?></td>
            <td><?= $subject['year_level'] ?></td>
            <td><?= $subject['semester'] === '1' ? '1st' : '2nd' ?></td>
            <td>
                <form method="POST" action="/dean/subjects/<?= $subject['id'] ?>/delete" style="display:inline">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Delete this subject?')">Delete</button>
                </form>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?php
$content = ob_get_clean();
$pageTitle = 'Subjects';
include __DIR__ . '/../../layouts/dashboard.php';
?>
