function selectedRole() {
    return document.querySelector('input[name="patron_role"]:checked')?.value || 'student';
}

function setBlockState(blockId, enabled) {
    const block = document.getElementById(blockId);

    block.classList.toggle('hidden', !enabled);
    block.querySelectorAll('input, select, textarea').forEach((field) => {
        field.disabled = !enabled;
        field.required = enabled && (field.name === 'patron_id' || field.name === 'program_or_department');
    });
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
    document.getElementById('previewCardRoleBadge').innerText = role.toUpperCase();

    const idInput = document.querySelector(`#${role}FieldsBlock [name="patron_id"]`);
    const programInput = document.querySelector(`#${role}FieldsBlock [name="program_or_department"]`);

    updateLiveBarcode(idInput?.value || '');
    updateLiveProgram(programInput?.value || '');
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

document.querySelectorAll('input[name="patron_role"]').forEach((input) => {
    input.addEventListener('change', () => handleRoleChange(input.value));
});

document.getElementById('firstNameInput').addEventListener('input', updateLiveCard);
document.getElementById('lastNameInput').addEventListener('input', updateLiveCard);
document.querySelectorAll('[name="patron_id"]').forEach((input) => {
    input.addEventListener('input', (event) => {
        if (!event.target.disabled) {
            updateLiveBarcode(event.target.value);
        }
    });
});

document.querySelectorAll('[name="program_or_department"]').forEach((input) => {
    input.addEventListener('change', (event) => {
        if (!event.target.disabled) {
            updateLiveProgram(event.target.value);
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
    document.getElementById('selectedFileName').innerText = file ? file.name : 'No file selected';
});

document.getElementById('registrationForm').addEventListener('submit', () => {
    const button = document.getElementById('submitRegistrationButton');
    const text = document.getElementById('submitRegistrationText');

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
