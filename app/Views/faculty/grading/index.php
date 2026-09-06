<?php
$pageTitle = 'Grading';
ob_start();
?>

<div class="page-header">
    <h2>Enter Grades</h2>
</div>

<form method="GET" action="/faculty/grading" class="form-inline">
    <div class="form-group">
        <label for="subject_id">Subject</label>
        <input type="hidden" name="subject_id" value="<?= $subjectId ?>">
    </div>
    <div class="form-group">
        <label for="period_id">Grading Period</label>
        <select id="period_id" name="period_id" onchange="this.form.submit()">
            <?php foreach ($periods as $period): ?>
                <option value="<?= $period['id'] ?>" <?= $period['id'] == $periodId ? 'selected' : '' ?>>
                    <?= htmlspecialchars($period['name']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
</form>

<form method="POST" action="/faculty/grading/save" class="form">
    <?= csrf_field() ?>
    <input type="hidden" name="subject_id" value="<?= $subjectId ?>">
    <input type="hidden" name="grading_period_id" value="<?= $periodId ?>">

    <?php if ($gradingSheet && $gradingSheet['status'] === 'SUBMITTED'): ?>
        <div class="alert alert-info">This grading sheet has been submitted and is under review.</div>
    <?php elseif ($gradingSheet && $gradingSheet['status'] === 'RETURNED'): ?>
        <div class="alert alert-warning">This grading sheet was returned. Please revise and resubmit.</div>
    <?php endif; ?>

    <?php if (empty($students)): ?>
        <div class="empty-state">
            <p>No students enrolled in this subject.</p>
        </div>
    <?php else: ?>
    <table class="table grading-table">
        <thead>
            <tr>
                <th>Student Number</th>
                <th>Name</th>
                <th>Grade</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($students as $student): ?>
            <tr>
                <td><?= htmlspecialchars($student['student_number']) ?></td>
                <td><?= htmlspecialchars($student['first_name'] . ' ' . $student['last_name']) ?></td>
                <td>
                    <input type="number" 
                           name="grades[<?= $student['id'] ?>]" 
                           value="<?= htmlspecialchars($grades[$student['id']] ?? '') ?>"
                           min="0" max="100" step="0.01"
                           class="form-control"
                           <?= in_array($gradingSheet['status'] ?? '', ['SUBMITTED', 'APPROVED']) ? 'disabled' : '' ?>>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div class="form-actions">
        <button type="submit" class="btn btn-primary" <?= in_array($gradingSheet['status'] ?? '', ['SUBMITTED', 'APPROVED']) ? 'disabled' : '' ?>>
            Save Grades
        </button>
    </div>
    <?php endif; ?>
</form>

<?php if (!empty($students) && $gradingSheet && ($gradingSheet['status'] === 'DRAFT' || $gradingSheet['status'] === 'RETURNED')): ?>
    <form method="POST" action="/faculty/grading/submit" style="margin-top: 15px;">
        <?= csrf_field() ?>
        <input type="hidden" name="grading_sheet_id" value="<?= $gradingSheet['id'] ?>">
        <button type="submit" class="btn btn-warning">Submit for Review</button>
    </form>
<?php endif; ?>

<?php
$content = ob_get_clean();
$pageTitle = 'Grading';
include __DIR__ . '/../../layouts/dashboard.php';
?>
