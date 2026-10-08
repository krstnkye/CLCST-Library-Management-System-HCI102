function selectedRole() {
    return document.querySelector('input[name="patron_role"]:checked')?.value || 'student';
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

    document.getElementById('studentFieldsBlock').classList.toggle('hidden', role !== 'student');
    document.getElementById('facultyFieldsBlock').classList.toggle('hidden', role !== 'faculty');
    document.getElementById('staffFieldsBlock').classList.toggle('hidden', role !== 'staff');
    document.getElementById('previewCardRoleBadge').innerText = role.toUpperCase();
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
document.getElementById('studentIdInput').addEventListener('input', (event) => updateLiveBarcode(event.target.value));
document.getElementById('programSelect').addEventListener('change', (event) => updateLiveProgram(event.target.value));

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

handleRoleChange(selectedRole());
