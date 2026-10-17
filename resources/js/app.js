import './editor';

document.documentElement.classList.add('js');

document.querySelectorAll('[data-toggle]').forEach((button) => {
    const target = document.querySelector(button.getAttribute('data-toggle'));

    if (!target) {
        return;
    }

    button.addEventListener('click', () => {
        const open = target.classList.toggle('is-open');
        button.setAttribute('aria-expanded', open ? 'true' : 'false');
    });
});

document.querySelectorAll('[data-accordion]').forEach((item) => {
    const button = item.querySelector('button');
    const panel = item.querySelector('[data-panel]');

    button?.addEventListener('click', () => {
        const open = button.getAttribute('aria-expanded') === 'true';
        button.setAttribute('aria-expanded', open ? 'false' : 'true');
        panel?.toggleAttribute('hidden', open);
    });
});

document.querySelectorAll('[data-ai-form]').forEach((form) => {
    form.addEventListener('submit', () => {
        const button = form.querySelector('[type="submit"]');

        if (button) {
            button.dataset.label = button.textContent;
            button.textContent = 'Génération en cours…';
            button.setAttribute('aria-busy', 'true');
            button.disabled = true;
        }
    });
});
