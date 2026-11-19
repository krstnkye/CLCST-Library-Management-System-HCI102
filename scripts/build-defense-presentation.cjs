const path = require('path');
const fs = require('fs');
const pptxgen = require('pptxgenjs');
const sharp = require('sharp');
const JSZip = require('jszip');

const root = path.resolve(__dirname, '..');
const shots = path.join(root, 'build', 'defense-screenshots');
const outDir = path.join(root, 'presentation');
const outFile = path.join(outDir, 'CLCST-Library-Management-System-HCI102-Defense.pptx');
const logo = path.join(root, 'public', 'images', 'celtech-logo.png');

fs.mkdirSync(outDir, { recursive: true });

const pptx = new pptxgen();
pptx.layout = 'LAYOUT_WIDE';
pptx.author = 'CLCST Library Management System Project Team';
pptx.subject = 'HCI102 Project Defense';
pptx.title = 'CLCST Library Management System';
pptx.company = 'Central Luzon College of Science and Technology';
pptx.lang = 'en-PH';
pptx.theme = {
  headFontFace: 'Georgia',
  bodyFontFace: 'Aptos',
  lang: 'en-PH',
};

const C = {
  green: '0D4A34',
  green2: '176245',
  green3: 'DDEDE4',
  brass: 'B58A2A',
  gold: 'D8BC7C',
  cream: 'F6F3EC',
  paper: 'FBFAF7',
  ink: '17251F',
  muted: '66766E',
  line: 'D8DED9',
  white: 'FFFFFF',
  red: 'A53F3F',
  amber: 'B36A0B',
  blue: '2A5C94',
};

const SW = 13.333;
const SH = 7.5;

async function repairGeneratedContentTypes(file) {
  const zip = await JSZip.loadAsync(fs.readFileSync(file));
  const contentTypes = zip.file('[Content_Types].xml');
  let xml = await contentTypes.async('string');

  // PptxGenJS can declare one slide master per slide even though the package
  // intentionally contains only the shared first master. Remove only those
  // declarations whose target file does not exist.
  xml = xml.replace(/<Override PartName="\/ppt\/slideMasters\/slideMaster(\d+)\.xml"[^>]*\/>/g, (entry, number) => {
    return zip.file(`ppt/slideMasters/slideMaster${number}.xml`) ? entry : '';
  });

  zip.file('[Content_Types].xml', xml);
  fs.writeFileSync(file, await zip.generateAsync({ type: 'nodebuffer', compression: 'DEFLATE' }));
}

function addNotes(slide, notes) {
  slide.addNotes(notes.join('\n'));
}

function base(slide, section, page, dark = false) {
  slide.background = { color: dark ? C.green : C.paper };
  slide.addShape(pptx.ShapeType.line, { x: 0.55, y: 0.45, w: 0.7, h: 0, line: { color: dark ? C.gold : C.brass, width: 2 } });
  slide.addText(section.toUpperCase(), { x: 1.38, y: 0.3, w: 5.5, h: 0.3, fontFace: 'Aptos', fontSize: 10, bold: true, charSpacing: 2.1, color: dark ? C.gold : C.green2, margin: 0 });
  slide.addText(String(page).padStart(2, '0'), { x: 12.2, y: 0.28, w: 0.55, h: 0.3, fontFace: 'Aptos', fontSize: 9, color: dark ? 'C9D8D0' : C.muted, align: 'right', margin: 0 });
  slide.addText('CLCST LIBRARY MANAGEMENT SYSTEM  ·  HCI102', { x: 0.55, y: 7.12, w: 6.2, h: 0.18, fontFace: 'Aptos', fontSize: 7.5, color: dark ? 'BFD0C7' : '78877F', charSpacing: 1.15, margin: 0 });
}

function title(slide, text, subtitle, opts = {}) {
  slide.addText(text, { x: opts.x ?? 0.72, y: opts.y ?? 0.82, w: opts.w ?? 11.8, h: opts.h ?? 0.5, fontFace: 'Georgia', fontSize: opts.size ?? 27, bold: false, color: opts.dark ? C.white : C.ink, margin: 0, breakLine: false, fit: 'shrink' });
  if (subtitle) slide.addText(subtitle, { x: opts.x ?? 0.72, y: (opts.y ?? 0.82) + (opts.h ?? 0.5) + 0.04, w: opts.w ?? 11.4, h: 0.28, fontFace: 'Aptos', fontSize: 11.5, color: opts.dark ? 'C7D8CF' : C.muted, margin: 0, fit: 'shrink' });
}

function card(slide, x, y, w, h, options = {}) {
  slide.addShape(pptx.ShapeType.roundRect, {
    x, y, w, h,
    rectRadius: 0.08,
    fill: { color: options.fill || C.white, transparency: options.transparency || 0 },
    line: { color: options.line || C.line, width: options.width || 1 },
    shadow: options.shadow === false ? undefined : { type: 'outer', color: '7F8A84', opacity: 0.12, blur: 1.5, angle: 45, distance: 1 },
  });
}

function label(slide, text, x, y, w, color = C.green, fill = C.green3) {
  slide.addText(text.toUpperCase(), { x, y, w, h: 0.28, fontFace: 'Aptos', fontSize: 8.5, bold: true, charSpacing: 1.1, color, fill: { color: fill }, margin: 0.08, align: 'center', valign: 'mid', fit: 'shrink' });
}

function bulletList(slide, items, x, y, w, h, opts = {}) {
  const runs = [];
  items.forEach((item, i) => {
    runs.push({ text: item, options: { bullet: { indent: 14 }, hanging: 3, breakLine: i < items.length - 1 } });
  });
  slide.addText(runs, { x, y, w, h, fontFace: 'Aptos', fontSize: opts.size || 15, color: opts.color || C.ink, breakLine: false, margin: 0.05, paraSpaceAfterPt: opts.space || 11, valign: 'top', fit: 'shrink' });
}

function metric(slide, value, name, x, y, w, accent = C.green) {
  slide.addText(value, { x, y, w, h: 0.5, fontFace: 'Georgia', fontSize: 27, color: accent, margin: 0, align: 'center', fit: 'shrink' });
  slide.addText(name, { x, y: y + 0.52, w, h: 0.38, fontFace: 'Aptos', fontSize: 10.5, bold: true, color: C.ink, margin: 0, align: 'center', fit: 'shrink' });
}

async function imageContain(file, x, y, w, h) {
  const m = await sharp(file).metadata();
  const r = m.width / m.height;
  const tr = w / h;
  if (r > tr) {
    const ih = w / r;
    return { path: file, x, y: y + (h - ih) / 2, w, h: ih };
  }
  const iw = h * r;
  return { path: file, x: x + (w - iw) / 2, y, w: iw, h };
}

async function screenshot(slide, file, x, y, w, h, options = {}) {
  card(slide, x, y, w, h, { fill: options.fill || C.white, line: options.line || 'CBD5CF', shadow: true });
  const inset = options.inset ?? 0.08;
  const img = await imageContain(file, x + inset, y + inset, w - inset * 2, h - inset * 2);
  slide.addImage({ ...img, altText: options.alt || 'Project interface screenshot' });
}

function roleRow(slide, y, role, purpose, rights, accent) {
  slide.addShape(pptx.ShapeType.rect, { x: 0.8, y, w: 0.07, h: 0.65, fill: { color: accent }, line: { color: accent } });
  slide.addText(role, { x: 1.03, y: y + 0.02, w: 1.65, h: 0.25, fontFace: 'Georgia', fontSize: 15, color: C.ink, margin: 0, fit: 'shrink' });
  slide.addText(purpose, { x: 2.78, y: y + 0.02, w: 3.05, h: 0.45, fontFace: 'Aptos', fontSize: 10.5, color: C.muted, margin: 0, fit: 'shrink' });
  slide.addText(rights, { x: 6.05, y: y + 0.02, w: 6.25, h: 0.48, fontFace: 'Aptos', fontSize: 10.5, color: C.ink, margin: 0, fit: 'shrink' });
  slide.addShape(pptx.ShapeType.line, { x: 1.03, y: y + 0.64, w: 11.25, h: 0, line: { color: C.line, width: 0.8 } });
}

async function build() {
  // 1 — Cover
  {
    const s = pptx.addSlide();
    s.background = { color: C.green };
    s.addShape(pptx.ShapeType.arc, { x: 7.3, y: 0, w: 6.03, h: 5.55, adjustPoint: 0.32, rotate: 90, fill: { color: C.green2, transparency: 55 }, line: { color: C.green2, transparency: 100 } });
    s.addImage({ path: logo, x: 0.72, y: 0.55, w: 0.66, h: 0.66, altText: 'CELTECH College logo' });
    s.addText('CENTRAL LUZON COLLEGE OF SCIENCE AND TECHNOLOGY', { x: 1.55, y: 0.63, w: 5.8, h: 0.23, fontFace: 'Aptos', fontSize: 9.2, bold: true, charSpacing: 1.1, color: 'D5E2DB', margin: 0, fit: 'shrink' });
    s.addText('HCI102 PROJECT DEFENSE', { x: 0.78, y: 2.03, w: 4.2, h: 0.26, fontFace: 'Aptos', fontSize: 10, bold: true, charSpacing: 2.3, color: C.gold, margin: 0 });
    s.addText('CLCST Library\nManagement System', { x: 0.75, y: 2.42, w: 6.35, h: 1.55, fontFace: 'Georgia', fontSize: 34, color: C.white, margin: 0, breakLine: false, fit: 'shrink' });
    s.addText('A secure, role-based campus library experience—from discovery and reservations to circulation, reporting, and policy control.', { x: 0.8, y: 4.18, w: 5.55, h: 0.78, fontFace: 'Aptos', fontSize: 15, color: 'CEDDD5', margin: 0, breakLine: false, fit: 'shrink' });
    s.addShape(pptx.ShapeType.line, { x: 0.8, y: 5.25, w: 3.15, h: 0, line: { color: C.brass, width: 2 } });
    s.addText('PROJECT TEAM  ·  NOVEMBER 2026', { x: 0.8, y: 5.42, w: 4.5, h: 0.26, fontFace: 'Aptos', fontSize: 9, bold: true, color: C.gold, charSpacing: 1.4, margin: 0 });
    await screenshot(s, path.join(shots, '01-sign-in.png'), 7.28, 1.28, 5.34, 4.84, { inset: 0.06, line: C.gold, alt: 'CLCST Library sign-in page' });
    s.addText('A defense presentation based on the current working repository and demo database.', { x: 7.45, y: 6.28, w: 4.95, h: 0.32, fontFace: 'Aptos', fontSize: 9.5, color: 'BED0C6', align: 'center', margin: 0 });
    addNotes(s, ['Introduce the project as an HCI102 system designed around real library roles and tasks.', 'State that every screen shown in this presentation comes from the current working Laravel application.']);
  }

  // 2 — Problem and response
  {
    const s = pptx.addSlide(); base(s, 'Project context', 2); title(s, 'From fragmented library work to one shared system', 'The project connects patron self-service, circulation work, administration, and governance without mixing permissions.');
    card(s, 0.75, 1.75, 5.65, 4.55, { fill: 'F3EFE6', line: 'E1D6C0' });
    label(s, 'Observed problem', 1.02, 2.05, 1.55, C.amber, 'F3E4C5');
    bulletList(s, ['Paper logs and scattered spreadsheets slow down searches and circulation.', 'Patrons cannot clearly see availability, due dates, reservations, and fines.', 'Staff work becomes error-prone when account approval, inventory, and transactions are separated.', 'Shared credentials or weak role checks expose sensitive records and administrative actions.'], 1.02, 2.58, 4.95, 3.2, { size: 14.3 });
    card(s, 6.78, 1.75, 5.78, 4.55, { fill: 'ECF4EF', line: 'BFD5C7' });
    label(s, 'Project response', 7.05, 2.05, 1.72, C.green, 'D5E9DC');
    bulletList(s, ['One web-based source of truth for books, copies, loans, holds, fines, requests, users, and policies.', 'Five role-specific workspaces with server-enforced RBAC.', 'Plain-language task flows for patrons, librarians, administrators, and the super administrator.', 'Secure sessions, private uploads, audit logging, validation, and reversible bulk imports.'], 7.05, 2.58, 4.95, 3.2, { size: 14.3 });
    addNotes(s, ['Frame the problem as both an operational and HCI issue: people need visibility, consistency, and safe boundaries.', 'Emphasize that the system does not merely digitize forms; it coordinates end-to-end library work.']);
  }

  // 3 — Objectives and scope
  {
    const s = pptx.addSlide(); base(s, 'Objectives and scope', 3); title(s, 'What the system is designed to achieve', 'A practical scope for a campus library, with clear ownership of every action.');
    const cols = [0.78, 4.52, 8.26];
    const heads = ['Discover & self-serve', 'Operate & control', 'Protect & explain'];
    const bodies = [
      ['Search the live catalog', 'Reserve and renew', 'View due dates, fines, history', 'Receive announcements and alerts'],
      ['Check books in and out', 'Manage copies and inventory', 'Approve accounts and requests', 'Import and export authorized data'],
      ['Apply RBAC on every request', 'Keep uploads private', 'Log sensitive actions', 'Use clear feedback and error prevention'],
    ];
    cols.forEach((x, i) => {
      card(s, x, 1.75, 3.35, 4.65, { fill: i === 1 ? 'F4F0E7' : C.white, line: i === 1 ? 'DED0AF' : C.line });
      s.addText(String(i + 1).padStart(2, '0'), { x: x + 0.22, y: 1.98, w: 0.55, h: 0.42, fontFace: 'Georgia', fontSize: 22, color: i === 1 ? C.brass : C.green, margin: 0 });
      s.addText(heads[i], { x: x + 0.22, y: 2.52, w: 2.75, h: 0.52, fontFace: 'Georgia', fontSize: 19, color: C.ink, margin: 0, fit: 'shrink' });
      bulletList(s, bodies[i], x + 0.22, 3.25, 2.82, 2.45, { size: 12.5, space: 10 });
    });
    addNotes(s, ['Use the three columns to describe the project scope: patron experience, library operations, and trust.', 'The system deliberately separates public registration from privileged role assignment.']);
  }

  // 4 — Roles and RBAC
  {
    const s = pptx.addSlide(); base(s, 'Role-based access', 4); title(s, 'Five workspaces, one permission model', 'The role stored in the database is checked by middleware and controllers—not trusted from the interface.');
    s.addText('ROLE', { x: 1.03, y: 1.68, w: 1.5, h: 0.22, fontFace: 'Aptos', fontSize: 8.5, bold: true, color: C.muted, charSpacing: 1.2, margin: 0 });
    s.addText('PRIMARY PURPOSE', { x: 2.78, y: 1.68, w: 2.5, h: 0.22, fontFace: 'Aptos', fontSize: 8.5, bold: true, color: C.muted, charSpacing: 1.2, margin: 0 });
    s.addText('AUTHORIZED CAPABILITIES', { x: 6.05, y: 1.68, w: 3.2, h: 0.22, fontFace: 'Aptos', fontSize: 8.5, bold: true, color: C.muted, charSpacing: 1.2, margin: 0 });
    roleRow(s, 2.0, 'Student', 'Self-service patron', 'Catalog, reservations, renewals, loans, fines, history, notifications, profile', C.green2);
    roleRow(s, 2.78, 'Faculty', 'Academic patron', 'Student capabilities plus reading lists, course reserves, sessions, ILL, recommendations', C.brass);
    roleRow(s, 3.56, 'Librarian', 'Daily library operations', 'Circulation desk, inventory, copies, requests, fines, announcements, reports, account review', C.blue);
    roleRow(s, 4.34, 'Administrator', 'Institutional management', 'Librarian workspace plus approvals, bulk data, reports, users, books, and activity logs', C.amber);
    roleRow(s, 5.12, 'Super Administrator', 'System governance', 'All administrative access plus roles, borrowing rules, fine settings, policy, and security status', C.red);
    addNotes(s, ['Explain the principle of least privilege: each role sees only the work it needs.', 'Point out that public registration cannot create Administrator or Super Administrator accounts.']);
  }

  // 5 — Patron experience
  {
    const s = pptx.addSlide(); base(s, 'Patron experience', 5); title(s, 'Patrons see the library as a connected journey', 'Student and faculty pages use the same visual language while respecting different limits and academic services.');
    await screenshot(s, path.join(shots, '02-student-dashboard.png'), 0.72, 1.72, 6.05, 4.58, { alt: 'Student dashboard with success toast' });
    await screenshot(s, path.join(shots, '03-faculty-dashboard.png'), 6.96, 1.72, 5.65, 4.58, { alt: 'Faculty dashboard' });
    label(s, 'Student portal', 0.95, 1.9, 1.45);
    label(s, 'Faculty portal', 7.19, 1.9, 1.45, C.brass, 'F1E6C7');
    s.addText('Immediate status, due dates, account limits, and actionable next steps reduce memory load.', { x: 0.9, y: 6.45, w: 5.65, h: 0.42, fontFace: 'Aptos', fontSize: 11, color: C.muted, align: 'center', margin: 0, fit: 'shrink' });
    s.addText('Faculty-specific services are added without changing familiar navigation or interaction patterns.', { x: 7.12, y: 6.45, w: 5.35, h: 0.42, fontFace: 'Aptos', fontSize: 11, color: C.muted, align: 'center', margin: 0, fit: 'shrink' });
    addNotes(s, ['Point to the visible success toast in the student screenshot as feedback after a change.', 'Explain that faculty accounts inherit patron functions and add academic-request workflows.']);
  }

  // 6 — Librarian operations
  {
    const s = pptx.addSlide(); base(s, 'Library operations', 6); title(s, 'The librarian workspace centers the circulation desk', 'Frequent tasks are grouped around books, transactions, users, requests, fines, and reporting.');
    await screenshot(s, path.join(shots, '04-librarian-dashboard.png'), 0.7, 1.72, 8.25, 5.03, { alt: 'Librarian operations dashboard' });
    const items = [
      ['1', 'Find patron', 'Scan the library e-Pass or enter the school ID.'],
      ['2', 'Scan a copy', 'Use the unique barcode to avoid title-level ambiguity.'],
      ['3', 'Apply rules', 'The same service checks limits, overdue items, holds, and reference status.'],
      ['4', 'Confirm outcome', 'Receipt numbers, status changes, audit records, and a visible result follow the action.'],
    ];
    items.forEach((it, i) => {
      const y = 1.72 + i * 1.15;
      s.addText(it[0], { x: 9.25, y, w: 0.42, h: 0.42, fontFace: 'Georgia', fontSize: 16, color: C.white, fill: { color: i === 3 ? C.brass : C.green }, margin: 0, align: 'center', valign: 'mid' });
      s.addText(it[1], { x: 9.85, y: y - 0.01, w: 2.45, h: 0.28, fontFace: 'Georgia', fontSize: 15, color: C.ink, margin: 0 });
      s.addText(it[2], { x: 9.85, y: y + 0.34, w: 2.5, h: 0.55, fontFace: 'Aptos', fontSize: 10.5, color: C.muted, margin: 0, fit: 'shrink' });
    });
    addNotes(s, ['Walk through a typical checkout: identify patron, identify copy, let the system enforce policy, confirm the transaction.', 'The interface supports barcode and QR input, but also provides manual entry when a camera is unavailable.']);
  }

  // 7 — Admin and super admin
  {
    const s = pptx.addSlide(); base(s, 'Administration and governance', 7); title(s, 'Management access is separated from system governance', 'Administrators manage institutional records; the super administrator controls global rules and security oversight.');
    await screenshot(s, path.join(shots, '05-admin-dashboard.png'), 0.74, 1.72, 6.03, 4.63, { alt: 'Administrator dashboard' });
    await screenshot(s, path.join(shots, '06-superadmin-dashboard.png'), 6.94, 1.72, 5.65, 4.63, { alt: 'Super administrator dashboard' });
    s.addText('ADMINISTRATOR', { x: 1.05, y: 6.46, w: 1.55, h: 0.25, fontFace: 'Aptos', fontSize: 9, bold: true, color: C.brass, charSpacing: 1.2, margin: 0 });
    s.addText('Approvals, records, reports, data exchange, transactions, and audit review.', { x: 2.62, y: 6.43, w: 3.9, h: 0.34, fontFace: 'Aptos', fontSize: 10.5, color: C.muted, margin: 0, fit: 'shrink' });
    s.addText('SUPER ADMINISTRATOR', { x: 7.18, y: 6.46, w: 2.05, h: 0.25, fontFace: 'Aptos', fontSize: 9, bold: true, color: C.red, charSpacing: 1.1, margin: 0 });
    s.addText('Roles, borrowing rules, fine settings, policies, security monitoring, and system status.', { x: 9.28, y: 6.43, w: 3.05, h: 0.38, fontFace: 'Aptos', fontSize: 10.5, color: C.muted, margin: 0, fit: 'shrink' });
    addNotes(s, ['Clarify that the administrator is not the same as the super administrator.', 'System-wide policy changes are limited to the highest role and are recorded in the activity log.']);
  }

  // 8 — Core workflow
  {
    const s = pptx.addSlide(); base(s, 'Core workflow', 8); title(s, 'One circulation lifecycle keeps every status consistent', 'The project tracks an individual physical copy from discovery to return—not only the book title.');
    const steps = [
      ['01', 'Discover', 'Search by title, author, ISBN, category, call number, or barcode.'],
      ['02', 'Reserve', 'Join the waitlist or receive a pickup hold when a copy is ready.'],
      ['03', 'Check out', 'Librarian scans the patron and copy; rules determine the due date.'],
      ['04', 'Renew', 'Patron requests renewal if the loan is current and nobody is waiting.'],
      ['05', 'Return', 'Check-in updates the copy, reservation queue, history, and any overdue fine.'],
    ];
    const xs = [0.75, 3.25, 5.75, 8.25, 10.75];
    steps.forEach((st, i) => {
      card(s, xs[i], 2.05, 1.85, 3.25, { fill: i === 4 ? 'F4EFE2' : C.white, line: i === 4 ? 'D8C69B' : C.line, shadow: false });
      s.addText(st[0], { x: xs[i] + 0.18, y: 2.25, w: 0.58, h: 0.35, fontFace: 'Georgia', fontSize: 20, color: i === 4 ? C.brass : C.green, margin: 0 });
      s.addText(st[1], { x: xs[i] + 0.18, y: 2.82, w: 1.45, h: 0.38, fontFace: 'Georgia', fontSize: 17, color: C.ink, margin: 0, fit: 'shrink' });
      s.addText(st[2], { x: xs[i] + 0.18, y: 3.45, w: 1.45, h: 1.18, fontFace: 'Aptos', fontSize: 10.5, color: C.muted, margin: 0, fit: 'shrink', valign: 'mid' });
      if (i < steps.length - 1) s.addShape(pptx.ShapeType.chevron, { x: xs[i] + 1.93, y: 3.33, w: 0.5, h: 0.45, fill: { color: 'D8E4DD' }, line: { color: 'D8E4DD' } });
    });
    s.addText('CENTRAL POLICY ENGINE', { x: 4.95, y: 5.73, w: 3.45, h: 0.27, fontFace: 'Aptos', fontSize: 9, bold: true, color: C.green, charSpacing: 1.8, align: 'center', margin: 0 });
    s.addText('Loan limits · loan periods · overdue blocking · renewal limits · waiting lists · reference restrictions · fine calculation', { x: 2.15, y: 6.12, w: 9.05, h: 0.42, fontFace: 'Aptos', fontSize: 11.5, color: C.ink, align: 'center', margin: 0, fit: 'shrink' });
    addNotes(s, ['This workflow is the operational heart of the project.', 'All pages call the same CirculationService, so the rule shown to a patron is the rule enforced at the desk.']);
  }

  // 9 — Data model
  {
    const s = pptx.addSlide(); base(s, 'Data architecture', 9); title(s, 'A relational model preserves both current state and history', 'SQLite supports the local demo; the same migrations work with MySQL or MariaDB for deployment.');
    const groups = [
      { x: 0.78, head: 'Identity & policy', color: C.green, items: ['users', 'library_settings', 'notification_reads'] },
      { x: 4.48, head: 'Catalog & circulation', color: C.brass, items: ['library_books', 'book_copies', 'loans', 'reservations', 'fines'] },
      { x: 8.18, head: 'Service & oversight', color: C.blue, items: ['library_requests', 'announcements', 'audit_logs'] },
    ];
    groups.forEach((g) => {
      card(s, g.x, 1.72, 3.12, 4.8, { fill: C.white, line: C.line });
      s.addShape(pptx.ShapeType.rect, { x: g.x, y: 1.72, w: 3.12, h: 0.13, fill: { color: g.color }, line: { color: g.color } });
      s.addText(g.head, { x: g.x + 0.23, y: 2.07, w: 2.55, h: 0.36, fontFace: 'Georgia', fontSize: 17, color: C.ink, margin: 0, fit: 'shrink' });
      g.items.forEach((it, i) => {
        const yy = 2.78 + i * 0.61;
        s.addText(it, { x: g.x + 0.25, y: yy, w: 2.6, h: 0.36, fontFace: 'Aptos', fontSize: 12, bold: true, color: g.color, fill: { color: i % 2 ? 'F8F8F5' : 'F1F5F2' }, margin: 0.1, fit: 'shrink' });
      });
    });
    s.addText('Integrity controls', { x: 11.48, y: 1.78, w: 1.2, h: 0.3, fontFace: 'Georgia', fontSize: 14, color: C.ink, margin: 0 });
    bulletList(s, ['Foreign keys protect transaction history', 'Unique IDs prevent duplicate ISBNs, barcodes, emails, school IDs, and receipts', 'Indexes support common status, due-date, patron, and title lookups'], 11.45, 2.28, 1.45, 3.85, { size: 9.6, space: 8 });
    addNotes(s, ['Distinguish a catalog title from its physical copies: one title can have many uniquely tracked copies.', 'The model keeps auditability and avoids deleting records that are still referenced by loans.']);
  }

  // 10 — Security
  {
    const s = pptx.addSlide(); base(s, 'Security and privacy', 10, true); title(s, 'Security is enforced in layers—not left to the interface', 'Controls cover identity, authorization, sessions, input, data storage, APIs, and traceability.', { dark: true });
    const columns = [
      { x: 0.78, title: 'Identity', items: ['CLCST email domain', '10+ character password policy', 'Bcrypt password hashing', 'Pending approval before access'] },
      { x: 4.45, title: 'Access & session', items: ['RBAC middleware + controller checks', 'Encrypted, regenerated sessions', '15-minute inactivity timeout', 'Rate-limited sign-in attempts'] },
      { x: 8.12, title: 'Data & API', items: ['CSRF and escaped output', 'Private verification uploads', 'Security headers + no-cache logout', 'Signed, expiring, revocable JWTs'] },
    ];
    columns.forEach((col, i) => {
      card(s, col.x, 1.78, 3.18, 4.55, { fill: i === 1 ? '164F3B' : '124631', line: '3B715C', shadow: false });
      s.addText(`0${i + 1}`, { x: col.x + 0.22, y: 2.05, w: 0.58, h: 0.35, fontFace: 'Georgia', fontSize: 20, color: C.gold, margin: 0 });
      s.addText(col.title, { x: col.x + 0.22, y: 2.58, w: 2.55, h: 0.42, fontFace: 'Georgia', fontSize: 19, color: C.white, margin: 0 });
      bulletList(s, col.items, col.x + 0.22, 3.28, 2.6, 2.35, { size: 12, color: 'D7E4DD', space: 9 });
    });
    s.addText('Passwords are never exported. Privileged public sign-up is disabled. Sensitive actions are logged.', { x: 1.38, y: 6.58, w: 10.6, h: 0.34, fontFace: 'Aptos', fontSize: 12, color: C.gold, bold: true, align: 'center', margin: 0, fit: 'shrink' });
    addNotes(s, ['Stress that hiding a link is not authorization; requests are rejected server-side when the role is wrong.', 'After logout, protected pages send no-cache headers, so the browser back button cannot restore an authenticated dashboard.']);
  }

  // 11 — HCI
  {
    const s = pptx.addSlide(); base(s, 'HCI design decisions', 11); title(s, 'The interface reduces uncertainty at every step', 'The visual system keeps the institutional character while making tasks easier to recognize, recover, and complete.');
    const principles = [
      ['Consistency', 'Universal sidebars, repeated action placement, stable color roles, and shared terminology across portals.'],
      ['Visibility of status', 'Availability, due dates, queue positions, account state, progress, and activity results stay visible.'],
      ['Feedback', 'Accessible toast notifications confirm successful additions, changes, and removals without interrupting work.'],
      ['Error prevention', 'Confirmations protect destructive actions; validation stays beside the affected field or task.'],
      ['Recognition', 'Plain-language labels, role-specific navigation, icons with text, and contextual help reduce recall.'],
      ['Accessibility', 'Keyboard focus, semantic status/alert roles, readable contrast, responsive layouts, and reduced-motion support.'],
    ];
    principles.forEach((p, i) => {
      const col = i % 2;
      const row = Math.floor(i / 2);
      const x = 0.78 + col * 6.15;
      const y = 1.75 + row * 1.55;
      s.addText(String(i + 1).padStart(2, '0'), { x, y, w: 0.48, h: 0.38, fontFace: 'Georgia', fontSize: 17, color: i === 2 ? C.brass : C.green, margin: 0 });
      s.addText(p[0], { x: x + 0.66, y, w: 2.1, h: 0.34, fontFace: 'Georgia', fontSize: 16, color: C.ink, margin: 0 });
      s.addText(p[1], { x: x + 0.66, y: y + 0.43, w: 5.0, h: 0.66, fontFace: 'Aptos', fontSize: 11, color: C.muted, margin: 0, fit: 'shrink' });
      s.addShape(pptx.ShapeType.line, { x: x + 0.66, y: y + 1.18, w: 4.98, h: 0, line: { color: C.line, width: 0.8 } });
    });
    addNotes(s, ['Connect each decision to a usability principle rather than discussing visual decoration alone.', 'The toast system is a recent improvement: success is visible but non-blocking, while errors remain persistent.']);
  }

  // 12 — Bulk and deployment
  {
    const s = pptx.addSlide(); base(s, 'Data exchange and deployment', 12); title(s, 'Bulk workflows save time without sacrificing validation', 'Authorized roles can move large datasets safely; deployment does not depend on a Node.js runtime.');
    await screenshot(s, path.join(shots, '07-bulk-data.png'), 0.72, 1.72, 7.32, 4.85, { alt: 'Bulk data import and export page' });
    const facts = [
      ['2,000', 'maximum CSV records per import'],
      ['1 transaction', 'all rows succeed or the file rolls back'],
      ['CSV-safe', 'spreadsheet formulas are neutralized on export'],
      ['Shared-host ready', 'Blade + compiled CSS + bundled fonts and scanner'],
      ['SQLite / MySQL', 'same Laravel migrations for local and deployed data'],
    ];
    facts.forEach((f, i) => {
      const y = 1.68 + i * 0.94;
      s.addText(f[0], { x: 8.42, y, w: 1.35, h: 0.38, fontFace: 'Georgia', fontSize: 18, color: i === 3 ? C.brass : C.green, margin: 0, align: 'right', fit: 'shrink' });
      s.addText(f[1], { x: 9.98, y: y + 0.01, w: 2.55, h: 0.46, fontFace: 'Aptos', fontSize: 11, color: C.ink, margin: 0, fit: 'shrink' });
      if (i < facts.length - 1) s.addShape(pptx.ShapeType.line, { x: 8.4, y: y + 0.66, w: 4.0, h: 0, line: { color: C.line, width: 0.7 } });
    });
    addNotes(s, ['Show that bulk import is permission-limited and transactional.', 'The runtime requires PHP and Composer; assets are already built, so Node.js is not required on shared hosting.']);
  }

  // 13 — Responsive and demo readiness
  {
    const s = pptx.addSlide(); base(s, 'Quality and readiness', 13); title(s, 'The current build is responsive, tested, and demo-ready', 'Verification covers the interface, role boundaries, security behavior, circulation rules, and database-backed pages.');
    await screenshot(s, path.join(shots, '08-mobile-student.png'), 0.88, 1.75, 2.85, 4.78, { alt: 'Mobile student dashboard' });
    card(s, 4.25, 1.75, 8.05, 4.78, { fill: C.white, line: C.line });
    metric(s, '112', 'automated tests passing', 4.66, 2.22, 2.0, C.green);
    metric(s, '1,105', 'assertions passing', 7.1, 2.22, 2.0, C.brass);
    metric(s, '5', 'role-specific portals', 9.54, 2.22, 2.0, C.blue);
    s.addShape(pptx.ShapeType.line, { x: 4.68, y: 3.42, w: 6.82, h: 0, line: { color: C.line, width: 1 } });
    bulletList(s, ['All migrations apply to a fresh database and the demo seeder provides realistic role-based records.', 'Browser-level toast behavior is verified, including keyboard dismissal and accessible status roles.', 'Security checks cover domain restrictions, password hashing, headers, CSRF, escaping, rate limits, RBAC, and logout caching.', 'Every portal page is loaded against the demo data, and the current interface is captured at desktop and mobile sizes.'], 4.7, 3.72, 6.9, 2.2, { size: 12, space: 8 });
    s.addText('Verified on the current project branch · 19 November 2026', { x: 4.67, y: 6.06, w: 6.9, h: 0.28, fontFace: 'Aptos', fontSize: 9.5, color: C.muted, align: 'center', margin: 0 });
    addNotes(s, ['Use these figures only as current-build evidence, not as usability-study results.', 'Mention that the local demo server is running and the seeded accounts cover every role.']);
  }

  // 14 — Limits and future work
  {
    const s = pptx.addSlide(); base(s, 'Limitations and next steps', 14); title(s, 'What remains beyond the present project scope', 'The current version is complete for the HCI102 defense, while these items are appropriate future extensions.');
    const current = [
      'Web application; no native Android or iOS client',
      'Password resets are staff-assisted rather than email-based',
      'Alerts appear in-system; no SMS or automated email gateway',
      'Fine records are managed in the library; cashier payment integration is outside scope',
      'Book-cover images use an external source when internet is available',
    ];
    const next = [
      'Conduct formal usability testing with students, faculty, and library staff',
      'Add optional email/SMS notifications after institutional approval',
      'Integrate identity services or single sign-on',
      'Add richer acquisition, supplier, and budget workflows',
      'Deploy over HTTPS with production backups, monitoring, and credential rotation',
    ];
    card(s, 0.78, 1.72, 5.78, 4.85, { fill: 'F4F1EA', line: 'DED6C8' });
    card(s, 6.78, 1.72, 5.78, 4.85, { fill: 'EEF5F0', line: 'C5D8CB' });
    label(s, 'Current boundaries', 1.05, 2.02, 1.75, C.amber, 'F2E5C6');
    label(s, 'Recommended next steps', 7.05, 2.02, 2.15);
    bulletList(s, current, 1.05, 2.55, 4.95, 3.35, { size: 12.5, space: 9 });
    bulletList(s, next, 7.05, 2.55, 4.95, 3.35, { size: 12.5, space: 9 });
    addNotes(s, ['Being explicit about limitations demonstrates scope control and technical honesty.', 'Formal usability evaluation is the most important HCI-focused next step.']);
  }

  // 15 — Demo and close
  {
    const s = pptx.addSlide(); base(s, 'Live demonstration', 15, true); title(s, 'A five-role demo in six minutes', 'Follow one book and one patron through the system, then show governance and data exchange.', { dark: true });
    const demo = [
      ['1', 'Student', 'Sign in, search a title, place a reservation, and show the confirmation toast.'],
      ['2', 'Faculty', 'Open academic services and demonstrate the expanded borrowing context.'],
      ['3', 'Librarian', 'Scan the patron and copy, then show checkout or check-in enforcement.'],
      ['4', 'Administrator', 'Review a pending account and open bulk data import/export.'],
      ['5', 'Super Admin', 'Show borrowing rules, fine settings, audit logs, and system status.'],
    ];
    demo.forEach((d, i) => {
      const y = 2.02 + i * 0.82;
      s.addText(d[0], { x: 0.9, y, w: 0.42, h: 0.42, fontFace: 'Georgia', fontSize: 17, color: C.green, fill: { color: i === 4 ? C.gold : C.cream }, margin: 0, align: 'center', valign: 'mid' });
      s.addText(d[1], { x: 1.58, y: y - 0.01, w: 1.4, h: 0.32, fontFace: 'Georgia', fontSize: 15.5, color: C.white, margin: 0 });
      s.addText(d[2], { x: 3.08, y, w: 6.05, h: 0.42, fontFace: 'Aptos', fontSize: 11.5, color: 'D4E1DA', margin: 0, fit: 'shrink' });
    });
    s.addShape(pptx.ShapeType.line, { x: 9.7, y: 1.92, w: 0, h: 4.18, line: { color: '3D715C', width: 1 } });
    s.addImage({ path: logo, x: 10.52, y: 1.75, w: 1.35, h: 1.35, altText: 'CELTECH College logo' });
    s.addText('Questions?', { x: 9.95, y: 3.42, w: 2.55, h: 0.62, fontFace: 'Georgia', fontSize: 27, color: C.white, align: 'center', margin: 0 });
    s.addText('Repository', { x: 10.25, y: 4.45, w: 1.95, h: 0.22, fontFace: 'Aptos', fontSize: 8.5, bold: true, color: C.gold, charSpacing: 1.5, align: 'center', margin: 0 });
    s.addText('github.com/krstnkye/\nCLCST-Library-Management-System-HCI102', { x: 9.92, y: 4.82, w: 2.65, h: 0.66, fontFace: 'Aptos', fontSize: 9.5, color: 'D3E0D9', align: 'center', margin: 0, fit: 'shrink', hyperlink: { url: 'https://github.com/krstnkye/CLCST-Library-Management-System-HCI102' } });
    s.addText('Thank you.', { x: 0.9, y: 6.37, w: 3.2, h: 0.45, fontFace: 'Georgia', fontSize: 22, color: C.gold, margin: 0 });
    addNotes(s, ['Use the seeded demo accounts; all demo passwords are LibraryDemo2026!.', 'Keep the demo narrative continuous by following one title and explaining how each role sees the same underlying records.', 'Invite questions after showing the super administrator audit trail.']);
  }

  await pptx.writeFile({ fileName: outFile });
  await repairGeneratedContentTypes(outFile);
  console.log(outFile);
}

build().catch((error) => {
  console.error(error);
  process.exit(1);
});
