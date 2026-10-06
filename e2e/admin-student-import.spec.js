// @ts-check
const { test, expect } = require('@playwright/test');
const path = require('path');

// Regression spec for the admin student bulk import.
// Historical failure: per-row SMTP credential emails blocked until Apache's
// max_execution_time, the PHP fatal rendered as HTML, and the modal's
// res.json() blew up with "Unexpected token '<'". The endpoint must now
// answer JSON and return the temp credentials in the payload instead.
test.use({ ignoreHTTPSErrors: true, serviceWorkers: 'block' });

const WORKBOOK = path.resolve(__dirname, '..', 'GWC_BSIT_Freshmen_45PerSet.xlsx');

test.describe('Admin Student Bulk Import', () => {
  test.beforeEach(async ({ page }) => {
    await page.goto('login');
    await page.fill('#email', 'admin@gwc.edu');
    await page.fill('#password', 'Admin123!');
    await page.click('#submitBtn');
    await expect(page).toHaveURL(/.*\/admin\/dashboard/);
  });

  test('imports the 45-per-set workbook and answers JSON, not HTML', async ({ page }) => {
    test.setTimeout(300 * 1000);

    await page.goto('admin/users/create?role=Student');

    await page.locator('button[data-bs-target="#importExcelModal"]').click();
    const modal = page.locator('#importExcelModal');
    await expect(modal).toBeVisible();

    const consoleErrors = [];
    page.on('console', (msg) => {
      if (msg.type() === 'error') consoleErrors.push(msg.text());
    });

    await modal.locator('#indexExcelFileInput').setInputFiles(WORKBOOK);

    await expect(modal.locator('#indexPreviewSummary')).toContainText('315 records');

    const rows = modal.locator('#indexPreviewTableBody tr');
    await expect(rows).toHaveCount(315);

    const responsePromise = page.waitForResponse(
      (r) => r.request().method() === 'POST' && r.url().includes('/admin/users/import'),
      { timeout: 280 * 1000 }
    );

    await modal.locator('#indexBtnSubmitImport').click();

    const response = await responsePromise;
    const contentType = response.headers()['content-type'] || '';
    const raw = await response.text();

    console.log('IMPORT status:', response.status());
    console.log('IMPORT content-type:', contentType);
    console.log('IMPORT body head:', raw.slice(0, 300));
    if (consoleErrors.length) console.log('Browser console errors:', JSON.stringify(consoleErrors));

    expect(contentType).toContain('application/json');
    expect(raw.trim()).toMatch(/^{/, 'Response body must be JSON, not a PHP/HTML error, not empty.');

    const body = JSON.parse(raw);
    expect(response.status()).toBe(200);
    expect(body.success).toBe(true);

    const total = body.created + body.failed;
    expect(Number(body.total)).toBe(315);
    expect(total).toBe(315);

    console.log('IMPORT result: created', body.created, '| failed', body.failed);

    const credentials = body.credentials || {};
    expect(Object.keys(credentials).length).toBe(body.created);
    if (body.created > 0) {
      const firstEmail = Object.keys(credentials)[0];
      expect(typeof credentials[firstEmail]).toBe('string');
      expect(credentials[firstEmail].length).toBeGreaterThanOrEqual(8);
    }

    await expect(modal.locator('#indexImportStatusAlert')).toContainText('Successfully registered');
  });
});