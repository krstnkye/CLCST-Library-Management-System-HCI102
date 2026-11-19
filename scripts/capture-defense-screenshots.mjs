import fs from 'node:fs/promises';
import path from 'node:path';
import { createRequire } from 'node:module';
import { fileURLToPath } from 'node:url';

const require = createRequire(import.meta.url);
const { chromium } = require('playwright');
const root = path.resolve(path.dirname(fileURLToPath(import.meta.url)), '..');
const output = path.join(root, 'build', 'defense-screenshots');
await fs.mkdir(output, { recursive: true });

const browser = await chromium.launch({
    headless: true,
    executablePath: process.env.CLCST_BROWSER_PATH || 'C:\\Program Files (x86)\\Microsoft\\Edge\\Application\\msedge.exe',
});

const baseUrl = process.env.CLCST_BASE_URL || 'http://127.0.0.1:8011';
const password = 'LibraryDemo2026!';
const accounts = {
    student: 'student@clcst.edu.ph',
    faculty: 'faculty@clcst.edu.ph',
    librarian: 'librarian@clcst.edu.ph',
    admin: 'admin@clcst.edu.ph',
    superadmin: 'superadmin@clcst.edu.ph',
};

async function signIn(page, role) {
    await page.goto(baseUrl, { waitUntil: 'networkidle' });
    await page.locator(`[data-role="${role}"]`).click();
    await page.locator('#identifier').fill(accounts[role]);
    await page.locator('#password').fill(password);
    await Promise.all([
        page.waitForURL((url) => url.pathname.includes('dashboard')),
        page.locator('button[type="submit"]').click(),
    ]);
    await page.waitForLoadState('networkidle');
}

try {
    const publicPage = await browser.newPage({ viewport: { width: 1440, height: 900 }, deviceScaleFactor: 1 });
    await publicPage.goto(baseUrl, { waitUntil: 'networkidle' });
    await publicPage.screenshot({ path: path.join(output, '01-sign-in.png') });
    await publicPage.close();

    for (const [index, role] of ['student', 'faculty', 'librarian', 'admin', 'superadmin'].entries()) {
        const page = await browser.newPage({ viewport: { width: 1440, height: 900 }, deviceScaleFactor: 1 });
        await signIn(page, role);
        if (role === 'student') {
            await page.evaluate(() => window.clcstUI?.toast('Your contact details were saved.', { title: 'Profile updated', tone: 'success', duration: 0 }));
        }
        await page.screenshot({ path: path.join(output, `${String(index + 2).padStart(2, '0')}-${role}-dashboard.png`) });
        if (role === 'admin') {
            await page.goto(`${baseUrl}/management/data`, { waitUntil: 'networkidle' });
            await page.screenshot({ path: path.join(output, '07-bulk-data.png') });
        }
        await page.close();
    }

    const mobile = await browser.newPage({ viewport: { width: 390, height: 844 }, deviceScaleFactor: 1 });
    await signIn(mobile, 'student');
    await mobile.screenshot({ path: path.join(output, '08-mobile-student.png') });
    await mobile.close();
} finally {
    await browser.close();
}

console.log(output);
