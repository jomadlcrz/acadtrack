// @ts-check
const { test, expect } = require('@playwright/test');

/**
 * Helper to log in a user with specific credentials.
 */
async function loginAsAdmin(page) {
    await page.goto('login');
    await page.locator('#email').fill('admin@gwc.edu');
    await page.locator('#password').fill('Admin123!');
    await page.locator('button[type="submit"]').click();
    await page.waitForLoadState('networkidle');
}

test.describe('Premium Institutional Design Verification', () => {

    test('Collegiate Deep Navy Navbar with Gold Brand Accents', async ({ page }) => {
        await page.setViewportSize({ width: 1280, height: 800 });
        await loginAsAdmin(page);

        await expect(page).toHaveURL(/.*admin\/dashboard/);

        const navbar = page.locator('.app-navbar');
        await expect(navbar).toBeVisible();

        // Verify navbar styling
        const navbarStyles = await navbar.evaluate(el => {
            const style = window.getComputedStyle(el);
            return {
                backgroundImage: style.backgroundImage,
                backgroundColor: style.backgroundColor,
                boxShadow: style.boxShadow,
            };
        });

        // Ensure linear-gradient navy or deep blue background
        expect(navbarStyles.backgroundImage).toContain('gradient');

        // Brand title is white
        const brandTitle = page.locator('.navbar-brand-title');
        await expect(brandTitle).toBeVisible();
        const titleColor = await brandTitle.evaluate(el => window.getComputedStyle(el).color);
        expect(titleColor).toBe('rgb(255, 255, 255)');

        // Brand system subtitle is collegiate gold
        const brandSystem = page.locator('.navbar-brand-system');
        await expect(brandSystem).toBeVisible();
        const systemColor = await brandSystem.evaluate(el => window.getComputedStyle(el).color);
        // rgb(245, 158, 11) is #f59e0b
        expect(systemColor).toBe('rgb(245, 158, 11)');

        // Take a screenshot of the navigation bar
        await page.screenshot({ path: 'test-results/navbar-premium.png' });
    });

    test('Stat KPI Cards Have Semantic Color Top Accents & Tabular Numerals', async ({ page }) => {
        await page.setViewportSize({ width: 1280, height: 800 });
        await loginAsAdmin(page);

        const statCards = page.locator('.stat-card');
        const count = await statCards.count();
        expect(count).toBeGreaterThanOrEqual(4);

        // Check first card (Total Users - Blue)
        const blueCard = statCards.first();
        const blueBorderTop = await blueCard.evaluate(el => window.getComputedStyle(el).borderTopColor);
        // rgb(37, 99, 235) is #2563eb
        expect(blueBorderTop).toBe('rgb(37, 99, 235)');

        // Check values have tabular nums
        const statValue = blueCard.locator('.stat-value');
        const fontVariant = await statValue.evaluate(el => window.getComputedStyle(el).fontVariantNumeric);
        expect(fontVariant).toContain('tabular-nums');

        // Take a screenshot of the dashboard cards
        await page.screenshot({ path: 'test-results/dashboard-stat-cards.png' });
    });

    test('Sidebar Navigation Active State Has Collegiate Left Indicator', async ({ page }) => {
        await page.setViewportSize({ width: 1280, height: 800 });
        await loginAsAdmin(page);

        const activeLink = page.locator('.sidebar-link.active');
        await expect(activeLink).toBeVisible();

        const activeStyles = await activeLink.evaluate(el => {
            const style = window.getComputedStyle(el);
            return {
                backgroundColor: style.backgroundColor,
                borderLeftColor: style.borderLeftColor,
                borderLeftWidth: style.borderLeftWidth,
                color: style.color,
            };
        });

        // #eff6ff is rgb(239, 246, 255)
        expect(activeStyles.backgroundColor).toBe('rgb(239, 246, 255)');
        // border-left 3px solid #1e3a8a rgb(30, 58, 138)
        expect(activeStyles.borderLeftWidth).toBe('3px');
        expect(activeStyles.borderLeftColor).toBe('rgb(30, 58, 138)');
    });

    test('Tables and Cards Have Refined Sub-headers and Borders', async ({ page }) => {
        await page.setViewportSize({ width: 1280, height: 800 });
        await loginAsAdmin(page);

        await page.goto('admin/departments');
        await page.waitForLoadState('networkidle');

        const thead = page.locator('.table thead');
        await expect(thead).toBeVisible();

        const thColor = await page.locator('.table thead th').first().evaluate(el => {
            const style = window.getComputedStyle(el);
            return {
                backgroundColor: style.backgroundColor,
                color: style.color,
            };
        });

        // #f8fafc is rgb(248, 250, 252)
        expect(thColor.backgroundColor).toBe('rgb(248, 250, 252)');

        // Take a screenshot of the departments page
        await page.screenshot({ path: 'test-results/departments-table-premium.png' });
    });

});
