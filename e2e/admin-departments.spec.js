// @ts-check
const { test, expect } = require('@playwright/test');

test.describe('Admin Departments Workflow', () => {
  test.beforeEach(async ({ page }) => {
    // Log in as Admin before each test
    await page.goto('login');
    await page.fill('#email', 'admin@gwc.edu');
    await page.fill('#password', 'Admin123!');
    await page.click('#submitBtn');
    await expect(page).toHaveURL(/.*\/admin\/dashboard/);
  });

  test('should navigate to departments index and display department table', async ({ page }) => {
    await page.goto('admin/departments');

    await expect(page).toHaveTitle(/Departments|Acadtrack/);
    await expect(page.locator('table')).toBeVisible();

    // Verify CITE department row is present
    const citeRow = page.locator('tr:has-text("CITE")').first();
    await expect(citeRow).toBeVisible();
    await expect(citeRow).toContainText('College of Information Technology Education');
  });

  test('should navigate to department details page when clicking a department row or link', async ({ page }) => {
    await page.goto('admin/departments');

    // Click on the CITE department link
    const citeLink = page.locator('a[href*="/admin/departments/"]:has-text("CITE")').first();
    await expect(citeLink).toBeVisible();
    await citeLink.click();

    // Should redirect to /admin/departments/{id}
    await expect(page).toHaveURL(/.*\/admin\/departments\/\d+/);

    // Verify details page header and sections
    await expect(page.locator('h1, h2, h3, h4, h5, h6').filter({ hasText: /College of Information Technology Education|CITE/ }).first()).toBeVisible();
    await expect(page.locator('text=Academic Programs').first()).toBeVisible();
    await expect(page.locator('text=Assigned Faculty').first()).toBeVisible();

    // Verify back button works
    const backBtn = page.locator('a:has-text("Back to departments")');
    await expect(backBtn).toBeVisible();
    await backBtn.click();
    await expect(page).toHaveURL(/.*\/admin\/departments$/);
  });
});
