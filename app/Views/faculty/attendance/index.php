<?php
$pageTitle = 'Attendance Management';
$subtitle = 'Record daily student attendance, monitor absences, and review historical class logs.';
$headerActions = '<a href="' . url('/faculty/students') . '" class="btn btn-outline-secondary d-inline-flex align-items-center gap-2"><i class="bi bi-people"></i> Student rosters</a>';
ob_start();

$hasSubjects = !empty($assignedSubjects);
$hasStudents = !empty($students);
?>

<!-- Filter & Context Bar -->
<div class="card shadow-sm border-0 mb-4" style="border: 1px solid #e2e8f0 !important; border-radius: 8px;">
    <div class="card-body py-3 px-4">
        <form method="GET" action="<?= url('/faculty/attendance') ?>" class="row g-3 align-items-center" id="attendanceFilterForm">
            <div class="col-md-2">
                <label for="semester" class="form-label mb-1 fw-semibold small text-muted">Semester</label>
                <select id="semester" name="semester" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="1" <?= ($selectedSemester ?? '1') === '1' ? 'selected' : '' ?>>1st Semester</option>
                    <option value="2" <?= ($selectedSemester ?? '1') === '2' ? 'selected' : '' ?>>2nd Semester</option>
                </select>
            </div>

            <div class="col-md-3">
                <label for="subject_id" class="form-label mb-1 fw-semibold small text-muted">Subject</label>
                <select id="subject_id" name="subject_id" class="form-select form-select-sm" onchange="this.form.submit()">
                    <?php if (empty($assignedSubjects)): ?>
                        <option value="">No subjects assigned</option>
                    <?php else: ?>
                        <?php foreach ($assignedSubjects as $subj): ?>
                            <option value="<?= $subj['id'] ?>" <?= (int)$subj['id'] === (int)$subjectId ? 'selected' : '' ?>>
                                <?= htmlspecialchars($subj['code'] . ' - ' . $subj['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>

            <div class="col-md-2">
                <label for="set_id" class="form-label mb-1 fw-semibold small text-muted">Set / Section</label>
                <select id="set_id" name="set_id" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">All sections</option>
                    <?php foreach ($sets as $set): ?>
                        <option value="<?= $set['id'] ?>" <?= ((int)($selectedSet ?? 0) === (int)$set['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($set['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-3">
                <label for="date" class="form-label mb-1 fw-semibold small text-muted">Session date</label>
                <div class="input-group input-group-sm">
                    <input type="date" id="date" name="date" class="form-control font-monospace" value="<?= htmlspecialchars($date) ?>" onchange="this.form.submit()">
                    <button type="button" class="btn btn-outline-secondary" onclick="setTodayDate()" title="Set to today">
                        Today
                    </button>
                </div>
            </div>

            <div class="col-md-2 text-md-end pt-md-3">
                <?php if ($hasStudents): ?>
                    <button type="button" class="btn btn-sm btn-outline-success d-inline-flex align-items-center gap-1.5 w-100 justify-content-center" onclick="markAllPresent()">
                        <i class="bi bi-check2-all"></i> Mark All Present
                    </button>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>

<?php if (!$hasSubjects): ?>
    <?php
    $icon = 'bi-journal-x';
    $iconColor = 'amber';
    $title = 'No subjects assigned';
    $message = 'You have no assigned subjects for ' . (($selectedSemester ?? '1') === '2' ? '2nd Semester' : '1st Semester') . '. Contact your Dean or Administrator.';
    $card = true;
    include __DIR__ . '/../../components/empty-state.php';
    ?>
<?php elseif (!$hasStudents): ?>
    <?php
    $icon = 'bi-people';
    $iconColor = 'blue';
    $title = 'No students enrolled';
    $message = 'There are currently no students registered for this class. Enroll students via Student Rosters.';
    $card = true;
    include __DIR__ . '/../../components/empty-state.php';
    ?>
<?php else: ?>

    <!-- Summary Stats Bar -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="attendance-stat-card">
                <div>
                    <span class="text-muted small d-block fw-medium">Enrolled Students</span>
                    <span class="fs-4 fw-bold mb-0 text-dark tabular-nums"><?= count($students) ?></span>
                </div>
                <div class="attendance-stat-icon bg-light text-secondary">
                    <i class="bi bi-people"></i>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="attendance-stat-card">
                <div>
                    <span class="text-muted small d-block fw-medium">Present Today</span>
                    <span class="fs-4 fw-bold mb-0 text-success tabular-nums" id="counterPresent"><?= $presentCount ?></span>
                </div>
                <div class="attendance-stat-icon bg-success-subtle text-success">
                    <i class="bi bi-person-check-fill"></i>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="attendance-stat-card">
                <div>
                    <span class="text-muted small d-block fw-medium">Absent</span>
                    <span class="fs-4 fw-bold mb-0 text-danger tabular-nums" id="counterAbsent"><?= $absentCount ?></span>
                </div>
                <div class="attendance-stat-icon bg-danger-subtle text-danger">
                    <i class="bi bi-person-x-fill"></i>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="attendance-stat-card">
                <div>
                    <span class="text-muted small d-block fw-medium">Late / Excused</span>
                    <span class="fs-4 fw-bold mb-0 text-warning tabular-nums" id="counterExcused"><?= ($excusedCount + $lateCount) ?></span>
                </div>
                <div class="attendance-stat-icon bg-warning-subtle text-warning">
                    <i class="bi bi-clock-history"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Attendance Form -->
    <form method="POST" action="<?= url('/faculty/attendance/save') ?>" id="attendanceSaveForm">
        <?= csrf_field() ?>
        <input type="hidden" name="subject_id" value="<?= $subjectId ?>">
        <input type="hidden" name="academic_term_id" value="<?= htmlspecialchars((string)($academicTerm['id'] ?? 1)) ?>">
        <input type="hidden" name="semester" value="<?= htmlspecialchars((string)($selectedSemester ?? '1')) ?>">
        <input type="hidden" name="set_id" value="<?= htmlspecialchars((string)($selectedSet ?? '')) ?>">
        <input type="hidden" name="attendance_date" value="<?= htmlspecialchars($date) ?>">

        <div class="card shadow-sm border-0 mb-4" style="border: 1px solid #e2e8f0 !important; border-radius: 8px; overflow: hidden;">
            <div class="card-header bg-white py-3 border-bottom d-flex flex-wrap justify-content-between align-items-center gap-2">
                <div class="d-flex align-items-center gap-2">
                    <h3 class="h6 mb-0 fw-semibold text-dark">
                        Class Attendance &mdash; <?= date('l, F j, Y', strtotime($date)) ?>
                    </h3>
                    <span class="badge bg-light text-muted border font-monospace"><?= htmlspecialchars($currentSubject['code'] ?? '') ?></span>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <button type="submit" class="btn btn-primary d-inline-flex align-items-center gap-1.5 shadow-sm" id="btnSaveAttendanceTop">
                        <i class="bi bi-cloud-arrow-up-fill"></i> Save Attendance
                    </button>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table align-middle mb-0" id="attendanceTable">
                    <thead class="bg-light border-bottom">
                        <tr>
                            <th class="py-2.5 px-4 text-secondary text-uppercase fw-semibold" style="width: 140px; font-size: 11px;">Student ID</th>
                            <th class="py-2.5 px-4 text-secondary text-uppercase fw-semibold" style="font-size: 11px;">Student name</th>
                            <th class="py-2.5 px-3 text-secondary text-uppercase fw-semibold text-center" style="width: 100px; font-size: 11px;">Section</th>
                            <th class="py-2.5 px-3 text-secondary text-uppercase fw-semibold text-center" style="width: 220px; font-size: 11px;">Status</th>
                            <th class="py-2.5 px-3 text-secondary text-uppercase fw-semibold text-center" style="width: 180px; font-size: 11px;">Accumulated Absences</th>
                            <th class="py-2.5 px-4 text-secondary text-uppercase fw-semibold" style="font-size: 11px;">Remarks / Note</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        <?php foreach ($students as $student): ?>
                        <?php 
                            $stId = (int)$student['id'];
                            $rec = $attendanceMap[$stId] ?? null;
                            $currentStatus = $rec['status'] ?? 'Present';
                            $remarks = $rec['remarks'] ?? '';

                            // Eager loaded from single batch query in controller (prevents N+1 in view loop)
                            $studentAbs = $studentAbsenceMap[$stId] ?? ['total_absences' => 0, 'has_warning' => false];
                            $totalAbs = (int)$studentAbs['total_absences'];
                            $hasWarning = (bool)$studentAbs['has_warning'];
                            $statusClass = 'status-' . strtolower($currentStatus);
                        ?>
                        <tr class="attendance-row <?= $statusClass ?>" id="row_<?= $stId ?>">
                            <td class="px-4 fw-semibold font-monospace small text-dark">
                                <?= !empty($student['student_number']) ? htmlspecialchars($student['student_number']) : 'No ID' ?>
                            </td>
                            <td class="px-4">
                                <div class="fw-semibold text-dark">
                                    <?= htmlspecialchars($student['first_name'] . ' ' . $student['last_name']) ?>
                                </div>
                                <div class="text-muted font-monospace" style="font-size: 11px;">
                                    <?= htmlspecialchars($student['email']) ?>
                                </div>
                            </td>
                            <td class="px-3 text-center small">
                                <?php if (!empty($student['set_name'])): ?>
                                    <span class="badge bg-light text-secondary border font-monospace"><?= htmlspecialchars($student['set_name']) ?></span>
                                <?php else: ?>
                                    <span class="text-muted">—</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-3 text-center">
                                <!-- Tactile Segmented Radio Control -->
                                <div class="attendance-segmented" role="radiogroup" aria-label="Attendance status for <?= htmlspecialchars($student['first_name']) ?>">
                                    <input type="radio" 
                                           class="btn-check att-radio" 
                                           name="attendance[<?= $stId ?>]" 
                                           id="att_p_<?= $stId ?>" 
                                           value="Present" 
                                           <?= $currentStatus === 'Present' ? 'checked' : '' ?> 
                                           onchange="onStatusChange(<?= $stId ?>, 'Present')">
                                    <label class="btn-segment" for="att_p_<?= $stId ?>" title="Present (P)">
                                        <i class="bi bi-check-lg"></i> P
                                    </label>

                                    <input type="radio" 
                                           class="btn-check att-radio" 
                                           name="attendance[<?= $stId ?>]" 
                                           id="att_l_<?= $stId ?>" 
                                           value="Late" 
                                           <?= $currentStatus === 'Late' ? 'checked' : '' ?> 
                                           onchange="onStatusChange(<?= $stId ?>, 'Late')">
                                    <label class="btn-segment" for="att_l_<?= $stId ?>" title="Late (L)">
                                        <i class="bi bi-clock"></i> L
                                    </label>

                                    <input type="radio" 
                                           class="btn-check att-radio" 
                                           name="attendance[<?= $stId ?>]" 
                                           id="att_e_<?= $stId ?>" 
                                           value="Excused" 
                                           <?= $currentStatus === 'Excused' ? 'checked' : '' ?> 
                                           onchange="onStatusChange(<?= $stId ?>, 'Excused')">
                                    <label class="btn-segment" for="att_e_<?= $stId ?>" title="Excused (E)">
                                        <i class="bi bi-info-circle"></i> E
                                    </label>

                                    <input type="radio" 
                                           class="btn-check att-radio" 
                                           name="attendance[<?= $stId ?>]" 
                                           id="att_a_<?= $stId ?>" 
                                           value="Absent" 
                                           <?= $currentStatus === 'Absent' ? 'checked' : '' ?> 
                                           onchange="onStatusChange(<?= $stId ?>, 'Absent')">
                                    <label class="btn-segment" for="att_a_<?= $stId ?>" title="Absent (A)">
                                        <i class="bi bi-x-lg"></i> A
                                    </label>
                                </div>
                            </td>
                            <td class="px-3 text-center small">
                                <?php if ($hasWarning): ?>
                                    <button type="button" 
                                            class="btn btn-sm btn-danger py-0 px-2 rounded-pill d-inline-flex align-items-center gap-1 shadow-sm"
                                            onclick="openStudentHistory(<?= $stId ?>, '<?= htmlspecialchars(addslashes($student['first_name'] . ' ' . $student['last_name'])) ?>')"
                                            title="View absence history">
                                        <i class="bi bi-exclamation-triangle-fill"></i> <?= $totalAbs ?> Absences (5+ Warning)
                                    </button>
                                <?php elseif ($totalAbs > 0): ?>
                                    <button type="button" 
                                            class="btn btn-sm btn-outline-warning py-0 px-2 rounded-pill font-monospace"
                                            onclick="openStudentHistory(<?= $stId ?>, '<?= htmlspecialchars(addslashes($student['first_name'] . ' ' . $student['last_name'])) ?>')"
                                            title="View absence history">
                                        <?= $totalAbs ?> absences
                                    </button>
                                <?php else: ?>
                                    <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill py-1 px-2.5">
                                        <i class="bi bi-check-circle me-1"></i> Perfect
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="px-4">
                                <input type="text" 
                                       name="remarks[<?= $stId ?>]" 
                                       class="form-control form-control-sm" 
                                       placeholder="Optional note / reason..." 
                                       value="<?= htmlspecialchars($remarks) ?>"
                                       style="max-width: 280px; border-radius: 6px;">
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="card-footer bg-white py-3 border-top d-flex justify-content-between align-items-center">
                <span class="text-muted small">
                    <i class="bi bi-shield-check text-success me-1"></i> Changes are recorded in official student course logs upon saving.
                </span>
                <button type="submit" class="btn btn-primary d-inline-flex align-items-center gap-1.5 shadow-sm" id="btnSaveAttendanceBottom">
                    <i class="bi bi-cloud-arrow-up-fill"></i> Save Attendance
                </button>
            </div>
        </div>
    </form>

    <!-- Historical Attendance Session Logs -->
    <div class="card shadow-sm border-0 mb-4" style="border: 1px solid #e2e8f0 !important; border-radius: 8px;">
        <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
            <h3 class="h6 mb-0 fw-semibold text-dark d-flex align-items-center gap-2">
                <i class="bi bi-clock-history text-primary"></i> Recent Class Session Logs
            </h3>
            <span class="badge bg-light text-secondary border font-monospace"><?= count($historyLogs) ?> sessions</span>
        </div>
        <div class="card-body p-0">
            <?php if (empty($historyLogs)): ?>
                <div class="py-4 text-center text-muted small">
                    No past attendance sessions recorded yet for this subject.
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" style="font-size: 13px;">
                        <thead class="bg-light border-bottom">
                            <tr>
                                <th class="py-2.5 px-4 text-secondary text-uppercase fw-semibold" style="font-size: 11px;">Session Date</th>
                                <th class="py-2.5 px-3 text-secondary text-uppercase fw-semibold text-center" style="font-size: 11px;">Roster</th>
                                <th class="py-2.5 px-3 text-secondary text-uppercase fw-semibold text-center" style="font-size: 11px;">Present</th>
                                <th class="py-2.5 px-3 text-secondary text-uppercase fw-semibold text-center" style="font-size: 11px;">Absent</th>
                                <th class="py-2.5 px-3 text-secondary text-uppercase fw-semibold text-center" style="font-size: 11px;">Late / Excused</th>
                                <th class="py-2.5 px-4 text-secondary text-uppercase fw-semibold text-end" style="font-size: 11px;">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            <?php foreach ($historyLogs as $log): ?>
                            <tr>
                                <td class="px-4 fw-semibold font-monospace text-dark">
                                    <i class="bi bi-calendar-event me-1.5 text-muted"></i>
                                    <?= date('M j, Y (D)', strtotime($log['attendance_date'])) ?>
                                </td>
                                <td class="px-3 text-center font-monospace">
                                    <?= $log['total_students'] ?>
                                </td>
                                <td class="px-3 text-center">
                                    <span class="badge bg-success-subtle text-success border border-success-subtle font-monospace">
                                        <?= $log['present_count'] ?>
                                    </span>
                                </td>
                                <td class="px-3 text-center">
                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle font-monospace">
                                        <?= $log['absent_count'] ?>
                                    </span>
                                </td>
                                <td class="px-3 text-center">
                                    <span class="badge bg-warning-subtle text-warning border border-warning-subtle font-monospace">
                                        <?= ((int)$log['excused_count'] + (int)$log['late_count']) ?>
                                    </span>
                                </td>
                                <td class="px-4 text-end">
                                    <a href="<?= url('/faculty/attendance?subject_id=' . $subjectId . '&semester=' . $selectedSemester . '&date=' . $log['attendance_date']) ?>" 
                                       class="btn btn-sm btn-outline-secondary py-1 px-2.5 d-inline-flex align-items-center gap-1">
                                        <i class="bi bi-pencil-square"></i> Open Session
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

<?php endif; ?>

<!-- Student Absence History Audit Modal -->
<div class="modal fade" id="studentAbsenceModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 12px; overflow: hidden;">
            <div class="modal-header py-3 px-4 border-bottom bg-light">
                <div>
                    <h5 class="modal-title h6 fw-bold mb-0 text-dark" id="historyModalStudentName">Student Absence Audit</h5>
                    <div class="small text-muted" id="historyModalSubtitle">Official attendance record</div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4" id="historyModalBody">
                <div class="text-center py-4 text-muted">
                    <span class="spinner-border spinner-border-sm me-2"></span> Loading audit logs...
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function setTodayDate() {
    const today = new Date().toISOString().slice(0, 10);
    const dateInput = document.getElementById('date');
    if (dateInput) {
        dateInput.value = today;
        document.getElementById('attendanceFilterForm').submit();
    }
}

function onStatusChange(stId, status) {
    const row = document.getElementById('row_' + stId);
    if (row) {
        row.classList.remove('status-present', 'status-absent', 'status-late', 'status-excused');
        row.classList.add('status-' + status.toLowerCase());
    }
    recalculateCounters();
}

function markAllPresent() {
    document.querySelectorAll('.att-radio[value="Present"]').forEach(radio => {
        radio.checked = true;
        const stId = radio.name.match(/\d+/)[0];
        const row = document.getElementById('row_' + stId);
        if (row) {
            row.classList.remove('status-present', 'status-absent', 'status-late', 'status-excused');
            row.classList.add('status-present');
        }
    });
    recalculateCounters();
}

function recalculateCounters() {
    let present = 0, absent = 0, excused = 0;
    document.querySelectorAll('.att-radio:checked').forEach(radio => {
        const val = radio.value;
        if (val === 'Present') present++;
        else if (val === 'Absent') absent++;
        else if (val === 'Excused' || val === 'Late') excused++;
    });

    const cp = document.getElementById('counterPresent');
    const ca = document.getElementById('counterAbsent');
    const ce = document.getElementById('counterExcused');

    if (cp) cp.textContent = present;
    if (ca) ca.textContent = absent;
    if (ce) ce.textContent = excused;
}

// Student History Modal
function openStudentHistory(studentId, studentName) {
    const modal = new bootstrap.Modal(document.getElementById('studentAbsenceModal'));
    document.getElementById('historyModalStudentName').textContent = studentName;
    document.getElementById('historyModalSubtitle').textContent = 'Absence record for this subject';
    const body = document.getElementById('historyModalBody');
    body.innerHTML = '<div class="text-center py-4 text-muted"><span class="spinner-border spinner-border-sm me-2"></span> Loading audit logs...</div>';
    modal.show();

    fetch('<?= url('/faculty/attendance/student-history') ?>?student_id=' + studentId + '&subject_id=<?= $subjectId ?>&term_id=<?= (int)($academicTerm['id'] ?? 1) ?>')
        .then(res => res.json())
        .then(data => {
            if (data && data.success && data.summary) {
                const s = data.summary;
                let html = `
                    <div class="row g-2 mb-3 text-center">
                        <div class="col-4">
                            <div class="p-2 border rounded bg-light">
                                <div class="text-muted" style="font-size: 10px;">PRESENT</div>
                                <div class="fs-5 fw-bold text-success">${s.present_count}</div>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="p-2 border rounded bg-light">
                                <div class="text-muted" style="font-size: 10px;">ABSENT</div>
                                <div class="fs-5 fw-bold text-danger">${s.absent_count}</div>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="p-2 border rounded bg-light">
                                <div class="text-muted" style="font-size: 10px;">LATE/EXCUSED</div>
                                <div class="fs-5 fw-bold text-warning">${s.late_count + s.excused_count}</div>
                            </div>
                        </div>
                    </div>
                `;

                if (s.has_warning) {
                    html += `<div class="alert alert-danger py-2 small mb-3"><i class="bi bi-exclamation-octagon-fill me-1"></i> <strong>Attendance Warning:</strong> Student has reached or exceeded 5 absences.</div>`;
                }

                if (!s.absence_history || s.absence_history.length === 0) {
                    html += `<p class="text-center text-muted small my-3">No recorded absences for this subject.</p>`;
                } else {
                    html += `<h6 class="fw-semibold small text-uppercase text-muted mb-2">Absence Dates Log</h6><ul class="list-group list-group-flush border rounded small">`;
                    s.absence_history.forEach(item => {
                        html += `
                            <li class="list-group-item d-flex justify-content-between align-items-center py-2 px-3">
                                <span class="font-monospace text-dark"><i class="bi bi-calendar-x text-danger me-1.5"></i>${item.date}</span>
                                <span class="badge ${item.status === 'Absent' ? 'bg-danger-subtle text-danger' : 'bg-info-subtle text-info'}">${item.status}</span>
                            </li>
                        `;
                    });
                    html += `</ul>`;
                }
                body.innerHTML = html;
            } else {
                body.innerHTML = `<div class="text-center text-danger small py-3">Failed to load student history.</div>`;
            }
        })
        .catch(() => {
            body.innerHTML = `<div class="text-center text-danger small py-3">An error occurred while loading audit record.</div>`;
        });
}

// Button loading state on save
document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('attendanceSaveForm');
    const topBtn = document.getElementById('btnSaveAttendanceTop');
    const btmBtn = document.getElementById('btnSaveAttendanceBottom');

    if (form) {
        form.addEventListener('submit', () => {
            if (topBtn) {
                topBtn.disabled = true;
                topBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status"></span> Saving...';
            }
            if (btmBtn) {
                btmBtn.disabled = true;
                btmBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status"></span> Saving...';
            }
        });
    }
});
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../layouts/dashboard.php';
?>
