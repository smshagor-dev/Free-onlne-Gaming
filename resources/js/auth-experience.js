import '../css/auth-experience.css';

const modal = document.getElementById('fg-auth-dialog');
let lastTrigger = null;

function setAuthPanel(panel) {
    if (!modal) return;
    const selected = panel === 'register' ? 'register' : 'login';
    modal.querySelectorAll('[data-auth-panel]').forEach(node => {
        node.hidden = node.dataset.authPanel !== selected;
    });
    const heading = modal.querySelector('#fg-auth-modal-title');
    if (heading) heading.textContent = selected === 'register' ? 'Create your account' : 'Sign in to FreeGames';
}

function openAuthModal(panel, trigger = null) {
    if (!modal) return false;
    if (trigger) lastTrigger = trigger;
    setAuthPanel(panel);
    modal.hidden = false;
    document.body.classList.add('fg-modal-open');
    const firstInput = modal.querySelector('[data-auth-panel]:not([hidden]) input:not([type="hidden"])');
    (firstInput || modal.querySelector('.fg-auth-modal-close'))?.focus();
    return true;
}

function closeAuthModal() {
    if (!modal || modal.hidden) return;
    modal.hidden = true;
    document.body.classList.remove('fg-modal-open');
    lastTrigger?.focus();
    lastTrigger = null;
}

document.addEventListener('click', event => {
    const trigger = event.target.closest('[data-auth-open]');
    if (trigger && modal) {
        event.preventDefault();
        openAuthModal(trigger.dataset.authOpen, trigger);
        return;
    }
    if (event.target.closest('[data-auth-close]')) closeAuthModal();
    const copyButton = event.target.closest('[data-copy-target]');
    if (copyButton) {
        const input = document.getElementById(copyButton.dataset.copyTarget);
        if (input && navigator.clipboard) {
            navigator.clipboard.writeText(input.value).then(() => {
                const label = copyButton.textContent;
                copyButton.textContent = 'Copied';
                setTimeout(() => { copyButton.textContent = label; }, 1400);
            }).catch(() => { copyButton.textContent = 'Copy unavailable'; });
        }
    }

    const toggle = event.target.closest('[data-password-toggle]');
    if (toggle) {
        const field = document.getElementById(toggle.dataset.passwordToggle);
        if (!field) return;
        const revealing = field.type === 'password';
        field.type = revealing ? 'text' : 'password';
        toggle.textContent = revealing ? 'Hide' : 'Show';
        toggle.setAttribute('aria-pressed', String(revealing));
        toggle.setAttribute('aria-label', revealing ? 'Hide password' : 'Show password');
    }
});

document.addEventListener('keydown', event => {
    if (!modal || modal.hidden) return;
    if (event.key === 'Escape') {
        event.preventDefault();
        closeAuthModal();
        return;
    }
    if (event.key !== 'Tab') return;
    const focusable = [...modal.querySelectorAll('a[href], button:not([disabled]), input:not([disabled]), select:not([disabled]), summary')]
        .filter(el => !el.closest('[hidden]') && el.getClientRects().length);
    if (!focusable.length) return;
    const first = focusable[0];
    const last = focusable[focusable.length - 1];
    if (event.shiftKey && document.activeElement === first) {
        event.preventDefault();
        last.focus();
    } else if (!event.shiftKey && document.activeElement === last) {
        event.preventDefault();
        first.focus();
    }
});

if (modal?.dataset.autoOpen) {
    openAuthModal(modal.dataset.autoOpen);
}

// Inline password confirmation and strength feedback, without replacing server validation.
document.querySelectorAll('.fg-auth-form').forEach(form => {
    const password = form.querySelector('input[name="password"]');
    const confirmation = form.querySelector('input[name="password_confirmation"]');
    if (password && confirmation) {
        const validateMatch = () => confirmation.setCustomValidity(
            confirmation.value && password.value !== confirmation.value ? 'Passwords must match.' : ''
        );
        password.addEventListener('input', validateMatch);
        confirmation.addEventListener('input', validateMatch);
        form.addEventListener('submit', validateMatch);
    }
    const strength = form.querySelector('[data-password-strength]');
    if (password && strength) {
        const meter = strength.querySelector('meter');
        password.addEventListener('input', () => {
            const value = password.value;
            const score = Math.min(4, Number(value.length >= 8) + Number(value.length >= 12) +
                Number(/[A-Z]/.test(value) && /[a-z]/.test(value)) + Number(/[0-9]/.test(value) && /[^a-zA-Z0-9]/.test(value)));
            meter.value = score;
            strength.firstChild.textContent = 'Password strength: ' + (value ? ['Very weak', 'Weak', 'Fair', 'Good', 'Strong'][score] : 'Use 8 or more characters') + '. ';
        });
    }
});

// Populate only the optional country/currency controls; do not block sign-up if the list is unavailable.
const countryInputs = [...document.querySelectorAll('[data-country-select]')];
if (countryInputs.length) {
    const source = countryInputs[0].dataset.source;
    fetch(source, {headers: {Accept: 'application/json'}, credentials: 'same-origin'})
        .then(response => {
            if (!response.ok) throw new Error('Country list unavailable');
            return response.json();
        })
        .then(countries => {
            if (!Array.isArray(countries)) return;
            countryInputs.forEach(select => {
                const form = select.closest('form');
                const currency = form?.querySelector('[data-currency-select]');
                if (!currency) return;
                countries.filter(country => country?.name).forEach(country => {
                    select.add(new Option(country.name, country.name));
                    if (country.currency && ![...currency.options].some(option => option.value === country.currency)) {
                        currency.add(new Option(country.currency, country.currency));
                    }
                });
                select.value = select.dataset.selected || '';
                currency.value = currency.dataset.selected || '';
                select.addEventListener('change', () => {
                    const choice = countries.find(country => country.name === select.value);
                    currency.value = choice?.currency || '';
                    const mobile = form.querySelector('input[name="mobile_number"]');
                    if (mobile && !mobile.value.trim() && choice?.dial) mobile.value = '+' + choice.dial + ' ';
                });
            });
        })
        .catch(() => {});
}

// Accessible AJAX verification-code resend, throttled visually by the current page.
const resend = document.querySelector('[data-verification-resend]');
if (resend) {
    const button = resend.querySelector('button[type="submit"]');
    const message = document.querySelector('[data-verification-message]');
    let remaining = 0;
    let interval;

    resend.addEventListener('submit', async event => {
        event.preventDefault();
        if (remaining) return;
        const formData = new FormData(resend);
        button.disabled = true;
        if (message) { message.textContent = 'Sending a new code…'; message.hidden = false; }
        try {
            const response = await fetch(resend.action, {
                method: 'POST', body: formData, credentials: 'same-origin',
                headers: {Accept: 'application/json'},
            });
            const data = await response.json();
            if (!response.ok || !data.success) throw new Error(data.message || 'Could not send verification code.');
            if (message) message.textContent = data.message || 'A new code has been sent.';
            remaining = 60;
            button.textContent = 'Resend available in ' + remaining + 's';
            interval = setInterval(() => {
                remaining--;
                button.textContent = remaining ? 'Resend available in ' + remaining + 's' : 'Resend verification code';
                if (!remaining) { clearInterval(interval); button.disabled = false; }
            }, 1000);
        } catch (error) {
            if (message) message.textContent = error.message || 'Unable to resend. Try again.';
            button.disabled = false;
        }
    });
}
