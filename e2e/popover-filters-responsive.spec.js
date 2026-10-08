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

/**
 * Asserts that documentElement has zero horizontal overflow.
 */
async function assertNoHorizontalOverflow(page) {
    const overflow = await page.evaluate(() => {
        const scrollWidth = document.documentElement.scrollWidth;
        const clientWidth = document.documentElement.clientWidth;
        return {
            scrollWidth,
            clientWidth,
            hasOverflow: scrollWidth > clientWidth + 1,
        };
    });
    expect(overflow.hasOverflow, `Expected no horizontal scrollbar, but scrollWidth (${overflow.scrollWidth}px) > clientWidth (${overflow.clientWidth}px)`).toBe(false);
}

test.describe('Filter Popovers Responsiveness Across Viewports', () => {

    const viewports = [
        { name: 'Mobile (375x667)', width: 375, height: 667, isMobile: true },
        { name: 'Mobile (390x844)', width: 390, height: 844, isMobile: true },
        { name: 'Tablet (768x1024)', width: 768, height: 1024, isMobile: false },
        { name: 'Desktop (1280x800)', width: 1280, height: 800, isMobile: false },
    ];

    for (const vp of viewports) {
        test(`Audit Log popover filter is responsive on ${vp.name}`, async ({ page }) => {
            await page.setViewportSize({ width: vp.width, height: vp.height });
            await loginAs(page, 'admin@gwc.edu', 'Admin123!');

            await page.goto('admin/academic-terms?tab=audit-log');
            await page.waitForLoadState('networkidle');

            // Assert page itself has no initial horizontal scrollbar
            await assertNoHorizontalOverflow(page);

            const filterBtn = page.locator('#adminAuditLogFilterBtn');
            await expect(filterBtn).toBeVisible();
            await filterBtn.scrollIntoViewIfNeeded();

            // Click to open popover
            await filterBtn.click();

            const popover = page.locator('#adminAuditLogFilterForm .filter-popover-panel');
            await expect(popover).toBeVisible();

            // Ensure no horizontal overflow after opening popover
            await assertNoHorizontalOverflow(page);

            // Verify popover fits strictly inside viewport
            const box = await popover.boundingBox();
            expect(box, 'Popover bounding box should exist').not.toBeNull();
            if (box) {
                expect(box.x, 'Popover should not be clipped on the left').toBeGreaterThanOrEqual(-2);
                expect(box.x + box.width, 'Popover should not exceed right viewport edge').toBeLessThanOrEqual(vp.width + 2);
                expect(box.y + box.height, 'Popover should not exceed bottom viewport edge').toBeLessThanOrEqual(vp.height + 2);
            }

            // Verify all form controls inside popover are visible and accessible
            await expect(popover.locator('select[name="audit_action"]')).toBeVisible();
            await expect(popover.locator('select[name="audit_sy"]')).toBeVisible();
            await expect(popover.locator('select[name="audit_sem"]')).toBeVisible();
            await expect(popover.locator('input[name="audit_date_from"]')).toBeVisible();
            await expect(popover.locator('input[name="audit_date_to"]')).toBeVisible();

            // Verify Apply button is visible
            const applyBtn = popover.locator('button[type="submit"]');
            await expect(applyBtn).toBeVisible();

            // Verify Close button works cleanly
            const closeBtn = popover.locator('.filter-popover-footer button.btn-light');
            await expect(closeBtn).toBeVisible();
            await closeBtn.click();

            await expect(popover).not.toBeVisible();
        });

        test(`Students popover filter is responsive on ${vp.name}`, async ({ page }) => {
            await page.setViewportSize({ width: vp.width, height: vp.height });
            await loginAs(page, 'admin@gwc.edu', 'Admin123!');

            await page.goto('admin/students');
            await page.waitForLoadState('networkidle');

            await assertNoHorizontalOverflow(page);

            const filterBtn = page.locator('#adminStudentsFilterBtn');
            await expect(filterBtn).toBeVisible();
            await filterBtn.scrollIntoViewIfNeeded();

            await filterBtn.click();

            const popover = page.locator('#studentsFilterForm .filter-popover-panel');
            await expect(popover).toBeVisible();

            await assertNoHorizontalOverflow(page);

            const box = await popover.boundingBox();
            expect(box, 'Popover bounding box should exist').not.toBeNull();
            if (box) {
                expect(box.x, 'Popover should not be clipped on the left').toBeGreaterThanOrEqual(-2);
                expect(box.x + box.width, 'Popover should not exceed right viewport edge').toBeLessThanOrEqual(vp.width + 2);
                expect(box.y + box.height, 'Popover should not exceed bottom viewport edge').toBeLessThanOrEqual(vp.height + 2);
            }

            // Close button
            const closeBtn = popover.locator('.filter-popover-footer button.btn-light');
            await expect(closeBtn).toBeVisible();
            await closeBtn.click();

            await expect(popover).not.toBeVisible();
        });
    }

});
