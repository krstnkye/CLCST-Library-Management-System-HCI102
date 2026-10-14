function selectedRole() {
    return document.querySelector('input[name="account_type_selector"]:checked')?.value || 'student';
}

function setBlockState(blockId, enabled) {
    const block = document.getElementById(blockId);

    block.classList.toggle('hidden', !enabled);
    block.querySelectorAll('input, select, textarea').forEach((field) => {
        field.disabled = !enabled;
        field.required = enabled && (field.classList.contains('role-id-input') || field.classList.contains('role-program-input'));
    });
}

function roleFieldIds(role) {
    return {
        id: `${role}IdInput`,
        program: role === 'student'
            ? 'programSelect'
            : (role === 'faculty' ? 'facultyDepartmentSelect' : (role === 'staff' ? 'staffAccessSelect' : 'superadminAccessSelect')),
    };
}

function syncSelectedRoleFields() {
    const role = selectedRole();
    const fields = roleFieldIds(role);
    const visibleId = document.getElementById(fields.id);
    const visibleProgram = document.getElementById(fields.program);
    const submittedId = document.getElementById('selectedPatronId');
    const submittedProgram = document.getElementById('selectedProgramOrDepartment');
    const submittedRole = document.getElementById('selectedPatronRole');

    submittedRole.value = role;
    submittedId.value = visibleId?.value?.trim() || '';
    submittedProgram.value = visibleProgram?.value || '';

    updateLiveBarcode(submittedId.value);
    updateLiveProgram(submittedProgram.value);
}

function handleRoleChange(role) {
    document.querySelectorAll('.role-option').forEach((card) => {
        const isSelected = card.dataset.role === role;
        const check = card.querySelector('.role-badge-check');
        const title = card.querySelector('h3');

        card.classList.toggle('border-primary', isSelected);
        card.classList.toggle('bg-surface-container-low/50', isSelected);
        card.classList.toggle('border-outline-variant', !isSelected);
        card.classList.toggle('bg-surface-container-lowest', !isSelected);
        check.classList.toggle('hidden', !isSelected);
        check.classList.toggle('flex', isSelected);
        title.classList.toggle('text-primary', isSelected);
        title.classList.toggle('text-on-surface', !isSelected);
    });

    setBlockState('studentFieldsBlock', role === 'student');
    setBlockState('facultyFieldsBlock', role === 'faculty');
    setBlockState('staffFieldsBlock', role === 'staff');
    setBlockState('superadminFieldsBlock', role === 'superadmin');
    document.getElementById('previewCardRoleBadge').innerText = role.toUpperCase();

    syncSelectedRoleFields();
}

function updateLiveCard() {
    const first = document.getElementById('firstNameInput').value.trim();
    const last = document.getElementById('lastNameInput').value.trim();
    document.getElementById('previewCardName').innerText = first || last ? `${first} ${last}`.trim().toUpperCase() : 'JUAN DELA CRUZ';
}

function updateLiveProgram(value) {
    document.getElementById('previewCardProgram').innerText = value || 'BS in Computer Science (BSCS)';
}

function updateLiveBarcode(value) {
    const clean = value.trim() || '2024-10492-OL';
    document.getElementById('previewCardId').innerText = clean;
    document.getElementById('barcodeText').innerText = `*${clean}*`;
}

document.querySelectorAll('input[name="account_type_selector"]').forEach((input) => {
    input.addEventListener('change', () => handleRoleChange(input.value));
});

document.getElementById('firstNameInput').addEventListener('input', updateLiveCard);
document.getElementById('lastNameInput').addEventListener('input', updateLiveCard);
document.querySelectorAll('.role-id-input').forEach((input) => {
    input.addEventListener('input', (event) => {
        if (!event.target.disabled) {
            syncSelectedRoleFields();
        }
    });
});

document.querySelectorAll('.role-program-input').forEach((input) => {
    input.addEventListener('change', (event) => {
        if (!event.target.disabled) {
            syncSelectedRoleFields();
        }
    });
});

document.querySelectorAll('.toggle-password').forEach((button) => {
    button.addEventListener('click', () => {
        const input = document.getElementById(button.dataset.target);
        const icon = button.querySelector('.material-symbols-outlined');
        const showPassword = input.type === 'password';

        input.type = showPassword ? 'text' : 'password';
        icon.innerText = showPassword ? 'visibility_off' : 'visibility';
    });
});

document.getElementById('fileUpload').addEventListener('change', (event) => {
    const file = event.target.files[0];
    const preview = document.getElementById('selectedFilePreview');

    document.getElementById('selectedFileName').innerText = file ? file.name : 'No file selected';

    if (preview.src) {
        URL.revokeObjectURL(preview.src);
    }

    if (file && file.type.startsWith('image/')) {
        preview.src = URL.createObjectURL(file);
        preview.classList.remove('hidden');
    } else {
        preview.removeAttribute('src');
        preview.classList.add('hidden');
    }
});

document.getElementById('registrationForm').addEventListener('submit', (event) => {
    const button = document.getElementById('submitRegistrationButton');
    const text = document.getElementById('submitRegistrationText');

    syncSelectedRoleFields();

    if (!document.getElementById('selectedPatronId').value || !document.getElementById('selectedProgramOrDepartment').value) {
        event.preventDefault();
        return;
    }

    button.disabled = true;
    text.innerText = 'Submitting registration...';
});

document.getElementById('registrationForm').addEventListener('reset', () => {
    window.setTimeout(() => {
        handleRoleChange(selectedRole());
        updateLiveCard();
    }, 0);
});

handleRoleChange(selectedRole());
