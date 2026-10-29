// CLCST library: pop-up dialogs, confirmations, action results, the barcode/QR scanner,
// e-Pass QR codes and the notification inbox. Shared by every portal; no build step.
(() => {
    const assetBase = (document.currentScript?.src || '').replace(/[^/]*$/, '');
    const csrf = () => document.querySelector('meta[name="csrf-token"]')?.content || '';
    const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    const el = (tag, className, html) => {
        const node = document.createElement(tag);
        if (className) node.className = className;
        if (html !== undefined) node.innerHTML = html;
        return node;
    };
    const icon = (name) => `<span class="material-symbols-outlined" aria-hidden="true">${name}</span>`;
    const escapeHtml = (text) => String(text ?? '').replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));

    const scripts = {};
    const loadScript = (file) => scripts[file] ??= new Promise((resolve, reject) => {
        const tag = document.createElement('script');
        tag.src = assetBase + file;
        tag.onload = resolve;
        tag.onerror = () => { delete scripts[file]; reject(new Error('Could not load ' + file)); };
        document.head.append(tag);
    });

    /* ---------- Modal dialog ---------- */
    const stack = [];
    const modal = ({ title = '', icon: iconName = 'info', tone = 'accent', body = '', size = '', actions = [], onClose, label } = {}) => {
        const lastFocus = document.activeElement;
        const root = el('div', `ui-modal ui-tone-${tone}`);
        const titleId = `ui-modal-title-${Date.now()}`;
        root.innerHTML = `<div class="ui-modal-panel ${size === 'wide' ? 'is-wide' : ''}" role="dialog" aria-modal="true" ${title ? `aria-labelledby="${titleId}"` : `aria-label="${escapeHtml(label || 'Dialog')}"`}>
            <button class="ui-modal-close" type="button" aria-label="Close">${icon('close')}</button>
            ${title ? `<header class="ui-modal-head"><span class="ui-modal-icon">${icon(iconName)}</span><h2 id="${titleId}">${escapeHtml(title)}</h2></header>` : ''}
            <div class="ui-modal-body"></div>
            <footer class="ui-modal-foot" hidden></footer></div>`;
        const panel = root.firstElementChild;
        const bodyEl = panel.querySelector('.ui-modal-body');
        if (body instanceof Node) bodyEl.append(body); else bodyEl.innerHTML = body;
        const foot = panel.querySelector('.ui-modal-foot');

        let closed = false;
        const close = (result) => {
            if (closed) return;
            closed = true;
            root.classList.remove('is-open');
            stack.splice(stack.indexOf(api), 1);
            if (!stack.length) document.documentElement.classList.remove('ui-modal-lock');
            setTimeout(() => root.remove(), reduceMotion ? 0 : 180);
            onClose?.(result);
            lastFocus?.focus?.({ preventScroll: true });
        };
        const api = { root, panel, body: bodyEl, close };

        actions.forEach((action) => {
            const tag = action.href ? 'a' : 'button';
            const button = el(tag, `ui-btn ui-btn-${action.variant || 'ghost'}`, `${action.icon ? icon(action.icon) : ''}<span>${escapeHtml(action.label)}</span>`);
            if (action.href) button.href = action.href; else button.type = 'button';
            button.addEventListener('click', (event) => {
                if (action.onClick) action.onClick(event, api);
                if (action.close !== false && !action.href) close(action.value);
            });
            foot.append(button);
        });
        foot.hidden = !actions.length;

        panel.querySelector('.ui-modal-close').addEventListener('click', () => close());
        root.addEventListener('mousedown', (event) => { if (event.target === root) close(); });
        root.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') { event.stopPropagation(); close(); }
            if (event.key !== 'Tab') return;
            const focusable = [...panel.querySelectorAll('a[href],button:not([disabled]),input:not([type=hidden]):not([disabled]),select,textarea,[tabindex]:not([tabindex="-1"])')].filter((node) => node.offsetParent !== null);
            if (!focusable.length) return;
            const [first, last] = [focusable[0], focusable[focusable.length - 1]];
            if (event.shiftKey && document.activeElement === first) { event.preventDefault(); last.focus(); }
            else if (!event.shiftKey && document.activeElement === last) { event.preventDefault(); first.focus(); }
        });

        document.body.append(root);
        document.documentElement.classList.add('ui-modal-lock');
        stack.push(api);
        requestAnimationFrame(() => {
            root.classList.add('is-open');
            const target = panel.querySelector('[autofocus]') || foot.querySelector('.ui-btn-primary, .ui-btn-danger') || panel.querySelector('.ui-modal-close');
            target?.focus({ preventScroll: true });
        });
        return api;
    };

    /* ---------- Toast ---------- */
    let toastHost;
    const toast = (message, tone = 'success') => {
        toastHost ??= document.body.appendChild(el('div', 'ui-toasts'));
        toastHost.setAttribute('aria-live', 'polite');
        const item = el('div', `ui-toast ui-tone-${tone}`, `${icon(tone === 'error' ? 'error' : 'check_circle')}<span>${escapeHtml(message)}</span>`);
        toastHost.append(item);
        requestAnimationFrame(() => item.classList.add('is-open'));
        setTimeout(() => { item.classList.remove('is-open'); setTimeout(() => item.remove(), 250); }, 3800);
    };

    window.clcstUI = { modal, toast };

    /* ---------- Confirmation before important actions ---------- */
    const confirmForms = () => {
        // Older markup used the browser's confirm(); move it to the styled dialog.
        document.querySelectorAll('form[onsubmit*="confirm("]').forEach((form) => {
            const match = form.getAttribute('onsubmit').match(/confirm\((['"])(.*)\1\)/);
            if (match && !form.dataset.confirm) form.dataset.confirm = match[2];
            form.removeAttribute('onsubmit');
        });

        document.addEventListener('submit', (event) => {
            const form = event.target;
            if (!(form instanceof HTMLFormElement) || !form.dataset.confirm || form.dataset.confirmed === '1') return;
            event.preventDefault();
            event.stopImmediatePropagation();
            const submitter = event.submitter;
            const danger = form.dataset.confirmTone === 'danger' || /cancel|remove|waive|suspend|delete|decline|archive/i.test(form.dataset.confirm + ' ' + (form.dataset.confirmLabel || ''));
            const detail = form.dataset.confirmDetail ? `<p class="ui-modal-detail">${escapeHtml(form.dataset.confirmDetail)}</p>` : '';
            modal({
                title: form.dataset.confirmTitle || (danger ? 'Are you sure?' : 'Please confirm'),
                icon: form.dataset.confirmIcon || (danger ? 'warning' : 'help'),
                tone: danger ? 'danger' : 'accent',
                body: `${detail}<p>${escapeHtml(form.dataset.confirm)}</p>`,
                actions: [
                    { label: form.dataset.cancelLabel || 'Go back', variant: 'ghost' },
                    {
                        label: form.dataset.confirmLabel || (danger ? 'Yes, continue' : 'Confirm'),
                        icon: danger ? 'check' : 'check_circle',
                        variant: danger ? 'danger' : 'primary',
                        onClick: () => {
                            form.dataset.confirmed = '1';
                            form.requestSubmit ? form.requestSubmit(submitter && submitter.form === form ? submitter : undefined) : form.submit();
                        },
                    },
                ],
            });
        }, true);
    };

    /* ---------- Busy state so an action is not sent twice ---------- */
    const busyForms = () => {
        document.addEventListener('submit', (event) => {
            const form = event.target;
            if (event.defaultPrevented || !(form instanceof HTMLFormElement) || (form.method || 'get').toLowerCase() !== 'post') return;
            if (form.matches('[data-notification-toggle], [data-notification-bulk]')) return;
            if (form.dataset.busy === '1') { event.preventDefault(); return; }
            form.dataset.busy = '1';
            const button = event.submitter || form.querySelector('button[type=submit], button:not([type])');
            if (button) {
                button.classList.add('ui-busy');
                button.setAttribute('aria-busy', 'true');
            }
        });
        window.addEventListener('pageshow', () => {
            document.querySelectorAll('form[data-busy]').forEach((form) => { delete form.dataset.busy; delete form.dataset.confirmed; });
            document.querySelectorAll('.ui-busy').forEach((button) => { button.classList.remove('ui-busy'); button.removeAttribute('aria-busy'); });
        });
    };

    /* ---------- Result pop-up after an action (reserve, renew, check in...) ---------- */
    const flashResult = () => {
        const flash = document.querySelector('[data-flash="error"]') || document.querySelector('[data-flash="success"]');
        if (!flash || performance.getEntriesByType?.('navigation')[0]?.type === 'back_forward') return;
        const success = flash.dataset.flash === 'success';
        const message = flash.querySelector('[data-flash-message]');
        const lines = message.querySelectorAll('p').length ? [...message.querySelectorAll('p')].map((p) => p.textContent.trim()) : [message.textContent.trim()];
        const actions = [];
        if (flash.dataset.flashLink) actions.push({ label: flash.dataset.flashLinkLabel || 'View', href: flash.dataset.flashLink, variant: 'ghost', icon: 'arrow_forward' });
        actions.push({ label: success ? 'Done' : 'OK, got it', variant: success ? 'primary' : 'danger' });
        modal({
            title: flash.dataset.flashTitle || (success ? 'Success' : 'That did not go through'),
            icon: success ? 'check_circle' : 'error',
            tone: success ? 'success' : 'danger',
            body: `<div class="ui-result ${success ? 'is-success' : 'is-error'}"><div class="ui-result-mark" aria-hidden="true">${icon(success ? 'check' : 'priority_high')}</div>${lines.map((line) => `<p>${escapeHtml(line)}</p>`).join('')}</div>`,
            actions,
        });
    };

    /* ---------- Content pop-ups from <template data-modal="..."> ---------- */
    const templateModals = () => {
        document.addEventListener('click', (event) => {
            const trigger = event.target.closest('[data-modal-open]');
            if (!trigger) return;
            const template = document.querySelector(`template[data-modal="${CSS.escape(trigger.dataset.modalOpen)}"]`);
            if (!template) return;
            event.preventDefault();
            modal({
                title: template.dataset.modalTitle,
                icon: template.dataset.modalIcon || 'info',
                size: template.dataset.modalSize,
                body: template.content.cloneNode(true),
            });
        });
    };

    /* ---------- Document preview (uploaded IDs) ---------- */
    const previews = () => {
        document.addEventListener('click', (event) => {
            const link = event.target.closest('a[data-preview]');
            if (!link || event.ctrlKey || event.metaKey || event.shiftKey) return;
            event.preventDefault();
            const frame = el('div', 'ui-preview', `<iframe src="${escapeHtml(link.href)}" title="${escapeHtml(link.dataset.previewTitle || 'Document preview')}"></iframe>`);
            modal({
                title: link.dataset.previewTitle || 'Document preview',
                icon: 'badge',
                size: 'wide',
                body: frame,
                actions: [
                    { label: 'Open in a new tab', href: link.href, variant: 'ghost', icon: 'open_in_new' },
                    { label: 'Close', variant: 'primary' },
                ],
            }).panel.querySelector('.ui-modal-foot a')?.setAttribute('target', '_blank');
        }, true);
    };

    /* ---------- Copy to clipboard ---------- */
    const copyButtons = () => {
        document.addEventListener('click', async (event) => {
            const button = event.target.closest('[data-copy]');
            if (!button) return;
            try {
                await navigator.clipboard.writeText(button.dataset.copy);
            } catch {
                const area = el('textarea');
                area.value = button.dataset.copy;
                document.body.append(area);
                area.select();
                document.execCommand('copy');
                area.remove();
            }
            const label = button.innerHTML;
            button.innerHTML = `${icon('check')}Copied`;
            button.classList.add('is-copied');
            setTimeout(() => { button.innerHTML = label; button.classList.remove('is-copied'); }, 1600);
        });
    };

    /* ---------- QR codes (library e-Pass) ---------- */
    const renderQr = async (node) => {
        try {
            await loadScript('vendor/qrcode-generator.js');
            const qr = window.qrcode(0, 'M');
            qr.addData(node.dataset.qr);
            qr.make();
            node.innerHTML = qr.createSvgTag({ cellSize: 4, margin: 2, scalable: true, alt: node.dataset.qrLabel || 'QR code' });
        } catch {
            node.innerHTML = `<span class="ui-qr-fallback">${escapeHtml(node.dataset.qr)}</span>`;
        }
    };
    const qrCodes = () => {
        document.querySelectorAll('[data-qr]').forEach(renderQr);
        document.addEventListener('click', (event) => {
            const trigger = event.target.closest('[data-epass]');
            if (!trigger) return;
            event.preventDefault();
            const { epassName: name, epassId: id, epassProgram: program, epassRole: role } = trigger.dataset;
            const card = el('div', 'ui-epass', `<div class="ui-epass-card">
                    <div class="ui-epass-top"><span>CLCST e-Pass</span><span>${escapeHtml(role || 'Library patron')}</span></div>
                    <div class="ui-epass-qr" data-qr="${escapeHtml(id)}" data-qr-label="QR code for ${escapeHtml(id)}"></div>
                    <p class="ui-epass-name">${escapeHtml(name)}</p>
                    <p class="ui-epass-id">${escapeHtml(id)}</p>
                    ${program ? `<p class="ui-epass-program">${escapeHtml(program)}</p>` : ''}
                </div>
                <p class="ui-modal-note">Show this code at the circulation desk. The librarian scans it to find your account when you borrow a book.</p>`);
            modal({
                title: 'My library e-Pass',
                icon: 'badge',
                body: card,
                actions: [
                    { label: 'Open my account', href: trigger.getAttribute('href'), variant: 'ghost', icon: 'manage_accounts' },
                    { label: 'Close', variant: 'primary' },
                ],
            });
            card.querySelectorAll('[data-qr]').forEach(renderQr);
        }, true);
    };

    /* ---------- Barcode and QR scanner ---------- */
    const beep = () => {
        try {
            const audio = new (window.AudioContext || window.webkitAudioContext)();
            const osc = audio.createOscillator();
            const gain = audio.createGain();
            osc.frequency.value = 1320;
            gain.gain.setValueAtTime(0.12, audio.currentTime);
            gain.gain.exponentialRampToValueAtTime(0.0001, audio.currentTime + 0.16);
            osc.connect(gain).connect(audio.destination);
            osc.start();
            osc.stop(audio.currentTime + 0.17);
        } catch { /* sound is optional */ }
        navigator.vibrate?.(60);
    };

    const openScanner = (trigger) => {
        const targetSelector = trigger.dataset.scanTarget;
        const target = targetSelector ? document.querySelector(targetSelector) : null;
        const purpose = trigger.dataset.scanPurpose || (target ? 'field' : 'catalog');
        const wrap = el('div', 'ui-scan', `
            <div class="ui-scan-stage">
                <div class="ui-scan-reader" id="ui-scan-reader-${Date.now()}"></div>
                <div class="ui-scan-frame" aria-hidden="true"><i></i><i></i><i></i><i></i><span class="ui-scan-laser"></span></div>
                <div class="ui-scan-status" role="status">${icon('photo_camera')}<span>Starting the camera...</span></div>
                <div class="ui-scan-hit" hidden>${icon('check_circle')}<strong></strong><span>Code captured</span></div>
            </div>
            <div class="ui-scan-tools">
                <label class="ui-scan-camera" hidden>${icon('cameraswitch')}<span class="sr-only">Camera</span><select></select></label>
                <label class="ui-btn ui-btn-ghost ui-scan-file">${icon('image')}<span>Scan from a photo</span><input type="file" accept="image/*" capture="environment" class="sr-only"></label>
            </div>
            <form class="ui-scan-manual">
                <label class="sr-only" for="ui-scan-manual-input">Type the code</label>
                <input id="ui-scan-manual-input" autocomplete="off" placeholder="No camera? Type the barcode, ISBN or ID">
                <button class="ui-btn ui-btn-primary" type="submit">${icon('keyboard_return')}<span>Use code</span></button>
            </form>
            <p class="ui-modal-note">Point the camera at a book barcode (ISBN), a copy barcode sticker, or a QR code such as a library e-Pass. Hold it steady about 15 cm away.</p>`);

        const stage = wrap.querySelector('.ui-scan-stage');
        const reader = wrap.querySelector('.ui-scan-reader');
        const status = wrap.querySelector('.ui-scan-status');
        const setStatus = (text, iconName = 'info', tone = '') => {
            status.className = `ui-scan-status ${tone}`;
            status.innerHTML = `${icon(iconName)}<span>${escapeHtml(text)}</span>`;
            status.hidden = false;
        };
        let scanner = null;
        let done = false;

        const stop = async () => {
            if (scanner?.isScanning) { try { await scanner.stop(); } catch { /* already stopped */ } }
            try { scanner?.clear(); } catch { /* nothing to clear */ }
        };

        const apply = async (raw) => {
            const code = String(raw || '').trim();
            if (!code || done) return;
            done = true;
            beep();
            stage.classList.add('is-hit');
            const hit = wrap.querySelector('.ui-scan-hit');
            hit.querySelector('strong').textContent = code;
            hit.hidden = false;
            status.hidden = true;
            await stop();
            setTimeout(() => {
                dialog.close('scanned');
                if (target) {
                    target.value = code;
                    target.dispatchEvent(new Event('input', { bubbles: true }));
                    const form = target.form;
                    const field = form?.querySelector('[name="field"]');
                    if (field && trigger.dataset.scanField) field.value = trigger.dataset.scanField;
                    if (trigger.hasAttribute('data-scan-submit') && form) form.requestSubmit ? form.requestSubmit() : form.submit();
                    else target.focus();
                } else if (purpose === 'catalog') {
                    const url = new URL(trigger.dataset.scanUrl || '/books/catalog', location.origin);
                    url.searchParams.set('q', code);
                    location.href = url.toString();
                }
            }, 650);
        };

        const dialog = modal({
            title: trigger.dataset.scanTitle || 'Scan a barcode or QR code',
            icon: 'barcode_scanner',
            size: 'wide',
            body: wrap,
            onClose: () => { done = true; stop(); },
        });

        wrap.querySelector('.ui-scan-manual').addEventListener('submit', (event) => {
            event.preventDefault();
            const value = wrap.querySelector('#ui-scan-manual-input').value;
            if (value.trim()) apply(value);
        });

        const formats = () => {
            const f = window.Html5QrcodeSupportedFormats;
            return f ? [f.QR_CODE, f.EAN_13, f.EAN_8, f.UPC_A, f.UPC_E, f.CODE_128, f.CODE_39, f.CODE_93, f.CODABAR, f.ITF, f.DATA_MATRIX] : undefined;
        };

        const start = async (cameraId) => {
            await stop();
            if (done) return;
            setStatus('Starting the camera...', 'photo_camera');
            try {
                await scanner.start(
                    cameraId ? { deviceId: { exact: cameraId } } : { facingMode: 'environment' },
                    { fps: 12, disableFlip: false },
                    (text) => apply(text),
                    () => { /* no code in this frame yet */ },
                );
                status.hidden = true;
                stage.classList.add('is-live');
            } catch (error) {
                stage.classList.remove('is-live');
                const name = error?.name || String(error);
                if (/NotAllowed|Permission/i.test(name)) setStatus('Camera access was blocked. Allow the camera from the icon in the address bar, or scan a photo or type the code below.', 'no_photography', 'is-error');
                else if (/NotFound|Overconstrained|DevicesNotFound/i.test(name)) setStatus('No camera was found on this device. Scan a photo of the barcode or type the code below.', 'videocam_off', 'is-error');
                else if (/NotReadable|TrackStart/i.test(name)) setStatus('The camera is being used by another app. Close it and try again, or type the code below.', 'videocam_off', 'is-error');
                else setStatus('The camera could not start. Scan a photo of the barcode or type the code below.', 'videocam_off', 'is-error');
            }
        };

        wrap.querySelector('.ui-scan-file input').addEventListener('change', async (event) => {
            const file = event.target.files?.[0];
            if (!file || !scanner) return;
            await stop();
            setStatus('Reading the photo...', 'image_search');
            try {
                const result = await scanner.scanFile(file, false);
                apply(result);
            } catch {
                setStatus('No barcode or QR code was found in that photo. Try a sharper, closer photo or type the code.', 'image_not_supported', 'is-error');
            }
            event.target.value = '';
        });

        if (!window.isSecureContext) {
            setStatus('The camera only works on a secure page (https or localhost). You can still scan a photo or type the code.', 'lock', 'is-error');
        }

        loadScript('vendor/html5-qrcode.min.js').then(async () => {
            if (done) return;
            scanner = new window.Html5Qrcode(reader.id, { formatsToSupport: formats(), useBarCodeDetectorIfSupported: true, verbose: false });
            if (!window.isSecureContext || !navigator.mediaDevices?.getUserMedia) return;
            await start();
            try {
                const cameras = await window.Html5Qrcode.getCameras();
                if (cameras.length > 1 && !done) {
                    const picker = wrap.querySelector('.ui-scan-camera');
                    const select = picker.querySelector('select');
                    select.innerHTML = cameras.map((camera, index) => `<option value="${escapeHtml(camera.id)}">${escapeHtml(camera.label || 'Camera ' + (index + 1))}</option>`).join('');
                    select.addEventListener('change', () => start(select.value));
                    picker.hidden = false;
                }
            } catch { /* the camera list is optional */ }
        }).catch(() => setStatus('The scanner could not load. Type the code below instead.', 'error', 'is-error'));
    };

    const scanners = () => {
        document.addEventListener('click', (event) => {
            const trigger = event.target.closest('[data-scan]');
            if (!trigger) return;
            event.preventDefault();
            openScanner(trigger);
        });
    };

    /* ---------- Notification inbox ---------- */
    const notifications = () => {
        const center = document.querySelector('[data-notification-center]');
        const items = () => [...document.querySelectorAll('[data-notification]')];
        let filter = 'all';

        const setBadges = (unread) => {
            document.querySelectorAll('[data-notification-badge]').forEach((badge) => { badge.textContent = unread; badge.hidden = unread < 1; });
            document.querySelectorAll('[data-unread-count]').forEach((count) => { count.textContent = unread; });
            document.querySelectorAll('a[aria-label^="Notifications ("]').forEach((link) => link.setAttribute('aria-label', `Notifications (${unread})`));
            if (!center) return;
            const total = items().length;
            center.querySelector('[data-notification-bulk="read"] button').disabled = unread < 1;
            center.querySelector('[data-notification-bulk="unread"] button').disabled = total === 0 || unread === total;
            const ring = center.querySelector('.nc-ring');
            ring?.style.setProperty('--nc-progress', `${total ? Math.round((total - unread) / total * 100) : 100}%`);
            ring?.querySelector('.material-symbols-outlined')?.replaceChildren(unread ? 'notifications_active' : 'notifications');
        };

        const markItem = (item, read) => {
            item.classList.toggle('is-read', read);
            item.classList.toggle('is-unread', !read);
            const state = item.querySelector('[data-notification-state]');
            if (state) state.textContent = read ? 'Read' : 'New';
            const toggle = item.querySelector('[data-notification-toggle]');
            if (toggle) {
                toggle.action = read ? item.dataset.unreadUrl : item.dataset.readUrl;
                const button = toggle.querySelector('button');
                button.title = button.ariaLabel = read ? 'Mark as unread' : 'Mark as read';
                button.querySelector('.material-symbols-outlined').textContent = read ? 'mark_email_unread' : 'mark_email_read';
            }
        };

        const send = async (url) => {
            const response = await fetch(url, { method: 'POST', headers: { 'X-CSRF-TOKEN': csrf(), Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' }, credentials: 'same-origin' });
            if (!response.ok) throw new Error(String(response.status));
            return response.json();
        };

        const applyFilter = () => {
            if (!center) return;
            let shown = 0;
            items().forEach((item) => {
                const visible = filter === 'all' || (filter === 'unread' ? item.classList.contains('is-unread') : item.dataset.group === filter);
                item.hidden = !visible;
                if (visible) shown++;
            });
            center.querySelectorAll('[data-notification-day]').forEach((day) => { day.hidden = !day.querySelector('[data-notification]:not([hidden])'); });
            const empty = center.querySelector('[data-notification-filter-empty]');
            if (empty) empty.hidden = shown > 0 || items().length === 0;
        };

        const setRead = async (item, read, quiet = false) => {
            const before = item.classList.contains('is-read');
            markItem(item, read);
            try {
                const data = await send(read ? item.dataset.readUrl : item.dataset.unreadUrl);
                setBadges(data.unread);
                if (!quiet) toast(data.message);
                if (filter === 'unread') applyFilter();
            } catch {
                markItem(item, before);
                toast('Could not update that notification. Please try again.', 'error');
            }
        };

        const openDetail = (item) => {
            const template = item.querySelector('template[data-notification-detail]');
            const tone = [...item.classList].find((c) => c.startsWith('nc-tone-'))?.replace('nc-tone-', '') || 'info';
            const read = () => item.classList.contains('is-read');
            const body = el('div', 'nc-detail-wrap');
            body.innerHTML = `<span class="nc-chip nc-detail-chip">${escapeHtml(item.querySelector('.nc-chip')?.textContent)}</span>`;
            body.append(template.content.cloneNode(true));
            const dialog = modal({
                title: item.querySelector('.nc-title').textContent.trim(),
                icon: item.querySelector('.nc-icon').textContent.trim(),
                tone: { error: 'danger', warning: 'warning', success: 'success' }[tone] || 'accent',
                size: 'wide',
                body,
                actions: item.dataset.readUrl ? [
                    { label: 'Mark as unread', icon: 'mark_email_unread', variant: 'ghost', onClick: () => setRead(item, false) },
                    { label: 'Close', variant: 'primary' },
                ] : [{ label: 'Close', variant: 'primary' }],
            });
            dialog.panel.classList.add('nc-modal');
            if (!read()) setRead(item, true, true);
        };

        document.addEventListener('click', (event) => {
            const open = event.target.closest('[data-notification-open]');
            if (open) { event.preventDefault(); openDetail(open.closest('[data-notification]')); return; }
            const tab = event.target.closest('[data-notification-filter]');
            if (tab) {
                filter = tab.dataset.notificationFilter;
                center.querySelectorAll('[data-notification-filter]').forEach((other) => other.setAttribute('aria-selected', String(other === tab)));
                applyFilter();
            }
        });

        document.addEventListener('submit', async (event) => {
            const toggle = event.target.closest('[data-notification-toggle]');
            const bulk = event.target.closest('[data-notification-bulk]');
            if (!toggle && !bulk) return;
            event.preventDefault();
            if (toggle) {
                const item = toggle.closest('[data-notification]');
                setRead(item, !item.classList.contains('is-read'));
                return;
            }
            const read = bulk.dataset.notificationBulk === 'read';
            const button = bulk.querySelector('button');
            button.classList.add('ui-busy');
            try {
                const data = await send(bulk.action);
                items().forEach((item) => markItem(item, read));
                setBadges(data.unread);
                applyFilter();
                center.classList.remove('nc-flash');
                void center.offsetWidth;
                center.classList.add('nc-flash');
                toast(data.message);
            } catch {
                toast('Could not update your notifications. Please try again.', 'error');
            } finally {
                button.classList.remove('ui-busy');
            }
        }, true);
    };

    const initialize = () => {
        confirmForms();
        busyForms();
        templateModals();
        previews();
        copyButtons();
        qrCodes();
        scanners();
        notifications();
        flashResult();
    };
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', initialize, { once: true });
    else initialize();
})();
