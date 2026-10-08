const roleConfig = {
    student: {
        tag: 'STUDENT PATRON',
        tagClass: 'inline-flex items-center rounded-md bg-primary-fixed px-2.5 py-0.5 text-[10px] font-bold text-on-primary-fixed-variant',
        desc: 'Authenticate using your Registered Student ID Number as printed on your COR or Smart RFID ID.',
        label: 'Student ID Number (e.g., 2023-10492-OL)',
        placeholder: 'Format: YYYY-XXXXX-XX',
        hint: 'COR Verified',
        icon: 'badge',
        heroIcon: 'school',
        submit: 'Authenticate as Student',
        password: 'Library PIN / Password',
        context: 'student-context-options',
        sso: false,
        cardName: 'DE GUZMAN, MATEO S.',
        cardId: 'ID: 2023-01984-OL',
        cardDept: 'BS Computer Science',
        barcode: '*202301984OL*',
    },
    faculty: {
        tag: 'FACULTY & RESEARCHER',
        tagClass: 'inline-flex items-center rounded-md bg-secondary-fixed px-2.5 py-0.5 text-[10px] font-bold text-on-secondary-fixed',
        desc: 'Access faculty reserve collections, extended loan quotas, and journal peer databases via institutional account.',
        label: 'Faculty Employee ID / Institutional Webmail',
        placeholder: 'e.g., FAC-2018-042 or prof.name@clcst.edu.ph',
        hint: 'Dept Cleared',
        icon: 'assignment_ind',
        heroIcon: 'assignment_ind',
        submit: 'Access Faculty Academic Portal',
        password: 'Institutional Account Password',
        context: 'faculty-context-options',
        sso: true,
        cardName: 'DR. EVELYN M. SANTOS',
        cardId: 'FAC: 2018-042-IT',
        cardDept: 'College of Engineering & Tech',
        barcode: '*FAC2018042IT*',
    },
    staff: {
        tag: 'LIBRARY OPERATOR / DESK',
        tagClass: 'inline-flex items-center rounded-md bg-error-container px-2.5 py-0.5 text-[10px] font-bold text-on-error-container',
        desc: 'Staff station credentials provide administrative authority for circulation, fines clearance, and catalog accessioning.',
        label: 'Workstation Operator ID / Staff Number',
        placeholder: 'e.g., CLCST-LIB-009',
        hint: 'Tier 2 Access',
        icon: 'admin_panel_settings',
        heroIcon: 'manage_accounts',
        submit: 'Sign In to Circulation Console',
        password: 'Operator Master Passkey',
        context: 'staff-context-options',
        sso: false,
        cardName: 'MARIA CORAZON REYES, RL',
        cardId: 'STAFF: LIB-009-OL',
        cardDept: 'Head of Circulation Services',
        barcode: '*STAFFLIB009OL*',
    },
};

function setRole(role) {
    const config = roleConfig[role] || roleConfig.student;

    document.getElementById('role-input').value = role;

    Object.keys(roleConfig).forEach((key) => {
        const button = document.getElementById(`btn-role-${key}`);
        const badge = document.getElementById(`badge-${key}`);
        const selected = key === role;

        button.classList.toggle('border-primary', selected);
        button.classList.toggle('ring-1', selected);
        button.classList.toggle('ring-primary', selected);
        button.classList.toggle('border-outline-variant/60', !selected);
        badge.classList.toggle('hidden', !selected);
        badge.classList.toggle('inline-flex', selected);
    });

    document.getElementById('active-role-tag').textContent = config.tag;
    document.getElementById('active-role-tag').className = config.tagClass;
    document.getElementById('active-role-desc').textContent = config.desc;
    document.getElementById('identifier-label').textContent = config.label;
    document.getElementById('identifier-input').placeholder = config.placeholder;
    document.getElementById('identifier-hint').textContent = config.hint;
    document.getElementById('identifier-icon').textContent = config.icon;
    document.getElementById('role-hero-icon').textContent = config.heroIcon;
    document.getElementById('submit-button-text').textContent = config.submit;
    document.getElementById('password-label').textContent = config.password;

    ['student-context-options', 'faculty-context-options', 'staff-context-options'].forEach((id) => {
        document.getElementById(id).classList.toggle('hidden', id !== config.context);
    });

    document.getElementById('google-sso-container').classList.toggle('hidden', !config.sso);
    document.getElementById('card-holder-name').textContent = config.cardName;
    document.getElementById('card-holder-id').textContent = config.cardId;
    document.getElementById('card-holder-dept').textContent = config.cardDept;
    document.getElementById('card-barcode-text').textContent = config.barcode;
}

document.querySelectorAll('[data-role]').forEach((button) => {
    button.addEventListener('click', () => setRole(button.dataset.role));
});

document.querySelectorAll('[data-alert]').forEach((button) => {
    button.addEventListener('click', () => window.alert(button.dataset.alert));
});

document.getElementById('toggle-password').addEventListener('click', () => {
    const passwordInput = document.getElementById('password-input');
    const icon = document.getElementById('pw-visibility-icon');
    const isHidden = passwordInput.type === 'password';

    passwordInput.type = isHidden ? 'text' : 'password';
    icon.textContent = isHidden ? 'visibility_off' : 'visibility';
});

setRole(document.getElementById('role-input').value || 'student');
