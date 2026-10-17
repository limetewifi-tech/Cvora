const form = document.querySelector('[data-autosave]');

if (form) {
    const status = document.querySelector('[data-save-status]');
    const preview = document.querySelector('[data-preview]');
    const progress = document.querySelector('[data-editor-progress]');
    const progressBar = progress?.querySelector('.progress-bar');
    const progressLabel = document.querySelector('[data-editor-progress-label]');
    const token = document.querySelector('meta[name="csrf-token"]')?.content;
    let timer = null;
    let controller = null;
    let generation = 0;

    const refreshProgress = () => {
        const buttons = [...document.querySelectorAll('[data-step]')];

        if (!buttons.length || !progress || !progressBar) {
            return;
        }

        let done = 0;

        buttons.forEach((button) => {
            const section = document.getElementById(button.dataset.step);
            const filled = [...(section?.querySelectorAll('input, textarea, select') ?? [])].some((field) => {
                if (field.type === 'hidden' || field.type === 'checkbox' || field.type === 'radio') {
                    return false;
                }

                return field.value.trim() !== '';
            });

            button.classList.toggle('is-done', filled);

            if (filled) {
                done += 1;
            }
        });

        const value = Math.round((done / buttons.length) * 100);
        progressBar.style.width = `${value}%`;
        progress.setAttribute('aria-valuenow', String(done));
        progress.setAttribute('aria-valuemax', String(buttons.length));

        if (progressLabel) {
            progressLabel.textContent = `${done} étape${done > 1 ? 's' : ''} renseignée${done > 1 ? 's' : ''} sur ${buttons.length}`;
        }
    };

    const schedule = () => {
        if (status) {
            status.textContent = 'Modifications en attente…';
        }

        refreshProgress();
        refreshReadiness();
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
            refreshProgress();
        });
    });

    const fieldByName = (name) => form.querySelector(`[name="${CSS.escape(name)}"]`);

    const fieldValue = (name) => {
        const field = fieldByName(name);

        if (!field || field.type === 'hidden' || field.type === 'checkbox' || field.type === 'radio') {
            return '';
        }

        return field.value.trim();
    };

    const rowReady = (prefix, names, any) => {
        const indexes = new Set();

        form.querySelectorAll('input, textarea').forEach((field) => {
            const match = field.name.match(new RegExp(`^${prefix}\\[(\\d+)\\]`));

            if (match) {
                indexes.add(match[1]);
            }
        });

        return [...indexes].some((index) => {
            const values = names.map((name) => fieldValue(`${prefix}[${index}][${name}]`));

            return any ? values.some(Boolean) : values.every(Boolean);
        });
    };

    const refreshReadiness = () => {
        const items = [...document.querySelectorAll('[data-readiness-item]')];
        let done = 0;

        items.forEach((item) => {
            const names = JSON.parse(item.dataset.readinessFields || '[]');
            const mode = item.dataset.readinessMode;
            let ready = false;

            if (mode === 'row') {
                ready = rowReady(item.dataset.readinessPrefix, names, false);
            } else if (mode === 'row-any') {
                ready = rowReady(item.dataset.readinessPrefix, names, true);
            } else if (mode === 'any') {
                ready = names.some((name) => fieldValue(name) !== '');
            } else {
                ready = names.every((name) => fieldValue(name) !== '');
            }

            item.dataset.done = ready ? '1' : '0';
            const icon = item.querySelector('[data-readiness-icon]');

            if (icon) {
                icon.className = `bi ${ready ? 'bi-check-circle-fill text-success' : 'bi-circle text-secondary'} me-1`;
            }

            if (ready) {
                done += 1;
            }
        });

        const label = document.querySelector('[data-readiness-label]');

        if (label) {
            label.textContent = `${done} élément${done > 1 ? 's' : ''} prêt${done > 1 ? 's' : ''} sur ${items.length}`;
        }
    };

    const fitPreview = () => {
        document.querySelectorAll('[data-a4-frame]').forEach((frame) => {
            frame.style.height = '';
            const iframe = frame.querySelector('iframe');

            if (iframe) {
                iframe.style.transform = '';
            }
        });
    };

    const coachField = (target, index) => {
        if (target === 'summary' || target === 'headline') {
            return form.querySelector(`[data-coach-field="${target}"]`);
        }

        const selector = `[data-coach-field="${target}"]`;

        if (index === null || index === undefined) {
            return form.querySelector(selector);
        }

        return form.querySelector(`${selector}[data-coach-index="${index}"]`);
    };

    document.querySelectorAll('[data-jump]').forEach((button) => {
        button.addEventListener('click', () => {
            document.getElementById(button.dataset.jump)?.scrollIntoView({ behavior: 'smooth', block: 'start' });
        });
    });

    document.querySelectorAll('[data-suggest]').forEach((button) => {
        button.addEventListener('click', async () => {
            const field = fieldByName(button.dataset.suggestField || '');
            const box = button.closest('.col-12, .card-body')?.querySelector('[data-suggest-output]');

            if (!field || !box) {
                return;
            }

            button.disabled = true;
            const body = new FormData();
            body.set('text', field.value);
            body.set('tone', 'professionnel');

            try {
                const response = await fetch(button.dataset.suggestUrl, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': token,
                        Accept: 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body,
                });
                const data = await response.json().catch(() => ({}));
                box.hidden = false;
                box.replaceChildren();

                if (!response.ok || !data.successful) {
                    const error = document.createElement('p');
                    error.className = 'small text-danger mb-0';
                    error.textContent = data.error || 'La proposition est indisponible.';
                    box.append(error);

                    return;
                }

                const proposal = data.data?.proposal || '';
                const note = document.createElement('p');
                note.className = 'small mb-2';
                note.textContent = proposal || ((data.missing || []).length
                    ? `Information manquante : ${data.missing.join(', ')}.`
                    : 'Aucune reformulation n\'a été renvoyée.');
                box.append(note);

                if (!proposal) {
                    return;
                }

                const use = document.createElement('button');
                use.type = 'button';
                use.className = 'btn btn-sm btn-primary';
                use.textContent = 'Utiliser cette proposition';
                use.addEventListener('click', () => {
                    field.value = proposal;
                    field.dispatchEvent(new Event('input', { bubbles: true }));
                    field.focus();
                });
                box.append(use);
            } catch {
                box.hidden = false;
                box.textContent = 'Connexion interrompue. Réessayez.';
            } finally {
                button.disabled = false;
            }
        });
    });

    const coach = document.querySelector('[data-coach]');

    if (coach) {
        const log = coach.querySelector('[data-coach-log]');
        const input = coach.querySelector('[data-coach-input]');
        const statusLine = coach.querySelector('[data-coach-status]');
        const send = coach.querySelector('[data-coach-send]');
        const history = [];

        const bubble = (role, text) => {
            const paragraph = document.createElement('p');
            paragraph.className = `cvora-coach-message is-${role} small mb-0`;
            paragraph.textContent = text;
            log.append(paragraph);
            log.scrollTop = log.scrollHeight;
        };

        const submitCoach = async (message) => {
            const text = message.trim();

            if (!text || send.disabled) {
                return;
            }

            send.disabled = true;
            statusLine.textContent = 'Envoi…';
            bubble('user', text);
            input.value = '';

            try {
                const response = await fetch(coach.dataset.coachUrl, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': token,
                        Accept: 'application/json',
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: JSON.stringify({ message: text, history: history.slice(-6) }),
                });
                const data = await response.json().catch(() => ({}));

                if (!response.ok || !data.successful) {
                    statusLine.textContent = data.error || 'La conversation est indisponible.';

                    return;
                }

                history.push({ role: 'user', content: text });

                if (data.reply) {
                    history.push({ role: 'assistant', content: data.reply });
                    bubble('assistant', data.reply);
                }

                (data.missing || []).forEach((item) => {
                    const note = document.createElement('p');
                    note.className = 'small text-secondary mb-0';
                    note.textContent = `Information manquante : ${item}`;
                    log.append(note);
                });

                (data.suggestions || []).forEach((suggestion) => {
                    const wrap = document.createElement('div');
                    const label = document.createElement('p');
                    label.className = 'small fw-semibold mb-1';
                    label.textContent = suggestion.label || 'Proposition';
                    const quote = document.createElement('p');
                    quote.className = 'small mb-2';
                    quote.textContent = suggestion.text;
                    const use = document.createElement('button');
                    use.type = 'button';
                    use.className = 'btn btn-sm btn-outline-primary';
                    use.textContent = 'Utiliser cette proposition';
                    use.addEventListener('click', () => {
                        const field = coachField(suggestion.target, suggestion.index);

                        if (!field) {
                            statusLine.textContent = 'Ajoutez d\'abord la ligne correspondante, puis utilisez la proposition.';

                            return;
                        }

                        field.value = suggestion.text;
                        field.dispatchEvent(new Event('input', { bubbles: true }));
                        field.focus();
                        statusLine.textContent = 'Proposition placée dans le formulaire.';
                    });
                    wrap.append(label, quote, use);
                    log.append(wrap);
                });

                statusLine.textContent = typeof data.credits_remaining === 'number'
                    ? `Crédits restants : ${data.credits_remaining}.`
                    : '';
            } catch {
                statusLine.textContent = 'Connexion interrompue. Réessayez.';
            } finally {
                send.disabled = false;
                log.scrollTop = log.scrollHeight;
            }
        };

        send.addEventListener('click', () => submitCoach(input.value));
        input.addEventListener('keydown', (event) => {
            if (event.key === 'Enter' && (event.ctrlKey || event.metaKey)) {
                event.preventDefault();
                submitCoach(input.value);
            }
        });
        coach.querySelectorAll('[data-coach-prompt]').forEach((button) => {
            button.addEventListener('click', () => submitCoach(button.textContent || ''));
        });
    }

    refreshReadiness();
    fitPreview();
    refreshProgress();
}
