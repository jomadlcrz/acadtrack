<!-- Student Grade Pass Slip Modal (IntelliGrade Model) -->
<div class="modal fade" id="passCardModal" tabindex="-1" aria-labelledby="passCardModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 12px; overflow: hidden;">
            <div class="modal-header bg-dark text-white py-3 px-4 border-0 d-flex justify-content-between align-items-center" style="background: linear-gradient(135deg, #0f172a, #1e293b) !important;">
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-primary px-2 py-1 text-uppercase" style="letter-spacing: 0.5px; font-size: 10px;">AcadTrack Verified</span>
                    <h5 class="modal-title h6 fw-bold mb-0 text-white" id="passCardModalLabel">
                        Official Student Grade Slip
                    </h5>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body p-0" id="printablePassCardArea">
                <div class="pass-card-sheet p-4" style="background: #ffffff; font-family: 'Inter', system-ui, -apple-system, sans-serif;">
                    <!-- Institution Brand Header -->
                    <div class="d-flex justify-content-between align-items-start pb-3 mb-3 border-bottom flex-wrap gap-2">
                        <div>
                            <h4 class="h6 mb-0 fw-bold text-dark text-uppercase" style="letter-spacing: 0.5px;">Grade Evaluation &amp; Review System</h4>
                            <small class="text-muted d-block" style="font-size: 11px;">AcadTrack Official Academic Scholastic Record</small>
                        </div>
                        <div class="text-end">
                            <span class="badge bg-success-subtle text-success border border-success-subtle px-2.5 py-1 fw-semibold" style="font-size: 11px;">
                                <i class="bi bi-patch-check-fill me-1"></i> Authenticated Slip
                            </span>
                            <small class="d-block text-muted mt-1 font-monospace" id="passCardTermLabel" style="font-size: 11px;">
                                2026-2027 &bull; 1st Semester
                            </small>
                        </div>
                    </div>

                    <!-- Student & Course Information Block -->
                    <div class="row g-3 mb-4 align-items-center">
                        <!-- QR Code & ID Section -->
                        <div class="col-sm-4 text-center">
                            <div class="p-2 border rounded-3 bg-light d-inline-block shadow-sm mb-1">
                                <div id="passCardQrContainer" style="width: 110px; height: 110px; margin: 0 auto;" class="d-flex align-items-center justify-content-center bg-white rounded">
                                    <span class="text-muted small">Loading QR...</span>
                                </div>
                            </div>
                            <div class="font-monospace fw-bold text-dark small" id="passCardStudentNumber">
                                2024-00000
                            </div>
                            <small class="text-muted d-block" style="font-size: 10px;">
                                <i class="bi bi-qr-code-scan me-1"></i> Scan to verify record
                            </small>
                        </div>

                        <!-- Metadata Grid -->
                        <div class="col-sm-8">
                            <h3 class="h5 fw-bold text-dark mb-2" id="passCardStudentName">
                                Student Full Name
                            </h3>
                            <div class="row g-2 pt-1 small">
                                <div class="col-6">
                                    <span class="text-muted d-block" style="font-size: 10px; text-transform: uppercase;">Subject Code</span>
                                    <span class="fw-semibold text-primary font-monospace" id="passCardSubjectCode">IT101</span>
                                </div>
                                <div class="col-6">
                                    <span class="text-muted d-block" style="font-size: 10px; text-transform: uppercase;">Course Nature</span>
                                    <span class="fw-semibold text-dark" id="passCardCourseNature">Lecture</span>
                                </div>
                                <div class="col-12">
                                    <span class="text-muted d-block" style="font-size: 10px; text-transform: uppercase;">Descriptive Title</span>
                                    <span class="fw-semibold text-dark" id="passCardSubjectTitle">Introduction to Computing</span>
                                </div>
                                <div class="col-6">
                                    <span class="text-muted d-block" style="font-size: 10px; text-transform: uppercase;">Year Level / Status</span>
                                    <span class="fw-semibold text-dark" id="passCardYearLevel">1st Year &bull; Regular</span>
                                </div>
                                <div class="col-6">
                                    <span class="text-muted d-block" style="font-size: 10px; text-transform: uppercase;">Faculty Instructor</span>
                                    <span class="fw-semibold text-dark" id="passCardFacultyName">Department Faculty</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 4-Period Grading Matrix -->
                    <div class="p-3 bg-light rounded-3 border mb-4">
                        <span class="d-block text-secondary small fw-semibold text-uppercase mb-2" style="font-size: 11px; letter-spacing: 0.5px;">
                            Evaluation Period Ratings
                        </span>
                        <div class="row g-2 text-center font-monospace">
                            <div class="col-3">
                                <div class="bg-white p-2.5 rounded border">
                                    <span class="text-muted d-block" style="font-size: 10px; text-transform: uppercase;">Prelim</span>
                                    <span class="h6 mb-0 fw-bold text-dark" id="passCardPrelim">—</span>
                                </div>
                            </div>
                            <div class="col-3">
                                <div class="bg-white p-2.5 rounded border">
                                    <span class="text-muted d-block" style="font-size: 10px; text-transform: uppercase;">Midterm</span>
                                    <span class="h6 mb-0 fw-bold text-dark" id="passCardMidterm">—</span>
                                </div>
                            </div>
                            <div class="col-3">
                                <div class="bg-white p-2.5 rounded border">
                                    <span class="text-muted d-block" style="font-size: 10px; text-transform: uppercase;">Semi-Final</span>
                                    <span class="h6 mb-0 fw-bold text-dark" id="passCardSemiFinal">—</span>
                                </div>
                            </div>
                            <div class="col-3">
                                <div class="bg-white p-2.5 rounded border">
                                    <span class="text-muted d-block" style="font-size: 10px; text-transform: uppercase;">Final</span>
                                    <span class="h6 mb-0 fw-bold text-dark" id="passCardFinal">—</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Final Evaluation & Benchmark Status -->
                    <div class="p-3 rounded-3 border d-flex flex-wrap justify-content-between align-items-center gap-3" id="passCardResultBanner" style="background-color: #f8fafc;">
                        <div>
                            <span class="text-muted small d-block" style="font-size: 11px; text-transform: uppercase;">Final Weighted Rating</span>
                            <div class="d-flex align-items-baseline gap-2">
                                <span class="h3 mb-0 fw-bold text-primary font-monospace" id="passCardFinalRating">0.00</span>
                                <span class="text-muted small">/ 100.00</span>
                            </div>
                        </div>
                        <div class="text-end">
                            <span class="text-muted small d-block mb-1" style="font-size: 11px; text-transform: uppercase;">Official CHED Remark</span>
                            <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-1.5 fs-6 fw-bold" id="passCardRemarkBadge">
                                PASSED
                            </span>
                        </div>
                    </div>

                    <!-- Document Footer & Security Stamp -->
                    <div class="pt-4 mt-3 border-top d-flex justify-content-between align-items-center text-muted small" style="font-size: 10px;">
                        <div>
                            <span>Digital Verification Hash: <code class="text-secondary" id="passCardHash">ACAD-000000</code></span>
                        </div>
                        <div>
                            <span>Generated on: <span id="passCardGeneratedDate">2026-09-14</span></span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer bg-light py-2.5 px-4 border-top d-flex justify-content-between align-items-center">
                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">
                    Close
                </button>
                <button type="button" class="btn btn-sm btn-primary d-inline-flex align-items-center gap-1.5" onclick="printPassCardSlip()">
                    <i class="bi bi-printer"></i> Print / Save Grade Slip
                </button>
            </div>
        </div>
    </div>
</div>

<style>
@media print {
    body * {
        visibility: hidden !important;
    }
    #printablePassCardArea, #printablePassCardArea * {
        visibility: visible !important;
    }
    #printablePassCardArea {
        position: fixed !important;
        left: 0 !important;
        top: 0 !important;
        width: 100% !important;
        margin: 0 !important;
        padding: 24px !important;
        box-shadow: none !important;
    }
    .modal {
        position: static !important;
        display: block !important;
    }
    .modal-dialog {
        max-width: 100% !important;
        margin: 0 !important;
    }
    .modal-content {
        border: none !important;
        box-shadow: none !important;
    }
}
</style>

<script src="<?= asset('vendor/qrcode.min.js') ?>"></script>
<script>
function renderPassCard(data) {
    document.getElementById('passCardStudentName').textContent = data.studentName || 'Student Name';
    document.getElementById('passCardStudentNumber').textContent = data.studentNumber || 'No ID';
    document.getElementById('passCardSubjectCode').textContent = data.subjectCode || '—';
    document.getElementById('passCardSubjectTitle').textContent = data.subjectTitle || 'Course Title';
    document.getElementById('passCardCourseNature').textContent = data.courseNature || 'Lecture';
    document.getElementById('passCardYearLevel').textContent = (data.yearLevel ? data.yearLevel + (data.yearLevel == 1 ? 'st' : (data.yearLevel == 2 ? 'nd' : (data.yearLevel == 3 ? 'rd' : 'th'))) + ' Year' : '1st Year') + ' • ' + (data.status || 'Regular');
    document.getElementById('passCardFacultyName').textContent = data.facultyName || 'Faculty Instructor';
    document.getElementById('passCardTermLabel').textContent = (data.schoolYear || '2026-2027') + ' • ' + (data.semesterLabel || '1st Semester');

    // Periods
    const pPrelim = document.getElementById('passCardPrelim');
    const pMidterm = document.getElementById('passCardMidterm');
    const pSemiFinal = document.getElementById('passCardSemiFinal');
    const pFinal = document.getElementById('passCardFinal');

    pPrelim.textContent = (data.prelim !== null && data.prelim !== undefined && data.prelim !== '') ? parseFloat(data.prelim).toFixed(2) : '—';
    pMidterm.textContent = (data.midterm !== null && data.midterm !== undefined && data.midterm !== '') ? parseFloat(data.midterm).toFixed(2) : '—';
    pSemiFinal.textContent = (data.semiFinal !== null && data.semiFinal !== undefined && data.semiFinal !== '') ? parseFloat(data.semiFinal).toFixed(2) : '—';
    pFinal.textContent = (data.final !== null && data.final !== undefined && data.final !== '') ? parseFloat(data.final).toFixed(2) : '—';

    // Final Rating & Remark
    const finalRating = parseFloat(data.finalRating) || 0;
    const ratingEl = document.getElementById('passCardFinalRating');
    const badgeEl = document.getElementById('passCardRemarkBadge');
    const bannerEl = document.getElementById('passCardResultBanner');

    ratingEl.textContent = finalRating > 0 ? finalRating.toFixed(2) : (data.prelim ? parseFloat(data.prelim).toFixed(2) : '0.00');

    const effectiveScore = finalRating > 0 ? finalRating : (parseFloat(data.prelim) || 0);
    const hasScores = (data.prelim !== null && data.prelim !== '') || finalRating > 0;

    if (!hasScores) {
        badgeEl.className = 'badge bg-secondary-subtle text-secondary border border-secondary-subtle px-3 py-1.5 fs-6 fw-bold';
        badgeEl.textContent = 'NO MARKS';
        if (bannerEl) bannerEl.style.backgroundColor = '#f8fafc';
    } else if (effectiveScore >= 75.0) {
        badgeEl.className = 'badge bg-success-subtle text-success border border-success-subtle px-3 py-1.5 fs-6 fw-bold';
        badgeEl.textContent = 'PASSED (3.00 Equivalent)';
        if (bannerEl) bannerEl.style.backgroundColor = '#f0fdf4';
    } else {
        badgeEl.className = 'badge bg-danger-subtle text-danger border border-danger-subtle px-3 py-1.5 fs-6 fw-bold';
        badgeEl.textContent = 'FAILED (< 75.00)';
        if (bannerEl) bannerEl.style.backgroundColor = '#fef2f2';
    }

    // Generate Hash
    const hash = 'ACAD-' + Math.abs((data.studentNumber || 'STU').split('').reduce((a, b) => { a = ((a << 5) - a) + b.charCodeAt(0); return a & a }, 0)).toString(16).toUpperCase();
    document.getElementById('passCardHash').textContent = hash;
    document.getElementById('passCardGeneratedDate').textContent = new Date().toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });

    // Generate Verification QR Code
    const qrContainer = document.getElementById('passCardQrContainer');
    if (qrContainer) {
        qrContainer.innerHTML = '';
        const qrPayload = {
            system: 'AcadTrack SIS',
            student_id: data.studentNumber,
            student_name: data.studentName,
            subject: data.subjectCode,
            final_grade: ratingEl.textContent,
            status: badgeEl.textContent.trim(),
            verified_hash: hash,
            timestamp: new Date().toISOString()
        };

        try {
            new QRCode(qrContainer, {
                text: JSON.stringify(qrPayload),
                width: 100,
                height: 100,
                colorDark: '#0f172a',
                colorLight: '#ffffff',
                correctLevel: QRCode.CorrectLevel.M
            });
        } catch (err) {
            qrContainer.innerHTML = '<span class="text-muted small" style="font-size: 10px;">QR Ready</span>';
        }
    }

    const modalEl = document.getElementById('passCardModal');
    if (modalEl) {
        const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
        modal.show();
    }
}

function printPassCardSlip() {
    window.print();
}
</script>
