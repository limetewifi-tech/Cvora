const form = document.querySelector('[data-autosave]');

if (form) {
    const status = document.querySelector('[data-save-status]');
    const preview = document.querySelector('[data-preview]');
    const token = document.querySelector('meta[name="csrf-token"]')?.content;
    let timer = null;
    let controller = null;
    let generation = 0;

    const schedule = () => {
        if (status) {
            status.textContent = 'Modifications en attente…';
        }

        clearTimeout(timer);
        timer = setTimeout(save, 800);
    };

    const save = async () => {
        const current = ++generation;
        controller?.abort();
        controller = new AbortController();

        if (status) {
            status.textContent = 'Enregistrement…';
        }

        try {
            const response = await fetch(form.dataset.autosave, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': token,
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: new FormData(form),
                signal: controller.signal,
            });
            const data = await response.json().catch(() => ({}));

            if (current !== generation) {
                return;
            }

            if (response.status === 409) {
                if (status) {
                    status.textContent = data.message || 'Rechargez la page avant d’enregistrer.';
                }

                return;
            }

            if (!response.ok) {
                if (status) {
                    status.textContent = data.message || 'Échec de l’enregistrement.';
                }

                return;
            }

            const lock = form.querySelector('[name="lock_version"]');

            if (lock && data.lock_version !== undefined) {
                lock.value = data.lock_version;
            }

            if (status) {
                status.textContent = data.updated_at ? `Enregistré à ${data.updated_at}` : 'Enregistré';
            }

            if (preview) {
                const url = new URL(preview.src, window.location.origin);
                url.searchParams.set('t', Date.now().toString());
                preview.src = url.toString();
            }
        } catch (error) {
            if (error.name === 'AbortError' || current !== generation) {
                return;
            }

            if (status) {
                status.textContent = 'Connexion interrompue. Réessayez.';
            }
        }
    };

    form.addEventListener('input', schedule);
    form.addEventListener('change', schedule);
    form.addEventListener('submit', () => {
        generation++;
        clearTimeout(timer);
        controller?.abort();
    });

    document.querySelectorAll('[data-step]').forEach((button) => {
        button.addEventListener('click', () => {
            const target = document.getElementById(button.dataset.step);
            target?.scrollIntoView({ behavior: 'smooth', block: 'start' });
            document.querySelectorAll('[data-step]').forEach((item) => item.removeAttribute('aria-current'));
            button.setAttribute('aria-current', 'step');
        });
    });
}
