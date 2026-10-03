<?php
/**
 * Modal partial for editing a user account inline.
 *
 * Expects:
 *   - $user (array or object with user attributes)
 *   - $departments (optional array of active departments)
 *   - $sets (optional array of active sets for term)
 */

$uid = (int) ($user['id'] ?? $user->id ?? 0);
$userRole = (string) ($user['role'] ?? ($user->role ?? 'Student'));
$userEmail = (string) ($user['email'] ?? ($user->email ?? ''));
$userStatus = (string) ($user['status'] ?? ($user->status ?? 'active'));
$firstName = (string) ($user['first_name'] ?? ($user->first_name ?? ''));
$lastName = (string) ($user['last_name'] ?? ($user->last_name ?? ''));

$studentNumber = (string) ($user['student_number'] ?? $user['student_detail']['student_number'] ?? ($user->student_number ?? ($user->studentDetail->student_number ?? '')));
$studentStatus = (string) ($user['student_status'] ?? $user['student']['status'] ?? $user['student_detail']['status'] ?? ($user->student->status ?? ($user->studentDetail->status ?? 'Regular')));
$studentStatus = in_array($studentStatus, ['Regular', 'Irregular'], true) ? $studentStatus : 'Regular';
$yearLevel = (int) ($user['year_level'] ?? $user['student']['year_level'] ?? $user['student_detail']['year_level'] ?? ($user->student->year_level ?? ($user->studentDetail->year_level ?? 1)));
$yearLevel = ($yearLevel >= 1 && $yearLevel <= 4) ? $yearLevel : 1;
$selectedSetId = (int) ($user['set_id'] ?? $user['student']['set_id'] ?? $user['student_detail']['set_id'] ?? ($user->student->set_id ?? ($user->studentDetail->set_id ?? 0)));

$departmentId = (int) ($user['department_id'] ?? $user['faculty']['department_id'] ?? ($user->faculty->department_id ?? 0));
?>

<div class="modal fade" id="editUserModal<?= $uid ?>" tabindex="-1" aria-labelledby="editUserModalLabel<?= $uid ?>" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content" style="border: 1px solid #cbd5e1; border-radius: 6px; box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1);">
            <div class="modal-header bg-white py-3 px-4 border-bottom" style="border-top-left-radius: 6px; border-top-right-radius: 6px;">
                <div class="d-flex align-items-center gap-2">
                    <div class="d-flex align-items-center justify-content-center rounded-2 text-primary" style="width: 32px; height: 32px; background-color: #eff6ff;">
                        <i class="bi bi-person-gear fs-6"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-semibold text-dark fs-6 mb-0" id="editUserModalLabel<?= $uid ?>">
                            Edit <?= htmlspecialchars($userRole) ?> Account
                        </h5>
                        <div class="text-muted text-xs d-flex align-items-center gap-1.5 mt-0.5" style="font-size: 11.5px;">
                            <span class="badge badge-<?= strtolower($userRole) ?> py-0.5 px-2"><?= htmlspecialchars($userRole) ?></span>
                            <span>&bull;</span>
                            <span>User ID: <strong class="text-dark">#<?= $uid ?></strong></span>
                        </div>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form method="POST" action="<?= url('/admin/users/' . $uid) ?>" autocomplete="off" class="user-edit-modal-form" data-modal-id="<?= $uid ?>">
                <?= csrf_field() ?>

                <div class="modal-body p-4">
                    <!-- Personal details section -->
                    <div class="mb-4">
                        <div class="d-flex align-items-center gap-2 mb-3 pb-1 border-bottom">
                            <i class="bi bi-person text-secondary" style="font-size: 14px;"></i>
                            <span class="fw-semibold text-dark small text-uppercase" style="font-size: 11px; letter-spacing: 0.5px;">Identity & Account</span>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="first_name_<?= $uid ?>" class="form-label fw-medium text-dark small mb-1">
                                    First name <span class="text-danger">*</span>
                                </label>
                                <input type="text" 
                                       class="form-control form-control-sm" 
                                       id="first_name_<?= $uid ?>" 
                                       name="first_name" 
                                       value="<?= htmlspecialchars($firstName) ?>" 
                                       required>
                            </div>
                            <div class="col-md-6">
                                <label for="last_name_<?= $uid ?>" class="form-label fw-medium text-dark small mb-1">
                                    Last name <span class="text-danger">*</span>
                                </label>
                                <input type="text" 
                                       class="form-control form-control-sm" 
                                       id="last_name_<?= $uid ?>" 
                                       name="last_name" 
                                       value="<?= htmlspecialchars($lastName) ?>" 
                                       required>
                            </div>

                            <div class="col-md-7">
                                <label for="email_<?= $uid ?>" class="form-label fw-medium text-dark small mb-1">
                                    Email address
                                </label>
                                <input type="email" 
                                       class="form-control form-control-sm bg-light" 
                                       id="email_<?= $uid ?>" 
                                       value="<?= htmlspecialchars($userEmail) ?>" 
                                       readonly 
                                       disabled>
                                <div class="form-text text-muted" style="font-size: 11px;">
                                    Primary login identifier (locked).
                                </div>
                            </div>

                            <div class="col-md-5">
                                <label for="status_<?= $uid ?>" class="form-label fw-medium text-dark small mb-1">
                                    Account status <span class="text-danger">*</span>
                                </label>
                                <select class="form-select form-select-sm" id="status_<?= $uid ?>" name="status">
                                    <option value="active" <?= $userStatus === 'active' ? 'selected' : '' ?>>Active (Enabled)</option>
                                    <option value="inactive" <?= $userStatus === 'inactive' ? 'selected' : '' ?>>Inactive (Suspended)</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <?php if ($userRole === 'Student'): ?>
                        <!-- Academic classification section for students -->
                        <div class="mb-2">
                            <div class="d-flex align-items-center gap-2 mb-3 pb-1 border-bottom">
                                <i class="bi bi-mortarboard text-secondary" style="font-size: 14px;"></i>
                                <span class="fw-semibold text-dark small text-uppercase" style="font-size: 11px; letter-spacing: 0.5px;">Academic Classification</span>
                            </div>

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="student_number_<?= $uid ?>" class="form-label fw-medium text-dark small mb-1">
                                        Student ID <span class="text-muted fw-normal">(Optional)</span>
                                    </label>
                                    <input type="text" 
                                           class="form-control form-control-sm font-monospace" 
                                           id="student_number_<?= $uid ?>" 
                                           name="student_number" 
                                           placeholder="e.g. 2026-0001" 
                                           value="<?= htmlspecialchars($studentNumber) ?>" 
                                           maxlength="50">
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-medium text-dark small mb-1">
                                        Enrollment standing <span class="text-danger">*</span>
                                    </label>
                                    <div class="btn-group w-100" role="group">
                                        <input type="radio" 
                                               class="btn-check status-toggle-radio" 
                                               name="student_status" 
                                               id="status_reg_<?= $uid ?>" 
                                               value="Regular" 
                                               <?= $studentStatus === 'Regular' ? 'checked' : '' ?> 
                                               data-modal="<?= $uid ?>">
                                        <label class="btn btn-outline-primary btn-sm" for="status_reg_<?= $uid ?>">
                                            <i class="bi bi-mortarboard me-1"></i> Regular
                                        </label>

                                        <input type="radio" 
                                               class="btn-check status-toggle-radio" 
                                               name="student_status" 
                                               id="status_irreg_<?= $uid ?>" 
                                               value="Irregular" 
                                               <?= $studentStatus === 'Irregular' ? 'checked' : '' ?> 
                                               data-modal="<?= $uid ?>">
                                        <label class="btn btn-outline-primary btn-sm" for="status_irreg_<?= $uid ?>">
                                            <i class="bi bi-shuffle me-1"></i> Irregular
                                        </label>
                                    </div>
                                </div>

                                <div class="col-md-6" id="yearLevelCol_<?= $uid ?>">
                                    <label for="year_level_<?= $uid ?>" class="form-label fw-medium text-dark small mb-1">
                                        Year level <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-select form-select-sm year-level-select" 
                                            id="year_level_<?= $uid ?>" 
                                            name="year_level" 
                                            data-modal="<?= $uid ?>" 
                                            required>
                                        <option value="1" <?= $yearLevel === 1 ? 'selected' : '' ?>>1st Year</option>
                                        <option value="2" <?= $yearLevel === 2 ? 'selected' : '' ?>>2nd Year</option>
                                        <option value="3" <?= $yearLevel === 3 ? 'selected' : '' ?>>3rd Year</option>
                                        <option value="4" <?= $yearLevel === 4 ? 'selected' : '' ?>>4th Year</option>
                                    </select>
                                </div>

                                <div class="col-md-6 <?= $studentStatus === 'Irregular' ? 'd-none' : '' ?>" id="setSectionCol_<?= $uid ?>">
                                    <label for="set_id_<?= $uid ?>" class="form-label fw-medium text-dark small mb-1">
                                        Assigned section <span class="text-danger" id="setRequiredStar_<?= $uid ?>">*</span>
                                    </label>
                                    <select class="form-select form-select-sm set-select" 
                                            id="set_id_<?= $uid ?>" 
                                            name="set_id" 
                                            data-modal="<?= $uid ?>" 
                                            <?= $studentStatus === 'Regular' ? 'required' : '' ?>>
                                        <option value="">Select section...</option>
                                        <?php foreach ($sets ?? [] as $st): ?>
                                            <option value="<?= $st['id'] ?>" 
                                                    data-year-level="<?= (int)($st['year_level'] ?? 1) ?>" 
                                                    <?= ($selectedSetId === (int)$st['id']) ? 'selected' : '' ?>>
                                                Section <?= htmlspecialchars($st['name']) ?> (Year <?= $st['year_level'] ?? 1 ?>)
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <div class="alert alert-warning py-1.5 px-2.5 mt-1.5 mb-0 small d-none" id="noSetsAlert_<?= $uid ?>" style="font-size: 11px;">
                                        <i class="bi bi-exclamation-triangle me-1"></i> No active sections found for Year <span id="noSetsYear_<?= $uid ?>">1</span>.
                                    </div>
                                </div>

                                <div class="col-12 <?= $studentStatus === 'Irregular' ? '' : 'd-none' ?>" id="irregularNotice_<?= $uid ?>">
                                    <div class="p-2.5 rounded-2 border text-muted small" style="background-color: #f8fafc; border-color: #e2e8f0 !important; font-size: 11.5px; line-height: 1.5;">
                                        <i class="bi bi-info-circle text-primary me-1"></i>
                                        <strong>Modular scheduling:</strong> Irregular students take custom subject loads across sections; section blocks are not assigned at account creation.
                                    </div>
                                </div>
                            </div>
                        </div>

                    <?php elseif (in_array($userRole, ['Faculty', 'Dean'], true)): ?>
                        <!-- Academic department affiliation for faculty / dean -->
                        <div class="mb-2">
                            <div class="d-flex align-items-center gap-2 mb-3 pb-1 border-bottom">
                                <i class="bi bi-building text-secondary" style="font-size: 14px;"></i>
                                <span class="fw-semibold text-dark small text-uppercase" style="font-size: 11px; letter-spacing: 0.5px;">Academic Department</span>
                            </div>

                            <div class="mb-2">
                                <label for="department_id_<?= $uid ?>" class="form-label fw-medium text-dark small mb-1">
                                    Department <span class="text-danger">*</span>
                                </label>
                                <select class="form-select form-select-sm" id="department_id_<?= $uid ?>" name="department_id" required>
                                    <option value="">Select department...</option>
                                    <?php foreach ($departments ?? [] as $dept):
                                        $deptCode = strtoupper(trim($dept['code'] ?? $dept['dept_abbrev'] ?? ''));
                                        $isSelected = ($departmentId === (int)$dept['id'])
                                                   || (empty($departmentId) && $deptCode === 'CITE');
                                    ?>
                                        <option value="<?= $dept['id'] ?>" <?= $isSelected ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($dept['name']) ?> (<?= htmlspecialchars($deptCode) ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="modal-footer py-2.5 px-4 d-flex justify-content-between align-items-center" style="background-color: #f8fafc; border-top: 1px solid #e2e8f0; border-bottom-left-radius: 6px; border-bottom-right-radius: 6px;">
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">
                        Cancel
                    </button>
                    <button type="submit" class="btn btn-primary btn-sm d-inline-flex align-items-center gap-1.5" style="background-color: #1e3a8a; border-color: #1e3a8a;">
                        <i class="bi bi-check2"></i> Save changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
