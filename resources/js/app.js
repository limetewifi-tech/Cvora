import * as bootstrap from 'bootstrap';
import './editor';

window.bootstrap = bootstrap;
document.documentElement.classList.add('js');

const themeIcon = (theme) => (theme === 'dark' ? 'bi-sun' : 'bi-moon-stars');

const applyThemeIcon = () => {
    const theme = document.documentElement.getAttribute('data-bs-theme') === 'dark' ? 'dark' : 'light';

    document.querySelectorAll('[data-theme-icon]').forEach((icon) => {
        icon.className = `bi ${themeIcon(theme)}`;
    });

    document.querySelectorAll('[data-theme-toggle]').forEach((button) => {
        button.setAttribute('aria-pressed', theme === 'dark' ? 'true' : 'false');
        button.setAttribute('aria-label', theme === 'dark' ? 'Activer le mode clair' : 'Activer le mode sombre');
    });
};

document.querySelectorAll('[data-theme-toggle]').forEach((button) => {
    button.addEventListener('click', () => {
        const next = document.documentElement.getAttribute('data-bs-theme') === 'dark' ? 'light' : 'dark';
        document.documentElement.setAttribute('data-bs-theme', next);
        localStorage.setItem('cvora-theme', next);
        applyThemeIcon();
    });
});

applyThemeIcon();

document.querySelectorAll('form').forEach((form) => {
    form.addEventListener('submit', (event) => {
        if (event.submitter?.name === 'action') {
            return;
        }

        if (!form.checkValidity()) {
            event.preventDefault();
            event.stopPropagation();
            form.classList.add('was-validated');
            form.querySelector(':invalid')?.focus();

            return;
        }

        if (form.hasAttribute('data-ai-form')) {
            return;
        }

        const button = event.submitter;

        if (button?.hasAttribute('data-loading')) {
            button.disabled = true;
            button.setAttribute('aria-busy', 'true');
            const spinner = document.createElement('span');
            spinner.className = 'spinner-border spinner-border-sm me-2';
            spinner.setAttribute('aria-hidden', 'true');
            button.prepend(spinner);
        }
    });
});

document.querySelectorAll('[data-print]').forEach((button) => {
    button.addEventListener('click', () => window.print());
});

document.querySelectorAll('[data-ai-form]').forEach((form) => {
    form.addEventListener('submit', (event) => {
        if (!form.checkValidity()) {
            return;
        }

        const button = event.submitter || form.querySelector('[type="submit"]');

        if (!button || button.disabled) {
            return;
        }

        button.dataset.label = button.textContent.trim();
        button.replaceChildren();
        const spinner = document.createElement('span');
        spinner.className = 'spinner-border spinner-border-sm me-2';
        spinner.setAttribute('aria-hidden', 'true');
        button.append(spinner, document.createTextNode('Génération en cours…'));
        button.setAttribute('aria-busy', 'true');
        button.disabled = true;
    });
});

document.querySelectorAll('.alert[data-autohide]').forEach((alert) => {
    window.setTimeout(() => {
        bootstrap.Alert.getOrCreateInstance(alert).close();
    }, 6000);
});
