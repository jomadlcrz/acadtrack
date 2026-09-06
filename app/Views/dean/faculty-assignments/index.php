<?php
$pageTitle = 'Faculty Assignments';
ob_start();
?>

<div class="page-header">
    <h2>Faculty Subject Assignments</h2>
</div>

<form method="POST" action="/dean/faculty-assignments/assign" class="form-inline">
    <?= csrf_field() ?>

    <div class="form-group">
        <label for="faculty_id">Faculty</label>
        <select id="faculty_id" name="faculty_id" required>
            <option value="">Select Faculty</option>
            <?php foreach ($faculty as $f): ?>
                <option value="<?= $f['id'] ?>"><?= htmlspecialchars($f['first_name'] . ' ' . $f['last_name']) ?></option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="form-group">
        <label for="subject_id">Subject</label>
        <select id="subject_id" name="subject_id" required>
            <option value="">Select Subject</option>
            <?php foreach ($subjects as $s): ?>
                <option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['code'] . ' - ' . $s['name']) ?></option>
            <?php endforeach; ?>
        </select>
    </div>

    <button type="submit" class="btn btn-primary">Assign</button>
</form>

<table class="table">
    <thead>
        <tr>
            <th>Subject Code</th>
            <th>Subject Name</th>
            <th>Assigned Faculty</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($subjects as $subject): ?>
        <tr>
            <td><?= htmlspecialchars($subject['code']) ?></td>
            <td><?= htmlspecialchars($subject['name']) ?></td>
            <td><?= $subject['assigned_faculty_count'] ?? 0 ?></td>
            <td>
                <form method="POST" action="/dean/faculty-assignments/remove" style="display:inline">
                    <?= csrf_field() ?>
                    <input type="hidden" name="subject_id" value="<?= $subject['id'] ?>">
                    <button type="submit" class="btn btn-sm btn-danger">Remove Assignment</button>
                </form>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?php
$content = ob_get_clean();
$pageTitle = 'Faculty Assignments';
include __DIR__ . '/../../layouts/dashboard.php';
?>
