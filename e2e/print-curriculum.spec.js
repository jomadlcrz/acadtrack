// @ts-check
const { test, expect } = require('@playwright/test');

/**
 * Helper to log in a user with specific credentials.
 */
async function loginAs(page, email, password) {
    await page.context().clearCookies();
    await page.goto('login');
    await page.locator('#email').fill(email);
    await page.locator('#password').fill(password);
    await page.locator('button[type="submit"]').click();
    await page.waitForLoadState('networkidle');
}

test.describe('Program Curriculum Print View', () => {

    test('Print button on Program Curricula links to dedicated print view', async ({ page }) => {
        await loginAs(page, 'admin@gwc.edu', 'Admin123!');
        await page.goto('admin/program-curricula');
        await page.waitForLoadState('networkidle');

        const printBtn = page.locator('a[title="Print Curriculum"], a:has-text("Print")');
        await expect(printBtn).toBeVisible();

        const href = await printBtn.getAttribute('href');
        expect(href).toMatch(/\/admin\/program-curricula\/\d+\/print/);
    });

    test('Dedicated print view renders on-screen header bar and exact printable letterhead', async ({ page }) => {
        await loginAs(page, 'admin@gwc.edu', 'Admin123!');
        
        // Block window.print dialog during automated test
        await page.addInitScript(() => {
            window.print = () => {};
        });

        await page.goto('admin/program-curricula/1/print');
        await page.waitForLoadState('networkidle');

        // Verify title
        await expect(page).toHaveTitle(/Curriculum — Bachelor of Science in Information Technology/);

        // 1. Verify on-screen preview header bar (.no-print-bar)
        const noPrintBar = page.locator('.no-print-bar');
        await expect(noPrintBar).toBeVisible();
        await expect(noPrintBar.locator('.doc-tag')).toContainText('Curriculum Document — BSIT');
        await expect(noPrintBar.locator('button:has-text("Print")')).toBeVisible();
        await expect(noPrintBar.locator('button:has-text("Close")')).toBeVisible();

        // 2. Verify dashboard app shell components are absent
        await expect(page.locator('.app-sidebar, #sidebar, .navbar')).toHaveCount(0);

        // 3. Verify institutional header matching class-scheduling-frontend
        const header = page.locator('.cp-header');
        await expect(header).toBeVisible();
        await expect(header.locator('strong')).toContainText('GOLDEN WEST COLLEGES, INC.');
        await expect(header.locator('span')).toContainText('San Jose Drive, Alaminos City, Pangasinan');
        await expect(header.locator('small')).toContainText('Curriculum for');
        await expect(header.locator('h2')).toContainText('BACHELOR OF SCIENCE IN INFORMATION TECHNOLOGY');

        // Verify logos
        await expect(page.locator('.cp-logo-left')).toBeVisible();
        await expect(page.locator('.cp-logo-right')).toBeVisible();

        // 4. Verify Year blocks and absence of Fifth Year
        const yearBlocks = page.locator('.cp-year-block');
        const count = await yearBlocks.count();
        expect(count).toBeGreaterThanOrEqual(1);

        const firstYear = page.locator('.cp-year-title:has-text("FIRST YEAR")');
        await expect(firstYear).toBeVisible();

        // CRITICAL: FIFTH YEAR must NOT exist
        const fifthYear = page.locator('.cp-year-title:has-text("FIFTH YEAR")');
        await expect(fifthYear).toHaveCount(0);

        // Verify Semesters side-by-side
        await expect(page.locator('.cp-sem-title:has-text("FIRST SEMESTER")').first()).toBeVisible();
        await expect(page.locator('.cp-sem-title:has-text("SECOND SEMESTER")').first()).toBeVisible();

        // Verify Subjects table columns
        const subTable = page.locator('.cp-subjects').first();
        await expect(subTable.locator('th:has-text("SUBJECT CODE")')).toBeVisible();
        await expect(subTable.locator('th:has-text("DESCRIPTIVE TITLE")')).toBeVisible();
        await expect(subTable.locator('th:has-text("UNITS")')).toBeVisible();
        await expect(subTable.locator('th:has-text("PRE-REQUISITE")')).toBeVisible();

        // Verify total units
        const totalUnits = page.locator('.cp-total');
        await expect(totalUnits).toBeVisible();
        await expect(totalUnits).toContainText('TOTAL UNITS:');

        // 5. Verify print media hides the top preview bar
        await page.emulateMedia({ media: 'print' });
        const isHiddenInPrint = await noPrintBar.evaluate(el => window.getComputedStyle(el).display === 'none');
        expect(isHiddenInPrint, 'Top preview bar should be hidden in print mode').toBe(true);

        // Switch back to screen
        await page.emulateMedia({ media: 'screen' });

        // Capture screenshot of preview
        await page.screenshot({ path: 'test-results/curriculum-print-preview.png', fullPage: true });
    });
});
