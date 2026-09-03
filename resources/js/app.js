import './bootstrap';
import 'flowbite';

window.openModal = function (id) {
    const el = document.getElementById(id);
    if (!el) return;

    el.classList.remove('hidden');
};

window.closeModal = function (id) {
    const el = document.getElementById(id);
    if (!el) return;

    el.classList.add('hidden');
};

function hasSensitiveInputs(modalEl) {
    if (!modalEl) return false;

    if (modalEl.querySelector('input[type="password"]')) return true;

    // Nama panitia/petugas/admin sering pakai input text.
    const nameInputs = modalEl.querySelectorAll('input[type="text"], input:not([type])');
    for (const input of nameInputs) {
        const nameAttr = (input.getAttribute('name') || '').toLowerCase();
        if (nameAttr.includes('nama')) return true;
        if (nameAttr.includes('panitia')) return true;
        if (nameAttr.includes('petugas')) return true;
    }

    return false;
}

function closeAndResetModal(modalEl) {
    if (!modalEl) return;

    // Tutup modal (tanpa mengutak-atik class display lain seperti `flex`)
    modalEl.classList.add('hidden');

    // Reset form (mengosongkan input yang user ketik)
    modalEl.querySelectorAll('form').forEach((form) => {
        try {
            form.reset();
        } catch (_) {
            // ignore
        }
    });

    // Pastikan password benar-benar kosong (kadang browser mengembalikan nilai dari BFCache)
    modalEl.querySelectorAll('input[type="password"]').forEach((input) => {
        input.value = '';
    });
}

function resetSensitiveModalsOnPageShow() {
    const candidates = document.querySelectorAll('[id^="modal"]');

    candidates.forEach((modalEl) => {
        // Pastikan benar-benar elemen modal overlay
        if (!modalEl.classList?.contains('fixed')) return;
        if (!hasSensitiveInputs(modalEl)) return;

        closeAndResetModal(modalEl);
    });
}

// Fix BFCache: saat kembali via tombol Back/Forward, DOM+input bisa dipulihkan.
// Kita paksa tutup & reset semua modal yang berisi password/nama.
window.addEventListener('pageshow', () => {
    resetSensitiveModalsOnPageShow();
});

function setPasswordToggleState(toggleBtn, inputEl, isVisible) {
    if (!toggleBtn || !inputEl) return;

    inputEl.type = isVisible ? 'text' : 'password';

    toggleBtn.setAttribute('aria-pressed', isVisible ? 'true' : 'false');
    toggleBtn.setAttribute('aria-label', isVisible ? 'Sembunyikan password' : 'Tampilkan password');

    const eyeIcon = toggleBtn.querySelector('[data-password-icon="eye"]');
    const eyeOffIcon = toggleBtn.querySelector('[data-password-icon="eye-off"]');

    if (eyeIcon) eyeIcon.classList.toggle('hidden', isVisible);
    if (eyeOffIcon) eyeOffIcon.classList.toggle('hidden', !isVisible);
}

let __passwordToggleAutoId = 0;

function autoAddPasswordTogglesInModals(scope = document) {
    const passwordInputs = scope.querySelectorAll('input[type="password"]');

    passwordInputs.forEach((inputEl) => {
        // Only handle modal inputs (public modals in this project use id starting with "modal")
        const modalRoot = inputEl.closest('[id^="modal"]');
        if (!modalRoot) return;
        if (!modalRoot.classList?.contains('fixed')) return;

        // Allow opt-out if needed later
        if (inputEl.hasAttribute('data-password-no-toggle')) return;

        // Ensure the input has an id to target
        if (!inputEl.id) {
            __passwordToggleAutoId += 1;
            inputEl.id = `pw_modal_${__passwordToggleAutoId}`;
        }

        // If already has a toggle button pointing to this input, skip
        const existingToggle = scope.querySelector(`[data-password-toggle="${inputEl.id}"]`);
        if (existingToggle) return;

        // Ensure room for the button
        inputEl.classList.add('pr-12');

        // Wrap input in a relative container if needed
        let wrapper = inputEl.parentElement;
        const parentHasToggle = wrapper?.querySelector?.('[data-password-toggle]');

        if (!wrapper || (!wrapper.classList.contains('relative') && !parentHasToggle)) {
            const newWrapper = document.createElement('div');
            newWrapper.className = 'relative';
            newWrapper.setAttribute('data-password-toggle-wrapper', '1');

            wrapper = newWrapper;
            inputEl.parentNode.insertBefore(newWrapper, inputEl);
            newWrapper.appendChild(inputEl);
        }

        // If wrapper already has toggle button, don't add again
        if (wrapper.querySelector('[data-password-toggle]')) return;

        const toggleBtn = document.createElement('button');
        toggleBtn.type = 'button';
        toggleBtn.className = 'absolute inset-y-0 right-0 flex items-center px-3 text-slate-400 hover:text-slate-600';
        toggleBtn.setAttribute('aria-label', 'Tampilkan password');
        toggleBtn.setAttribute('aria-pressed', 'false');
        toggleBtn.setAttribute('data-password-toggle', inputEl.id);
        toggleBtn.innerHTML = `
            <svg data-password-icon="eye" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.5 12s3.5-7 9.5-7 9.5 7 9.5 7-3.5 7-9.5 7-9.5-7-9.5-7Z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15.5a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7Z" />
            </svg>
            <svg data-password-icon="eye-off" class="h-5 w-5 hidden" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4l16 16" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.2 10.3a2.5 2.5 0 0 0 3.5 3.5" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6.8 6.9C4.2 8.6 2.5 12 2.5 12s3.5 7 9.5 7c2.1 0 4-.6 5.5-1.5" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.1 5.3C10 5.1 11 5 12 5c6 0 9.5 7 9.5 7s-1.2 2.4-3.6 4.3" />
            </svg>
        `.trim();

        wrapper.appendChild(toggleBtn);
    });
}

function initPasswordToggles(scope = document) {
    autoAddPasswordTogglesInModals(scope);

    const toggles = scope.querySelectorAll('[data-password-toggle]');

    toggles.forEach((toggleBtn) => {
        if (toggleBtn.dataset.passwordToggleInitialized === '1') return;

        const targetId = toggleBtn.getAttribute('data-password-toggle');
        if (!targetId) return;

        const inputEl = document.getElementById(targetId);
        if (!inputEl) return;

        // Default state: hidden
        setPasswordToggleState(toggleBtn, inputEl, false);

        toggleBtn.addEventListener('click', () => {
            const isVisible = inputEl.type === 'password';
            setPasswordToggleState(toggleBtn, inputEl, isVisible);
        });

        toggleBtn.dataset.passwordToggleInitialized = '1';
    });
}

document.addEventListener('DOMContentLoaded', () => {
    initPasswordToggles();
});

// BFCache: kalau balik via Back/Forward, DOM bisa dipulihkan dalam state "password terlihat".
// Kita reset balik ke mode tersembunyi.
window.addEventListener('pageshow', () => {
    initPasswordToggles();

    document.querySelectorAll('[data-password-toggle]').forEach((toggleBtn) => {
        const targetId = toggleBtn.getAttribute('data-password-toggle');
        const inputEl = targetId ? document.getElementById(targetId) : null;
        if (!inputEl) return;

        setPasswordToggleState(toggleBtn, inputEl, false);
    });
});
