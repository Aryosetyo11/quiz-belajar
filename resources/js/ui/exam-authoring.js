const escapeHtml = (value) => String(value ?? '').replace(/[&<>"']/g, (character) => ({
    '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;',
}[character]));

export function initializeExamAuthoring() {
    const countdown = document.querySelector('[data-exam-countdown]');
    if (countdown) {
        const output = countdown.querySelector('[data-countdown-output]');
        const form = document.querySelector('[data-exam-submit]');
        const submitButton = form?.querySelector('button[type="submit"]');
        const deadline = Number(countdown.dataset.examCountdown);
        let timer;
        const updateCountdown = () => {
            const secondsLeft = Math.max(0, Math.floor((deadline - Date.now()) / 1000));
            output.textContent = `${Math.floor(secondsLeft / 60).toString().padStart(2, '0')}:${(secondsLeft % 60).toString().padStart(2, '0')}`;
            if (secondsLeft === 0 && form) {
                window.clearInterval(timer);
                if (submitButton) submitButton.disabled = true;
                form.requestSubmit();
            }
        };
        updateCountdown();
        timer = window.setInterval(updateCountdown, 1000);
    }

    const root = document.querySelector('[data-exam-authoring]');
    const form = document.querySelector('#exam-form');

    if (!root || !form) return;

    const list = root.querySelector('[data-question-list]');
    const hiddenInput = root.querySelector('#questions-json');
    const errorMessage = root.querySelector('[data-question-error]');
    const imported = root.querySelector('[data-imported-questions]');
    let questions = [];

    try {
        questions = JSON.parse(imported?.textContent || '[]');
        if (!Array.isArray(questions)) questions = [];
    } catch {
        questions = [];
    }

    const render = () => {
        list.innerHTML = questions.map((question, index) => `
            <fieldset class="rounded-lg border border-slate-200 p-4" data-question="${index}">
                <legend class="px-1 text-sm font-semibold text-slate-800">Soal ${index + 1}</legend>
                <div class="flex justify-end"><button type="button" data-remove-question="${index}" class="rounded px-2 py-1 text-sm font-semibold text-rose-700 hover:bg-rose-50">Hapus soal</button></div>
                <label class="mb-1.5 block text-sm font-medium text-slate-700">Pertanyaan</label>
                <textarea data-field="prompt" rows="2" maxlength="5000" required class="block w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm">${escapeHtml(question.prompt)}</textarea>
                <div class="mt-3 grid gap-3 sm:grid-cols-2">
                    ${['A', 'B', 'C', 'D'].map((key) => `<label class="block text-sm font-medium text-slate-700">Pilihan ${key}<input data-option="${key}" value="${escapeHtml(question.options?.[key])}" maxlength="1000" required class="mt-1.5 block w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm"></label>`).join('')}
                </div>
                <div class="mt-3 grid gap-3 sm:grid-cols-[1fr_160px]">
                    <label class="block text-sm font-medium text-slate-700">Kunci jawaban<select data-field="answer_key" required class="mt-1.5 block w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm">${['A', 'B', 'C', 'D'].map((key) => `<option value="${key}" ${question.answer_key === key ? 'selected' : ''}>${key}</option>`).join('')}</select></label>
                    <label class="block text-sm font-medium text-slate-700">Poin<input data-field="points" type="number" value="${escapeHtml(question.points || 1)}" min="1" max="100" required class="mt-1.5 block w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm"></label>
                </div>
            </fieldset>`).join('');
    };

    const readForm = () => {
        questions = [...list.querySelectorAll('[data-question]')].map((card) => ({
            prompt: card.querySelector('[data-field="prompt"]').value.trim(),
            options: Object.fromEntries(['A', 'B', 'C', 'D'].map((key) => [key, card.querySelector(`[data-option="${key}"]`).value.trim()])),
            answer_key: card.querySelector('[data-field="answer_key"]').value,
            points: Number(card.querySelector('[data-field="points"]').value),
        }));
    };

    root.addEventListener('input', readForm);
    root.addEventListener('change', readForm);
    root.addEventListener('click', (event) => {
        const addButton = event.target.closest('[data-add-question]');
        const removeButton = event.target.closest('[data-remove-question]');

        if (addButton) {
            readForm();
            questions.push({ prompt: '', options: { A: '', B: '', C: '', D: '' }, answer_key: 'A', points: 1 });
            render();
        }
        if (removeButton) {
            readForm();
            questions.splice(Number(removeButton.dataset.removeQuestion), 1);
            render();
        }
    });
    form.addEventListener('submit', (event) => {
        readForm();
        if (questions.length === 0 || questions.length > 100) {
            event.preventDefault();
            errorMessage.textContent = 'Tambahkan minimal satu soal dan maksimal 100 soal.';
            return;
        }
        if (questions.some((question) => !question.prompt || Object.values(question.options).some((option) => !option))) {
            event.preventDefault();
            errorMessage.textContent = 'Lengkapi pertanyaan dan pilihan A–D pada setiap soal.';
            return;
        }
        hiddenInput.value = JSON.stringify(questions);
    });

    if (questions.length === 0) questions.push({ prompt: '', options: { A: '', B: '', C: '', D: '' }, answer_key: 'A', points: 1 });
    render();
}
