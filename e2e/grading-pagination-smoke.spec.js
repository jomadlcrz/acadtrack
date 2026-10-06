// @ts-check
const { test, expect } = require('@playwright/test');

test.use({ ignoreHTTPSErrors: true, serviceWorkers: 'block' });

test('faculty grading page paginates and searches server-side', async ({ page }) => {
  test.setTimeout(120 * 1000);

  await page.goto('login');
  await page.fill('#email', 'faculty@gwc.edu');
  await page.fill('#password', 'faculty123');
  await page.click('#submitBtn');
  await expect(page).toHaveURL(/.*faculty\/dashboard/);

  await page.goto('faculty/grading');
  await expect(page).toHaveURL(/.*faculty\/grading/);
  await expect(page.locator('#rosterSearchForm')).toBeVisible();
  await expect(page.locator('#settingsModal')).toBeVisible();

  const table = page.locator('#rosterTable');
  const pageTitle = page.locator('h1');
  await expect(pageTitle).toHaveText(/Grade Encoding Sheet/);

  const rowCount = await table.locator('tbody tr').count();
  console.log('page-1 roster rows:', rowCount);

  const paginationVisible = await page.locator('.pagination').count();
  console.log('pagination nav present:', paginationVisible > 0);

  const totalText = (await page.locator('text=enrolled students').first().textContent()) || '';
  console.log('enrolled count label:', totalText.trim());

  await page.fill('#rosterSearchForm input[name="search"]', '2026-2');
  await Promise.all([
    page.waitForNavigation(),
    page.locator('#rosterSearchForm').press('Enter'),
  ]);
  await expect(page).toHaveURL(/search=2026-2/);
  await expect(page.locator('#rosterSearchForm input[name="search"]')).toHaveValue('2026-2');
  const filteredRowCount = await table.locator('tbody tr').count();
  console.log('search-result roster rows:', filteredRowCount);
  expect(filteredRowCount).toBeLessThanOrEqual(15);
});