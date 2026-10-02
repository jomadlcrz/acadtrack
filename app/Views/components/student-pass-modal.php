<!-- Digital Student Pass Modal (Official Institutional Credential) -->
<div class="modal fade" id="digitalStudentPassModal" tabindex="-1" aria-labelledby="digitalStudentPassModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 12px; overflow: hidden; background: #ffffff;">
            <div class="modal-header border-bottom py-3 px-4 d-flex justify-content-between align-items-center">
                <h5 class="modal-title h6 fw-semibold text-dark mb-0">Digital Student Pass</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0">
                <!-- Pass Card Render Target -->
                <div id="studentPassCard" class="pass-certificate-wrapper" style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; border: none; border-radius: 0;">
                    <!-- Card Header -->
                    <div class="pass-certificate-header">
                        <div class="d-flex align-items-center gap-3">
                            <img src="<?= asset('images/cite.png') ?>" alt="College of Information Technology Seal" class="pass-emblem">
                            <div>
                                <div class="pass-institution-title">College of Information Technology</div>
                                <div class="pass-doc-title">Official Digital Student Pass</div>
                                <div class="small opacity-75 text-white" style="font-size: 11.5px;">Golden West Colleges &bull; Office of Academic Affairs</div>
                            </div>
                        </div>
                    </div>

                    <!-- Card Body -->
                    <div style="padding: 32px 36px; border-bottom: 1px solid #f1f5f9; background: #ffffff;">
                        <div class="row g-4 align-items-center">
                            <!-- QR Code Block -->
                            <div class="col-auto text-center" style="width: 150px;">
                                <div id="passQrContainer" class="p-2 border rounded-3 bg-white d-flex align-items-center justify-content-center mx-auto mb-2" style="width: 130px; height: 130px; border-color: #cbd5e1 !important;">
                                    <div class="text-muted small">Generating...</div>
                                </div>
                                <div class="font-monospace fw-bold text-dark small py-1 px-2 bg-light rounded border text-truncate" id="passStudentNumber" style="font-size: 11px;">
                                    STU-0000
                                </div>
                                <div class="text-muted mt-1" style="font-size: 11px;">
                                    <i class="bi bi-qr-code"></i> Scan to verify
                                </div>
                            </div>

                            <!-- Student Info Block -->
                            <div class="col ps-md-3">
                                <h3 class="h4 fw-bold text-dark mb-1" id="passStudentName" style="letter-spacing: -0.01em;">Student Name</h3>
                                <div class="text-muted small mb-4" id="passProgramName">Degree Program</div>

                                <div class="row g-3" style="font-size: 13px;">
                                    <div class="col-6 col-md-4">
                                        <span class="text-muted d-block text-uppercase fw-semibold" style="font-size: 10.5px; letter-spacing: 0.04em; margin-bottom: 3px;">Academic Status</span>
                                        <span class="fw-semibold text-dark" id="passStudentStatus">Regular</span>
                                    </div>
                                    <div class="col-6 col-md-4">
                                        <span class="text-muted d-block text-uppercase fw-semibold" style="font-size: 10.5px; letter-spacing: 0.04em; margin-bottom: 3px;">Year Level</span>
                                        <span class="fw-semibold text-dark" id="passYearLevel">First Year</span>
                                    </div>
                                    <div class="col-6 col-md-4">
                                        <span class="text-muted d-block text-uppercase fw-semibold" style="font-size: 10.5px; letter-spacing: 0.04em; margin-bottom: 3px;">Class Section</span>
                                        <span class="fw-semibold text-dark" id="passSetName">Set A</span>
                                    </div>
                                    <div class="col-6 col-md-4">
                                        <span class="text-muted d-block text-uppercase fw-semibold" style="font-size: 10.5px; letter-spacing: 0.04em; margin-bottom: 3px;">Course Code</span>
                                        <span class="fw-semibold text-dark font-monospace" id="passCourseCode">SUBJ-101</span>
                                    </div>
                                    <div class="col-6 col-md-4">
                                        <span class="text-muted d-block text-uppercase fw-semibold" style="font-size: 10.5px; letter-spacing: 0.04em; margin-bottom: 3px;">Issued Date</span>
                                        <span class="fw-semibold text-dark" id="passIssuedDate">September 16, 2026</span>
                                    </div>
                                    <div class="col-6 col-md-4">
                                        <span class="text-muted d-block text-uppercase fw-semibold" style="font-size: 10.5px; letter-spacing: 0.04em; margin-bottom: 3px;">Verification Code</span>
                                        <span class="fw-semibold text-dark font-monospace" id="passVerificationCode">VERIFIED</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Academic Performance Slip -->
                    <div style="background: #ffffff; padding: 32px 36px; border-bottom: 1px solid #f1f5f9;">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <span class="text-uppercase fw-bold text-secondary" style="font-size: 11px; letter-spacing: 0.06em;">
                                Academic Period Evaluation
                            </span>
                        </div>

                        <!-- 4 Periods Grid -->
                        <div class="row g-3 mb-4 text-center" id="passPeriodsGrid">
                            <div class="col-3">
                                <div class="transcript-tile">
                                    <div class="tile-label">Prelim</div>
                                    <div class="tile-value" id="passPrelimScore">-</div>
                                </div>
                            </div>
                            <div class="col-3">
                                <div class="transcript-tile">
                                    <div class="tile-label">Midterm</div>
                                    <div class="tile-value" id="passMidtermScore">-</div>
                                </div>
                            </div>
                            <div class="col-3">
                                <div class="transcript-tile">
                                    <div class="tile-label">Semi-Final</div>
                                    <div class="tile-value" id="passSemifinalScore">-</div>
                                </div>
                            </div>
                            <div class="col-3">
                                <div class="transcript-tile">
                                    <div class="tile-label">Final</div>
                                    <div class="tile-value" id="passFinalScore">-</div>
                                </div>
                            </div>
                        </div>

                        <!-- Computed Final Grade Footer -->
                        <div class="d-flex align-items-center justify-content-between pt-3 border-top">
                            <div class="d-flex align-items-baseline gap-2">
                                <span class="text-secondary fw-semibold small">Final Weighted Rating:</span>
                                <span class="h3 fw-bold font-monospace mb-0 text-dark tabular-nums" id="passFinalGrade">0.00</span>
                            </div>
                            <div>
                                <span class="badge py-1.5 px-3 fs-7" id="passRemarkBadge">
                                    PASS
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Attendance Standing Block -->
                    <div style="padding: 24px 36px; background: #ffffff; border-bottom: 1px solid #f1f5f9;" id="passAttendanceBlock">
                        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                            <div class="d-flex align-items-center gap-2 small">
                                <i class="bi bi-calendar-check text-muted fs-6"></i>
                                <span class="fw-semibold text-dark">Attendance Standing:</span>
                                <span class="text-secondary font-monospace" id="passAttendanceStats">
                                    0 sessions present &bull; 0 absences
                                </span>
                            </div>
                            <div id="passWarningContainer">
                                <span class="text-secondary small fw-medium" id="passAbsenceBadge">
                                    <i class="bi bi-check2 text-success me-1"></i> Regular Standing
                                </span>
                            </div>
                        </div>
                        <div class="d-none mt-2 alert alert-danger py-2 px-3 small mb-0 rounded-2" id="passWarningNotice">
                            <i class="bi bi-exclamation-triangle-fill me-1"></i>
                            <strong>Attendance Warning:</strong> Student has accumulated 5 or more absences in this course.
                        </div>
                    </div>

                    <!-- Card Security Footer -->
                    <div class="py-3 px-4 px-md-5 bg-white text-muted d-flex justify-content-between align-items-center" style="font-size: 11px;">
                        <span>
                            <i class="bi bi-shield-lock me-1 text-secondary"></i> Validated by AcadTrack Academic Engine
                        </span>
                        <span id="passCardFooterId" class="font-monospace text-secondary">STU-0000</span>
                    </div>
                </div>
            </div>

            <!-- Modal Action Footer -->
            <div class="modal-footer py-3 px-4 bg-light border-top d-flex justify-content-between align-items-center">
                <a href="#" target="_blank" class="btn btn-outline-secondary btn-sm d-inline-flex align-items-center gap-1.5" id="passVerifyDirectLink">
                    <i class="bi bi-box-arrow-up-right"></i> Open Verification Page
                </a>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary btn-sm d-inline-flex align-items-center gap-1.5 shadow-sm" id="btnDownloadPass" onclick="downloadStudentPass()">
                        <i class="bi bi-download"></i> Download PNG Pass
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- QR Code Library & html2canvas CDN -->
<script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/html2canvas@1.4.1/dist/html2canvas.min.js"></script>

<script>
let currentPassStudentName = '';

function openStudentPassModal(data) {
    const passObj = (data && data.pass) ? data.pass : data;
    if (passObj) {
        renderStudentPass(passObj);
        const modalEl = document.getElementById('digitalStudentPassModal');
        const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
        modal.show();
    }
}

function openDigitalStudentPass(studentId, subjectId, termId) {
    const modalEl = document.getElementById('digitalStudentPassModal');
    const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
    modal.show();

    fetch('<?= url('/faculty/students/pass-data') ?>?student_id=' + studentId + '&subject_id=' + subjectId + '&term_id=' + termId)
        .then(response => response.json())
        .then(data => {
            if (data.success && data.pass) {
                renderStudentPass(data.pass);
            } else {
                alert(data.message || 'Unable to retrieve student pass data.');
                modal.hide();
            }
        })
        .catch(err => {
            console.error(err);
            alert('Failed to load student pass record.');
            modal.hide();
        });
}

function renderStudentPass(data) {
    currentPassStudentName = data.name || 'Student';

    // Populate student metadata
    document.getElementById('passStudentName').textContent = data.name || 'Unknown Student';
    document.getElementById('passStudentNumber').textContent = data.student_number || 'STU-0000';
    document.getElementById('passCardFooterId').textContent = (data.student_number || 'STU-0000') + ' | ' + (data.subject_code || 'SUBJ');
    document.getElementById('passProgramName').textContent = data.program_name || 'Degree Program';
    document.getElementById('passStudentStatus').textContent = data.status || 'Regular';
    document.getElementById('passYearLevel').textContent = data.year_level || 'First Year';
    document.getElementById('passSetName').textContent = data.set_name || 'Set A';
    document.getElementById('passCourseCode').textContent = data.subject_code || 'SUBJ';
    document.getElementById('passIssuedDate').textContent = data.issued_date || 'Today';

    // Populate period grades
    const scores = data.period_scores || {};
    document.getElementById('passPrelimScore').textContent = scores['Prelim'] !== undefined && scores['Prelim'] !== null ? parseFloat(scores['Prelim']).toFixed(1) : '—';
    document.getElementById('passMidtermScore').textContent = scores['Midterm'] !== undefined && scores['Midterm'] !== null ? parseFloat(scores['Midterm']).toFixed(1) : '—';
    document.getElementById('passSemifinalScore').textContent = scores['Semi-Final'] !== undefined && scores['Semi-Final'] !== null ? parseFloat(scores['Semi-Final']).toFixed(1) : '—';
    document.getElementById('passFinalScore').textContent = scores['Final'] !== undefined && scores['Final'] !== null ? parseFloat(scores['Final']).toFixed(1) : '—';

    // Final grade and badge
    const finalGradeEl = document.getElementById('passFinalGrade');
    const badgeEl = document.getElementById('passRemarkBadge');
    const statusBadge = document.getElementById('passStatusBadge');

    if (data.final_grade !== null && data.final_grade !== undefined) {
        finalGradeEl.textContent = parseFloat(data.final_grade).toFixed(2) + '%';
        if (data.final_grade >= 75.0) {
            if (badgeEl) {
                badgeEl.className = 'badge border text-dark fw-semibold px-3 py-1.5 fs-7';
                badgeEl.style.backgroundColor = '#f1f5f9';
                badgeEl.style.borderColor = '#cbd5e1';
                badgeEl.textContent = 'PASSED';
            }
        } else {
            if (badgeEl) {
                badgeEl.className = 'badge border text-dark fw-semibold px-3 py-1.5 fs-7';
                badgeEl.style.backgroundColor = '#fef2f2';
                badgeEl.style.borderColor = '#fecaca';
                badgeEl.textContent = 'FAILED';
            }
        }
    } else {
        finalGradeEl.textContent = 'INCOMPLETE';
        if (badgeEl) {
            badgeEl.className = 'badge border text-dark fw-semibold px-3 py-1.5 fs-7';
            badgeEl.style.backgroundColor = '#fffbeb';
            badgeEl.style.borderColor = '#fef3c7';
            badgeEl.textContent = 'PENDING';
        }
    }
    if (statusBadge) {
        statusBadge.style.display = 'none';
    }

    // Attendance stats
    const att = data.attendance || {};
    const presentCount = att.present_count || 0;
    const totalAbsences = att.total_absences || 0;
    const hasWarning = att.has_warning || false;

    document.getElementById('passAttendanceStats').textContent = `${presentCount} sessions present • ${totalAbsences} recorded absences`;

    const warningNotice = document.getElementById('passWarningNotice');
    const absenceBadge = document.getElementById('passAbsenceBadge');
    if (hasWarning) {
        if (warningNotice) warningNotice.classList.remove('d-none');
        if (absenceBadge) {
            absenceBadge.className = 'badge bg-danger text-white rounded-pill py-1 px-2.5';
            absenceBadge.innerHTML = '<i class="bi bi-exclamation-triangle-fill me-1"></i> Attendance Warning (5+ Absences)';
        }
    } else {
        if (warningNotice) warningNotice.classList.add('d-none');
        if (absenceBadge) {
            absenceBadge.className = 'text-secondary small fw-medium';
            absenceBadge.innerHTML = '<i class="bi bi-check2 text-success me-1"></i> Regular Standing';
        }
    }

    // Direct Verification Link
    const verifyLink = document.getElementById('passVerifyDirectLink');
    if (verifyLink) {
        verifyLink.href = data.verify_url || '#';
    }

    // Render QR Code
    const qrContainer = document.getElementById('passQrContainer');
    qrContainer.innerHTML = '';

    const verifyPayload = data.verify_url || JSON.stringify({
        id: data.student_number,
        name: data.name,
        course: data.subject_code,
        grade: data.final_grade,
        issued: data.issued_date
    });

    try {
        new QRCode(qrContainer, {
            text: verifyPayload,
            width: 114,
            height: 114,
            colorDark: '#0e244d',
            colorLight: '#ffffff',
            correctLevel: QRCode.CorrectLevel.M
        });
    } catch (e) {
        qrContainer.innerHTML = '<div class="text-danger small" style="font-size:10px;">QR unavailable</div>';
    }
}

function downloadStudentPass() {
    const card = document.getElementById('studentPassCard');
    const btn = document.getElementById('btnDownloadPass');
    if (!card) return;

    if (typeof html2canvas === 'undefined') {
        alert('Image generator is initializing. Please try again in a moment.');
        return;
    }

    if (btn) {
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1.5" role="status"></span> Generating...';
    }

    html2canvas(card, {
        scale: 2,
        backgroundColor: '#ffffff',
        useCORS: true,
        logging: false
    }).then(canvas => {
        const link = document.createElement('a');
        const cleanName = (currentPassStudentName || 'Student').replace(/[^a-zA-Z0-9_-]/g, '_');
        link.download = `GWC_Student_Pass_${cleanName}_${Date.now()}.png`;
        link.href = canvas.toDataURL('image/png');
        link.click();
    }).catch(err => {
        console.error('Failed to export student pass image', err);
        alert('Failed to generate pass image. Please try again.');
    }).finally(() => {
        if (btn) {
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-download"></i> Download PNG Pass';
        }
    });
}
</script>
