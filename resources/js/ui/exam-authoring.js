const escapeHtml = (value) => String(value ?? '').replace(/[&<>"']/g, (character) => ({
    '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;',
}[character]));

export function initializeExamAuthoring() {
    initializeExamSessionPackagePicker();

    const root = document.querySelector('[data-exam-authoring]');
    const form = document.querySelector('#exam-form');

    if (!root || !form) return;

    const cardArea = root.querySelector('[data-question-card]');
    const navigatorGrid = root.querySelector('[data-question-navigator]');
    const navCounter = root.querySelector('[data-question-counter]');
    const hiddenInput = root.querySelector('#questions-json');
    const errorMessage = root.querySelector('[data-question-error]');
    const imported = root.querySelector('[data-imported-questions]');

    let questions = [];
    let currentIndex = 0;

    try {
        questions = JSON.parse(imported?.textContent || '[]');
        if (!Array.isArray(questions)) questions = [];
    } catch {
        questions = [];
    }

    if (questions.length === 0) {
        questions.push(newBlankQuestion());
    }

    /** Returns a blank PG question object. */
    function newBlankQuestion() {
        return {
            type: 'multiple_choice',
            prompt: '',
            options: { A: '', B: '', C: '', D: '' },
            answer_key: 'A',
            sample_answer: null,
            points: 10,
        };
    }

    /** Saves the currently visible card back into `questions[currentIndex]`. */
    function saveCurrentCard() {
        if (!cardArea) return;
        const card = cardArea.querySelector('[data-question]');
        if (!card) return;

        const type = card.querySelector('[data-field="type"]')?.value || 'multiple_choice';
        const prompt = card.querySelector('[data-field="prompt"]')?.value.trim() || '';
        const points = Number(card.querySelector('[data-field="points"]')?.value) || 10;

        if (type === 'essay') {
            questions[currentIndex] = {
                type: 'essay',
                prompt,
                options: null,
                answer_key: null,
                sample_answer: card.querySelector('[data-field="sample_answer"]')?.value.trim() || null,
                points,
            };
        } else {
            const options = {};
            ['A', 'B', 'C', 'D'].forEach((key) => {
                options[key] = card.querySelector(`[data-option="${key}"]`)?.value.trim() || '';
            });
            questions[currentIndex] = {
                type: 'multiple_choice',
                prompt,
                options,
                answer_key: card.querySelector('[data-field="answer_key"]')?.value || 'A',
                sample_answer: null,
                points,
            };
        }
    }

    /** Checks if a question is "filled" enough to mark as answered in navigator. */
    function isQuestionFilled(q) {
        if (!q.prompt) return false;
        if (q.type === 'essay') return true;
        return !!(q.options?.A && q.options?.B);
    }

    /** Renders the navigator grid on the right sidebar. */
    function renderNavigator() {
        if (!navigatorGrid) return;
        navigatorGrid.innerHTML = questions.map((q, i) => {
            const isCurrent = i === currentIndex;
            const isFilled = isQuestionFilled(q);
            let cls;
            if (isCurrent) {
                cls = 'bg-indigo-700 text-white shadow-sm ring-2 ring-indigo-300 ring-offset-1';
            } else if (isFilled) {
                cls = 'bg-emerald-100 text-emerald-900 border border-emerald-300 hover:bg-emerald-200';
            } else {
                cls = 'bg-slate-100 text-slate-700 border border-slate-200 hover:bg-slate-200';
            }
            return `<button type="button" data-nav-to="${i}" class="flex h-9 w-9 items-center justify-center rounded-lg text-sm font-bold transition ${cls}" title="Soal ${i + 1}">${i + 1}</button>`;
        }).join('');

        if (navCounter) {
            navCounter.textContent = `Soal ${currentIndex + 1} / ${questions.length}`;
        }
    }

    /** Renders the single active question card. */
    function renderCard() {
        if (!cardArea) return;
        const question = questions[currentIndex];
        const isEssay = question.type === 'essay';
        const options = question.options || { A: '', B: '', C: '', D: '' };
        const answerKey = question.answer_key || 'A';
        const points = question.points || 10;
        const sampleAnswer = question.sample_answer || '';
        const total = questions.length;

        cardArea.innerHTML = `
        <fieldset class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm" data-question="${currentIndex}">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <div class="flex items-center gap-2.5">
                    <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-indigo-600 text-xs font-bold text-white">
                        ${currentIndex + 1}
                    </span>
                    <legend class="text-sm font-bold text-slate-800">Butir Soal #${currentIndex + 1}</legend>
                    <select data-field="type" class="rounded-lg border border-slate-300 bg-slate-50 px-2 py-1 text-xs font-semibold text-slate-700">
                        <option value="multiple_choice" ${!isEssay ? 'selected' : ''}>Pilihan Ganda</option>
                        <option value="essay" ${isEssay ? 'selected' : ''}>Esai / Uraian</option>
                    </select>
                </div>
                <button type="button" data-remove-question="${currentIndex}" class="rounded-lg border border-rose-200 bg-rose-50 px-2.5 py-1 text-xs font-semibold text-rose-700 hover:bg-rose-100 transition">
                    Hapus Soal
                </button>
            </div>

            <div class="mt-4 space-y-3">
                <div>
                    <label class="mb-1 block text-sm font-semibold text-slate-700">Pertanyaan</label>
                    <textarea data-field="prompt" rows="3" maxlength="5000" required placeholder="Tuliskan pertanyaan di sini..." class="block w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 placeholder:text-slate-400 outline-none focus:border-indigo-600 focus:ring-2 focus:ring-indigo-100">${escapeHtml(question.prompt)}</textarea>
                </div>

                ${!isEssay ? `
                <div class="pt-2">
                    <label class="mb-2 block text-xs font-bold uppercase tracking-wider text-slate-500">Pilihan Jawaban (A, B, C, D):</label>
                    <div class="grid gap-3 sm:grid-cols-2">
                        ${['A', 'B', 'C', 'D'].map((key) => `
                            <div class="flex items-center gap-2 rounded-xl border border-slate-200 bg-slate-50/50 p-2 focus-within:border-indigo-600 focus-within:bg-white">
                                <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-slate-200 text-xs font-bold text-slate-700">${key}</span>
                                <input data-option="${key}" value="${escapeHtml(options[key] ?? '')}" maxlength="1000" placeholder="Jawaban opsi ${key}..." class="w-full bg-transparent text-sm text-slate-900 outline-none">
                            </div>
                        `).join('')}
                    </div>
                </div>

                <div class="grid gap-3 sm:grid-cols-[1fr_160px] pt-1">
                    <div>
                        <label class="mb-1 block text-xs font-semibold text-slate-700">Kunci Jawaban yang Benar</label>
                        <select data-field="answer_key" required class="block w-full rounded-xl border border-slate-300 px-3 py-2 text-sm font-bold text-indigo-700 outline-none focus:border-indigo-600 focus:ring-2 focus:ring-indigo-100">
                            ${['A', 'B', 'C', 'D'].map((key) => `<option value="${key}" ${answerKey === key ? 'selected' : ''}>Opsi ${key} (Benar)</option>`).join('')}
                        </select>
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-semibold text-slate-700">Bobot Poin (1–100)</label>
                        <input data-field="points" type="number" value="${escapeHtml(points)}" min="1" max="100" required class="block w-full rounded-xl border border-slate-300 px-3 py-2 text-sm text-slate-900 outline-none focus:border-indigo-600 focus:ring-2 focus:ring-indigo-100">
                    </div>
                </div>
                ` : `
                <div class="rounded-xl border border-purple-200 bg-purple-50/40 p-3.5 space-y-2">
                    <label class="block text-xs font-bold text-purple-900">Pedoman / Kunci Jawaban Acuan Pengajar (Opsional)</label>
                    <textarea data-field="sample_answer" rows="2" maxlength="2000" placeholder="Tuliskan poin kunci atau rubrik penilaian untuk jawaban ini..." class="block w-full rounded-lg border border-purple-200 bg-white px-3 py-2 text-sm text-slate-900 outline-none focus:border-purple-600">${escapeHtml(sampleAnswer)}</textarea>
                    <div class="max-w-[160px] pt-1">
                        <label class="mb-1 block text-xs font-semibold text-slate-700">Bobot Poin (1–100)</label>
                        <input data-field="points" type="number" value="${escapeHtml(points)}" min="1" max="100" required class="block w-full rounded-xl border border-slate-300 px-3 py-2 text-sm text-slate-900 outline-none focus:border-indigo-600 focus:ring-2 focus:ring-indigo-100">
                    </div>
                </div>
                `}
            </div>

            <div class="mt-5 flex items-center justify-between border-t border-slate-100 pt-4">
                <button type="button" data-nav-prev
                    class="inline-flex items-center gap-1.5 rounded-xl border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50 disabled:opacity-40 disabled:cursor-not-allowed"
                    ${currentIndex === 0 ? 'disabled' : ''}>
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18"/></svg>
                    Sebelumnya
                </button>
                <span class="text-xs font-semibold text-slate-400">${currentIndex + 1} / ${total}</span>
                <button type="button" data-nav-next
                    class="inline-flex items-center gap-1.5 rounded-xl border border-indigo-300 bg-indigo-50 px-4 py-2 text-sm font-semibold text-indigo-800 transition hover:bg-indigo-100 disabled:opacity-40 disabled:cursor-not-allowed"
                    ${currentIndex === total - 1 ? 'disabled' : ''}>
                    Berikutnya
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/></svg>
                </button>
            </div>
        </fieldset>`;
    }

    /** Full render: card + navigator. */
    function render() {
        renderCard();
        renderNavigator();
    }

    /** Navigate to a specific index (saves current card first). */
    function goTo(index) {
        saveCurrentCard();
        currentIndex = Math.max(0, Math.min(index, questions.length - 1));
        render();
        // Smooth scroll to card area on mobile
        cardArea?.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }

    // --- Event delegation ---

    root.addEventListener('input', () => {
        // Live-save so navigator can reflect filled state
        saveCurrentCard();
        renderNavigator();
    });

    root.addEventListener('change', (event) => {
        if (event.target.matches('[data-field="type"]')) {
            saveCurrentCard();
            render();
            return;
        }
        saveCurrentCard();
        renderNavigator();
    });

    root.addEventListener('click', (event) => {
        // Navigator grid button
        const navBtn = event.target.closest('[data-nav-to]');
        if (navBtn) {
            goTo(Number(navBtn.dataset.navTo));
            return;
        }

        // Previous / Next buttons
        if (event.target.closest('[data-nav-prev]')) {
            goTo(currentIndex - 1);
            return;
        }
        if (event.target.closest('[data-nav-next]')) {
            goTo(currentIndex + 1);
            return;
        }

        // Add question
        const addButton = event.target.closest('[data-add-question]');
        if (addButton) {
            saveCurrentCard();
            questions.push(newBlankQuestion());
            currentIndex = questions.length - 1;
            render();
            cardArea?.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            return;
        }

        // Remove question
        const removeButton = event.target.closest('[data-remove-question]');
        if (removeButton) {
            saveCurrentCard();
            const index = Number(removeButton.dataset.removeQuestion);
            questions.splice(index, 1);
            if (questions.length === 0) {
                questions.push(newBlankQuestion());
            }
            currentIndex = Math.min(currentIndex, questions.length - 1);
            render();
        }
    });

    // --- Form submit ---
    form.addEventListener('submit', (event) => {
        saveCurrentCard();

        if (questions.length === 0 || questions.length > 100) {
            event.preventDefault();
            errorMessage.textContent = 'Tambahkan minimal 1 butir soal dan maksimal 100 butir soal.';
            root.scrollIntoView({ behavior: 'smooth', block: 'start' });
            return;
        }

        for (let i = 0; i < questions.length; i++) {
            const q = questions[i];
            if (!q.prompt) {
                event.preventDefault();
                errorMessage.textContent = `Pertanyaan pada soal nomor ${i + 1} belum diisi.`;
                // Navigate to the offending question
                currentIndex = i;
                render();
                root.scrollIntoView({ behavior: 'smooth', block: 'start' });
                return;
            }
            if (q.type === 'multiple_choice' && q.options) {
                if (!q.options.A || !q.options.B) {
                    event.preventDefault();
                    errorMessage.textContent = `Pilihan jawaban A dan B pada soal nomor ${i + 1} wajib diisi.`;
                    currentIndex = i;
                    render();
                    root.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    return;
                }
            }
        }

        errorMessage.textContent = '';
        hiddenInput.value = JSON.stringify(questions);
    });

    // Initial render
    render();
}

function initializeExamSessionPackagePicker() {
    const subjectSelect = document.querySelector('[data-exam-subject-select]');
    const packageSelect = document.querySelector('[data-exam-package-select]');

    if (!subjectSelect || !packageSelect) {
        return;
    }

    const placeholder = packageSelect.options[0];
    const packageOptions = Array.from(packageSelect.options).slice(1);

    const updatePackages = (resetSelection = false) => {
        const subjectId = subjectSelect.value;
        let availablePackages = 0;

        packageOptions.forEach((option) => {
            const isAvailable = subjectId !== '' && option.dataset.subjectId === subjectId;
            option.hidden = !isAvailable;
            option.disabled = !isAvailable;
            availablePackages += isAvailable ? 1 : 0;
        });

        packageSelect.disabled = subjectId === '' || availablePackages === 0;
        placeholder.textContent = subjectId === ''
            ? 'Pilih mapel terlebih dahulu'
            : (availablePackages === 0 ? 'Belum ada paket untuk mapel ini' : 'Pilih paket soal');

        if (resetSelection || packageSelect.selectedOptions[0]?.disabled) {
            packageSelect.value = '';
        }
    };

    subjectSelect.addEventListener('change', () => updatePackages(true));
    updatePackages();
}
