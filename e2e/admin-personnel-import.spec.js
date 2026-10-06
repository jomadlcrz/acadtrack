// @ts-check
const { test, expect } = require('@playwright/test');

test.describe('Admin Personnel Import', () => {
  test.beforeEach(async ({ page }) => {
    await page.goto('login');
    await page.fill('#email', 'admin@gwc.edu');
    await page.fill('#password', 'Admin123!');
    await page.click('#submitBtn');
    await expect(page).toHaveURL(/.*\/admin\/dashboard/);
  });

  test('Faculty create page shows download template and import spreadsheet actions', async ({ page }) => {
    await page.goto('admin/users/create?role=Faculty');

    await expect(page.locator('h1')).toContainText('Faculty');

    const downloadBtn = page.locator('a[href*="import-template?role=Faculty"]').first();
    await expect(downloadBtn).toBeVisible();
    await expect(downloadBtn).toContainText('Download template');

    const importBtn = page.locator('button[data-bs-target="#personnelImportModal"]');
    await expect(importBtn).toBeVisible();
    await expect(importBtn).toContainText('Import spreadsheet');
  });

  test('Dean create page shows the personnel import modal with Dean wording', async ({ page }) => {
    await page.goto('admin/users/create?role=Dean');

    await page.locator('button[data-bs-target="#personnelImportModal"]').click();

    const modal = page.locator('#personnelImportModal');
    await expect(modal).toBeVisible();
    await expect(modal.locator('.modal-title')).toContainText('Batch Dean Registration');
    await expect(modal).toContainText('Required columns: First Name, Last Name, Email, Department');
    await expect(modal.locator('a[href*="import-template?role=Dean"]')).toBeVisible();
  });

  test('Faculty import modal previews a valid personnel spreadsheet without submitting', async ({ page }) => {
    await page.goto('admin/users/create?role=Faculty');
    await page.locator('button[data-bs-target="#personnelImportModal"]').click();

    const modal = page.locator('#personnelImportModal');
    await expect(modal).toBeVisible();

    const csv = 'First Name,Last Name,Email,Department\r\n' +
      'Juan,Dela Cruz,juan.delacruz@gwc.edu.ph,CITE\r\n' +
      'Maria,Santos,maria.santos@gwc.edu.ph,College of Information Technology Education\r\n';

    await modal.locator('#personnelImportFileInput').setInputFiles({
      name: 'personnel.csv',
      mimeType: 'text/csv',
      buffer: Buffer.from(csv, 'utf-8'),
    });

    await expect(modal.locator('#personnelPreviewContainer')).toBeVisible();
    await expect(modal.locator('#personnelPreviewSummary')).toContainText('2 records');

    const rows = modal.locator('#personnelPreviewBody tr');
    await expect(rows).toHaveCount(2);
    await expect(rows.nth(0)).toContainText('Dela Cruz, Juan');
    await expect(rows.nth(0)).toContainText('juan.delacruz@gwc.edu.ph');

    // Each preview row is marked Ready and the submit button enables.
    await expect(modal.locator('#personnelPreviewBody')).toContainText('Ready');
    await expect(modal.locator('#personnelImportSubmit')).toBeEnabled();
  });

  test('Admin create page does not offer spreadsheet import', async ({ page }) => {
    await page.goto('admin/users/create?role=Admin');

    await expect(page.locator('h1')).toContainText('Add Admin Account');

    await expect(page.locator('a[href*="import-template"]')).toHaveCount(0);
    await expect(page.locator('button[data-bs-target="#personnelImportModal"]')).toHaveCount(0);
    await expect(page.locator('#personnelImportModal')).toHaveCount(0);
  });

  test('Student create page still offers the student import modal', async ({ page }) => {
    await page.goto('admin/users/create?role=Student');

    await page.locator('button[data-bs-target="#importExcelModal"]').click();

    const modal = page.locator('#importExcelModal');
    await expect(modal).toBeVisible();
    await expect(modal.locator('.modal-title')).toContainText('Batch Student Registration');
  });
});

test.describe('Official Legal Pages', () => {
  test('privacy page no longer shows the removed metadata blocks', async ({ page }) => {
    await page.goto('privacy');

    await expect(page).toHaveTitle(/Privacy|Acadtrack/);

    await expect(page.locator('.legal-doc-title')).toContainText('Data Privacy Notice');
    await expect(page.locator('text=Governing Academic Unit')).toHaveCount(0);
    await expect(page.locator('text=Statutory Reference')).toHaveCount(0);
    await expect(page.locator('text=Effective Term')).toHaveCount(0);
    await expect(page.locator('.legal-meta-grid')).toHaveCount(0);
  });

  test('terms page no longer shows the removed metadata blocks', async ({ page }) => {
    await page.goto('terms');

    await expect(page).toHaveTitle(/Terms|Acadtrack/);

    await expect(page.locator('.legal-doc-title')).toContainText('Terms of Academic Service');
    await expect(page.locator('text=Governing Academic Unit')).toHaveCount(0);
    await expect(page.locator('text=Jurisdiction')).toHaveCount(0);
    await expect(page.locator('text=Effective Term')).toHaveCount(0);
    await expect(page.locator('.legal-meta-grid')).toHaveCount(0);
  });
});