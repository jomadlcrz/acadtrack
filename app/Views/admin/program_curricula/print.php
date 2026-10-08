<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <title>Curriculum — <?= htmlspecialchars($program->program_name ?? 'Program Curriculum') ?></title>
  <link rel="icon" href="<?= url('/favicon.ico') ?>" />
  <style>
    * { box-sizing: border-box; margin: 0; padding: 0; }
    html, body {
      -webkit-print-color-adjust: exact;
      print-color-adjust: exact;
    }
    body {
      font-family: Arial, Helvetica, sans-serif;
      color: #000;
      background: #fff;
      font-size: 8.5pt;
      line-height: 1.2;
    }

    /* Top preview bar for on-screen preview (matching evaluation & grade-review style) */
    .no-print-bar {
      background: #0f172a;
      color: #f8fafc;
      padding: 10px 20px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      font-size: 13px;
      box-shadow: 0 2px 4px rgba(0, 0, 0, 0.15);
      position: sticky;
      top: 0;
      z-index: 100;
      width: 100%;
    }
    .no-print-bar .doc-tag {
      font-weight: 600;
      letter-spacing: 0.3px;
    }
    .no-print-bar .btn-group {
      display: flex;
      gap: 8px;
    }
    .btn-preview {
      border: 1px solid #475569;
      background: transparent;
      color: #f8fafc;
      padding: 6px 14px;
      font-size: 12px;
      border-radius: 4px;
      cursor: pointer;
      text-decoration: none;
      display: inline-flex;
      align-items: center;
      gap: 6px;
      font-weight: 500;
      transition: all 0.15s ease;
    }
    .btn-preview:hover {
      background: #1e293b;
      color: #fff;
    }
    .btn-preview.btn-primary-preview {
      background: #1e3a5f;
      border-color: #1e3a5f;
      color: #fff;
    }
    .btn-preview.btn-primary-preview:hover {
      background: #152942;
    }

    @media screen {
      body {
        background: #f1f5f9;
        padding-bottom: 30px;
      }
      .print-sheet {
        max-width: 8.5in;
        margin: 24px auto;
        background: #fff;
        padding: 0.4in;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -2px rgba(0, 0, 0, 0.1);
        border: 1px solid #cbd5e1;
      }
    }

    @page {
      size: portrait;
      margin: 0.35in;
    }

    @media print {
      body {
        background: #fff !important;
        padding: 0 !important;
        margin: 0 !important;
      }
      .no-print-bar {
        display: none !important;
      }
      .print-sheet {
        padding: 0 !important;
        margin: 0 !important;
        box-shadow: none !important;
        border: none !important;
        max-width: 100% !important;
        width: 100% !important;
      }
      tr {
        page-break-inside: avoid !important;
      }
      .cp-year-block {
        break-inside: avoid !important;
        page-break-inside: avoid !important;
      }
    }

    /* Institutional Letterhead */
    .cp-header {
      position: relative;
      min-height: 0.72in;
      text-align: center;
      margin-bottom: 0.4rem;
      line-height: 1.12;
      padding: 0 0.78in;
    }
    .cp-header strong, .cp-header span, .cp-header small { display: block; }
    .cp-header strong { font-size: 15px; }
    .cp-header span { font-size: 10px; }
    .cp-header small { margin-top: 0.35rem; font-size: 9px; }
    .cp-header h2 { margin: 0.05rem 0 0; font-size: 15px; }
    .cp-header p { margin: 0.05rem 0 0; font-size: 10px; }

    .cp-logo {
      position: absolute;
      top: 0.03in;
      width: 0.62in;
      height: 0.62in;
      display: block;
      box-sizing: border-box;
      padding: 0.03in;
      object-fit: contain;
      object-position: center;
    }
    .cp-logo-left { left: 0.03in; }
    .cp-logo-right { right: 0.03in; }

    /* Year Blocks */
    .cp-year-block {
      width: 100%;
      margin-top: 0.45rem;
      break-inside: avoid;
      page-break-inside: avoid;
    }
    .cp-year-title {
      border: none;
      background: transparent;
      text-align: center;
      font-size: 13px;
      font-weight: bold;
      padding: 0.25rem 0 0.15rem 0;
      letter-spacing: 0.5px;
      margin-bottom: 0.2rem;
    }

    /* Semesters Row: side-by-side with clear gap separation, natural height without auto-stretching */
    .cp-semesters-row {
      display: flex;
      gap: 0.65rem;
      align-items: flex-start;
    }
    .cp-semester-col {
      flex: 1;
      min-width: 0;
    }
    .cp-sem-title {
      border: 1px solid #444;
      border-bottom: none;
      text-align: center;
      font-size: 9.5px;
      font-weight: bold;
      padding: 0.12rem 0.2rem;
      background: #f1f5f9;
      letter-spacing: 0.3px;
    }

    /* Subject tables within each semester */
    .cp-subjects {
      width: 100%;
      table-layout: fixed;
      border-collapse: collapse;
    }
    .cp-subjects th, .cp-subjects td {
      border: 1px solid #444;
      padding: 0.08rem 0.12rem;
      color: #000;
      font-size: 8px;
      line-height: 1.1;
      vertical-align: middle;
    }
    .cp-subjects th {
      text-align: center;
      background: #f8fafc;
      font-weight: bold;
    }
    .cp-subjects td:nth-child(1), .cp-subjects th:nth-child(1) { width: 22%; }
    .cp-subjects td:nth-child(2), .cp-subjects th:nth-child(2) { width: 50%; }
    .cp-subjects td:nth-child(3), .cp-subjects th:nth-child(3) { width: 10%; text-align: center; }
    .cp-subjects td:nth-child(4), .cp-subjects th:nth-child(4) { width: 18%; text-align: center; }
    .cp-subjects tfoot td {
      border-top: 1px solid #444;
      border-left-color: transparent;
      border-right-color: transparent;
      border-bottom-color: transparent;
      text-align: center;
      font-weight: bold;
    }

    .cp-total {
      margin-top: 0.35rem;
      font-size: 9.5px;
    }
  </style>
</head>
<body>
  <!-- Top preview bar for on-screen preview (hidden when printing) -->
  <div class="no-print-bar">
    <div class="doc-tag">Curriculum Document — <?= htmlspecialchars($program->program_abbrev ?? 'Program') ?></div>
    <div class="btn-group">
      <button type="button" class="btn-preview btn-primary-preview" onclick="window.print()">
        <svg width="14" height="14" fill="currentColor" viewBox="0 0 16 16"><path d="M2.5 8a.5.5 0 1 0 0-1 .5.5 0 0 0 0 1z"/><path d="M5 1a2 2 0 0 0-2 2v2H2a2 2 0 0 0-2 2v3a2 2 0 0 0 2 2h1v1a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2v-1h1a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-1V3a2 2 0 0 0-2-2H5zM4 3a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1v2H4V3zm1 5a2 2 0 0 0-2 2v1H2a1 1 0 0 1-1-1V7a1 1 0 0 1 1-1h12a1 1 0 0 1 1 1v3a1 1 0 0 1-1 1h-1v-1a2 2 0 0 0-2-2H5zm7 2v3a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1v-3a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1z"/></svg>
        Print
      </button>
      <button type="button" class="btn-preview" onclick="window.close()">Close</button>
    </div>
  </div>

  <div class="print-sheet">
    <header class="cp-header">
      <img class="cp-logo cp-logo-left" src="<?= url('/assets/images/gwc.png') ?>" alt="Golden West Colleges, Inc. logo" />
      <img class="cp-logo cp-logo-right" src="<?= url('/assets/images/cite.png') ?>" alt="College logo" onerror="if(this.src!=='<?= url('/assets/images/gwc.png') ?>')this.src='<?= url('/assets/images/gwc.png') ?>'" />
      <strong>GOLDEN WEST COLLEGES, INC.</strong>
      <span>San Jose Drive, Alaminos City, Pangasinan</span>
      <small>Curriculum for</small>
      <h2><?= htmlspecialchars(strtoupper($program->program_name ?? '')) ?></h2>
    </header>

    <?php foreach ($yearBlocks as $year): ?>
      <?php $groups = array_values($year['groups']); ?>
      <div class="cp-year-block">
        <div class="cp-year-title"><?= strtoupper(htmlspecialchars($year['year_title'])) ?></div>
        <div class="cp-semesters-row">
          <?php foreach ($groups as $group): ?>
            <div class="cp-semester-col">
              <div class="cp-sem-title"><?= strtoupper(htmlspecialchars($group['semester_label'])) ?></div>
              <table class="cp-subjects">
                <thead>
                  <tr>
                    <th>SUBJECT CODE</th>
                    <th>DESCRIPTIVE TITLE</th>
                    <th>UNITS</th>
                    <th>PRE-REQUISITE</th>
                  </tr>
                </thead>
                <tbody>
                  <?php if (empty($group['subjects'])): ?>
                    <tr><td colspan="4" style="text-align:center; color:#666; font-style:italic;">No subjects assigned.</td></tr>
                  <?php else: ?>
                    <?php foreach ($group['subjects'] as $subject): ?>
                      <tr>
                        <td><?= htmlspecialchars($subject->subject_code) ?></td>
                        <td><?= htmlspecialchars($subject->descriptive_title) ?></td>
                        <td><?= (float)$subject->units == (int)$subject->units ? (int)$subject->units : number_format((float)$subject->units, 1) ?></td>
                        <td><?= !empty($subject->prerequisites) ? htmlspecialchars($subject->prerequisites) : '-' ?></td>
                      </tr>
                    <?php endforeach; ?>
                  <?php endif; ?>
                </tbody>
                <tfoot>
                  <tr>
                    <td colspan="2"></td>
                    <td><?= $group['total_units'] > 0 ? ((float)$group['total_units'] == (int)$group['total_units'] ? (int)$group['total_units'] : number_format((float)$group['total_units'], 1)) : '' ?></td>
                    <td></td>
                  </tr>
                </tfoot>
              </table>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    <?php endforeach; ?>

    <p class="cp-total">TOTAL UNITS: <strong><?= (float)$totalUnits == (int)$totalUnits ? (int)$totalUnits : number_format((float)$totalUnits, 1) ?></strong></p>
  </div>

  <script>
  (function () {
    var asked = false;
    function settle(img) {
      if (img.complete) return Promise.resolve();
      return new Promise(function (done) {
        img.addEventListener("load", done, { once: true });
        img.addEventListener("error", done, { once: true });
      });
    }
    function print() {
      if (asked) return;
      asked = true;
      requestAnimationFrame(function () {
        requestAnimationFrame(function () {
          window.focus();
          window.print();
        });
      });
    }
    window.addEventListener("load", function () {
      var waits = Array.prototype.map.call(document.images, settle);
      if (document.fonts && document.fonts.ready) waits.push(document.fonts.ready);
      Promise.race([
        Promise.all(waits),
        new Promise(function (done) { setTimeout(done, 1500); })
      ]).then(print, print);
    });
  })();
  </script>
</body>
</html>
