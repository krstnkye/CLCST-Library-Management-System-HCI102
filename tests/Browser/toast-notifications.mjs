import assert from 'node:assert/strict';
import { createRequire } from 'node:module';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

const require = createRequire(import.meta.url);
const { chromium } = require('playwright');

const projectRoot = path.resolve(path.dirname(fileURLToPath(import.meta.url)), '..', '..');
const browser = await chromium.launch({
    headless: true,
    executablePath: process.env.CLCST_BROWSER_PATH || 'C:\\Program Files (x86)\\Microsoft\\Edge\\Application\\msedge.exe',
});

try {
    const page = await browser.newPage({ reducedMotion: 'reduce' });
    await page.setContent(`
        <!doctype html><html><body>
            <div hidden data-flash-toast data-toast-tone="success" data-toast-title="Book added">
                <span data-flash-message>Three physical copies were added.</span>
            </div>
        </body></html>
    `);
    await page.addScriptTag({ path: path.join(projectRoot, 'public', 'js', 'ui-actions.js') });

    const toast = page.locator('.ui-toast');
    await toast.waitFor({ state: 'visible' });
    assert.equal(await toast.getAttribute('role'), 'status');
    assert.equal(await toast.getAttribute('tabindex'), '0');
    assert.equal(await toast.locator('.ui-toast-title').innerText(), 'Book added');
    assert.equal(await toast.locator('.ui-toast-message').innerText(), 'Three physical copies were added.');

    const close = toast.getByRole('button', { name: 'Dismiss notification' });
    await close.focus();
    await close.press('Enter');
    await toast.waitFor({ state: 'detached' });
} finally {
    await browser.close();
}

console.log('Toast browser test passed.');
