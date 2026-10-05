// @ts-check
const { test, expect } = require('@playwright/test');

test.describe('Admin Sets and Curricula Workflow', () => {
  test.beforeEach(async ({ page }) => {
    // Log in as Admin
    await page.goto('login');
    await page.fill('#email', 'admin@gwc.edu');
    await page.fill('#password', 'Admin123!');
    await page.click('#submitBtn');
    await expect(page).toHaveURL(/.*\/admin\/dashboard/);
  });

  test('should navigate to sets and verify section list renders', async ({ page }) => {
    await page.goto('admin/sets');

    await expect(page).toHaveTitle(/Sets|Sections|Acadtrack/);
    await expect(page.locator('table, .card, .container-fluid').first()).toBeVisible();
    await expect(page.getByRole('button', { name: 'Create Sections' })).toBeVisible();
  });

  test('should navigate to program curricula and display curriculum subjects', async ({ page }) => {
    await page.goto('admin/program-curricula');

    await expect(page).toHaveTitle(/Curricula|Curriculum|Acadtrack/);
    await expect(page.locator('h1, h2, h3, h4').filter({ hasText: /Curriculum|Program/ }).first()).toBeVisible();
    await expect(page.locator('table, .curriculum-table').first()).toBeVisible();
  });
});
