// @ts-check
const { test, expect } = require('@playwright/test');

test.describe('Authentication Flows', () => {
  test('should display the login page with all branding and form elements', async ({ page }) => {
    await page.goto('login');

    // Verify title and page headers
    await expect(page).toHaveTitle(/Sign In|Acadtrack/);
    await expect(page.locator('.auth-brand-emblem')).toBeVisible();
    await expect(page.locator('.auth-brand-sys')).toContainText('Golden West Colleges, Inc.');
    await expect(page.locator('#email')).toBeVisible();
    await expect(page.locator('#password')).toBeVisible();
    await expect(page.locator('#submitBtn')).toBeVisible();
    await expect(page.locator('a[href*="forgot-password"]')).toBeVisible();
  });

  test('should toggle password visibility when clicking the eye button', async ({ page }) => {
    await page.goto('login');

    const passwordInput = page.locator('#password');
    const toggleBtn = page.locator('#togglePasswordBtn');

    // Initially type is password
    await expect(passwordInput).toHaveAttribute('type', 'password');

    // Click to show password
    await toggleBtn.click();
    await expect(passwordInput).toHaveAttribute('type', 'text');

    // Click again to hide password
    await toggleBtn.click();
    await expect(passwordInput).toHaveAttribute('type', 'password');
  });

  test('should display error message on invalid credentials', async ({ page }) => {
    await page.goto('login');

    await page.fill('#email', 'invalid_user@gwc.edu');
    await page.fill('#password', 'WrongPassword123!');
    await page.click('#submitBtn');

    // Should stay on login or show error alert
    await expect(page.locator('.alert-danger, .error-message, .alert')).toBeVisible();
  });

  test('should successfully log in as Admin and redirect to dashboard', async ({ page }) => {
    await page.goto('login');

    await page.fill('#email', 'admin@gwc.edu');
    await page.fill('#password', 'Admin123!');
    await page.click('#submitBtn');

    // Should redirect to admin dashboard
    await expect(page).toHaveURL(/.*\/admin\/dashboard/);
    await expect(page.getByRole('heading', { name: 'Admin Dashboard' })).toBeVisible();
  });
});
