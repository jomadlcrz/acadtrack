// @ts-check
const { test, expect } = require('@playwright/test');

/**
 * Helper to log in a user with specific credentials.
 */
async function loginAs(page, email, password) {
    await page.goto('login');
    await page.locator('#email').fill(email);
    await page.locator('#password').fill(password);
    await page.locator('button[type="submit"]').click();
    await page.waitForLoadState('networkidle');
}

/**
 * Asserts that the documentElement has no horizontal overflow beyond the client width.
 */
async function assertNoHorizontalOverflow(page) {
    const overflow = await page.evaluate(() => {
        const scrollWidth = document.documentElement.scrollWidth;
        const clientWidth = document.documentElement.clientWidth;
        return {
            scrollWidth,
            clientWidth,
            hasOverflow: scrollWidth > clientWidth + 1, // allow 1px rounding tolerance
        };
    });
    expect(overflow.hasOverflow, `Expected no horizontal scrollbar, but scrollWidth (${overflow.scrollWidth}px) > clientWidth (${overflow.clientWidth}px)`).toBe(false);
}

test.describe('Portal Pages Responsiveness', () => {

    test('Mobile viewport (375x667): Admin Dashboard & Off-Canvas Sidebar Navigation', async ({ page }) => {
        await page.setViewportSize({ width: 375, height: 667 });
        await loginAs(page, 'admin@gwc.edu', 'Admin123!');

        await expect(page).toHaveURL(/.*dashboard/);

        // Verify zero horizontal scrollbar on root
        await assertNoHorizontalOverflow(page);

        // Sidebar toggle button must be visible on mobile
        const toggleBtn = page.locator('#sidebarToggle');
        await expect(toggleBtn).toBeVisible();

        const sidebar = page.locator('#appSidebar');
        const backdrop = page.locator('#sidebarBackdrop');

        // Initially sidebar is off-screen
        const initialBox = await sidebar.boundingBox();
        expect(initialBox === null || (initialBox.x + initialBox.width) <= 0).toBe(true);

        // Tap toggle to open sidebar
        await toggleBtn.click();
        await expect(page.locator('body')).toHaveClass(/sidebar-open/);

        // Wait for slide-in transition to settle
        await page.waitForFunction(() => {
            const el = document.getElementById('appSidebar');
            if (!el) return false;
            const rect = el.getBoundingClientRect();
            return Math.abs(rect.left) < 2;
        });

        // Close sidebar via the close button
        const closeBtn = page.locator('#sidebarCloseBtn');
        await expect(closeBtn).toBeVisible();
        await closeBtn.click();
        await expect(page.locator('body')).not.toHaveClass(/sidebar-open/);

        // Wait for slide-out transition to settle
        await page.waitForFunction(() => {
            const el = document.getElementById('appSidebar');
            if (!el) return false;
            const rect = el.getBoundingClientRect();
            return rect.right <= 0;
        });

        // Re-open and test closing via backdrop click
        await toggleBtn.click();
        await expect(page.locator('body')).toHaveClass(/sidebar-open/);
        await page.waitForFunction(() => {
            const el = document.getElementById('appSidebar');
            if (!el) return false;
            const rect = el.getBoundingClientRect();
            return Math.abs(rect.left) < 2;
        });

        await backdrop.click({ position: { x: 350, y: 100 } });
        await expect(page.locator('body')).not.toHaveClass(/sidebar-open/);

        // Wait for slide-out transition to settle
        await page.waitForFunction(() => {
            const el = document.getElementById('appSidebar');
            if (!el) return false;
            const rect = el.getBoundingClientRect();
            return rect.right <= 0;
        });

        // Final overflow check
        await assertNoHorizontalOverflow(page);
    });

    test('Mobile viewport (375x667): Admin Departments Table with responsive container', async ({ page }) => {
        await page.setViewportSize({ width: 375, height: 667 });
        await loginAs(page, 'admin@gwc.edu', 'Admin123!');

        await page.goto('admin/departments');
        await page.waitForLoadState('networkidle');

        await expect(page.locator('h1')).toContainText('Departments');

        // Ensure table container allows horizontal scrolling internally without breaking the page layout
        const tableResponsive = page.locator('.table-responsive');
        await expect(tableResponsive.first()).toBeVisible();

        await assertNoHorizontalOverflow(page);
    });

    test('Mobile viewport (375x667): Dean Grade Review Queue', async ({ page }) => {
        await page.setViewportSize({ width: 375, height: 667 });
        await loginAs(page, 'dean@gwc.edu', 'Admin123!');

        await page.goto('dean/grade-review');
        await page.waitForLoadState('networkidle');

        await expect(page.locator('h1')).toContainText('Grade Review');

        // Summary strip should fit without page overflow
        const summaryStrip = page.locator('.summary-strip');
        if (await summaryStrip.count() > 0) {
            await expect(summaryStrip.first()).toBeVisible();
        }

        await assertNoHorizontalOverflow(page);
    });

    test('Mobile viewport (375x667): Faculty Dashboard', async ({ page }) => {
        await page.setViewportSize({ width: 375, height: 667 });
        await loginAs(page, 'faculty@gwc.edu', 'Admin123!');

        await expect(page).toHaveURL(/.*faculty\/dashboard/);
        await expect(page.locator('h1')).toBeVisible();

        await assertNoHorizontalOverflow(page);
    });

    test('Mobile viewport (375x667): Student Dashboard', async ({ page }) => {
        await page.setViewportSize({ width: 375, height: 667 });
        await loginAs(page, 'student@gwc.edu', 'Admin123!');

        await expect(page).toHaveURL(/.*student\/dashboard/);
        await expect(page.locator('h1')).toBeVisible();

        await assertNoHorizontalOverflow(page);
    });

    test('Tablet viewport (768x1024): Admin Dashboard & Layout', async ({ page }) => {
        await page.setViewportSize({ width: 768, height: 1024 });
        await loginAs(page, 'admin@gwc.edu', 'Admin123!');

        await expect(page).toHaveURL(/.*dashboard/);

        // Sidebar toggle is visible on tablet (< 992px)
        await expect(page.locator('#sidebarToggle')).toBeVisible();

        await assertNoHorizontalOverflow(page);
    });

    test('Desktop viewport (1280x800): Standard fixed sidebar & no toggle button', async ({ page }) => {
        await page.setViewportSize({ width: 1280, height: 800 });
        await loginAs(page, 'admin@gwc.edu', 'Admin123!');

        await expect(page).toHaveURL(/.*dashboard/);

        // Sidebar toggle button is hidden on desktop
        await expect(page.locator('#sidebarToggle')).toBeHidden();

        // Sidebar is in standard flow
        const sidebar = page.locator('#appSidebar');
        await expect(sidebar).toBeVisible();
        const box = await sidebar.boundingBox();
        expect(box.x).toBe(0);

        await assertNoHorizontalOverflow(page);
    });

});
