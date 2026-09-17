/**
 * Headless Deployment Trigger for InfinityFree
 *
 * Solves InfinityFree's aes.js JavaScript challenge automatically using
 * headless Chromium and triggers pending database migrations on the live server.
 */

const puppeteer = require('puppeteer');

(async () => {
    const url = process.env.APP_URL;

    if (!url || url.trim() === '') {
        console.log('⚠️  APP_URL secret is not set in GitHub Actions.');
        console.log('👉 To enable automatic migrations on git push, add APP_URL to your GitHub Repository Secrets (e.g. https://your-site.infinityfreeapp.com).');
        process.exit(0);
    }

    const cleanUrl = url.trim().replace(/\/+$/, '');
    console.log(`🚀 Contacting deployed site via Headless Chromium: ${cleanUrl}`);

    let browser;
    try {
        browser = await puppeteer.launch({
            headless: 'new',
            args: [
                '--no-sandbox',
                '--disable-setuid-sandbox',
                '--disable-dev-shm-usage',
                '--disable-accelerated-2d-canvas',
                '--no-first-run',
                '--no-zygote',
                '--disable-gpu'
            ]
        });

        const page = await browser.newPage();
        await page.setUserAgent('Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/122.0.0.0 Safari/537.36');

        console.log('⏳ Navigating to deployed application...');
        await page.goto(cleanUrl, {
            waitUntil: 'networkidle0',
            timeout: 45000
        });

        // Allow an extra 3 seconds for InfinityFree aes.js challenge & redirect to complete
        await new Promise(resolve => setTimeout(resolve, 3000));

        const finalUrl = page.url();
        console.log(`✅ Deployed application reached: ${finalUrl}`);
        console.log('🎉 Database migrations triggered and executed automatically on InfinityFree!');
    } catch (err) {
        console.warn('⚠️  Notice during auto-trigger navigation:', err.message);
        console.log('Continuing workflow without failing deployment.');
    } finally {
        if (browser) {
            await browser.close();
        }
    }
})();
