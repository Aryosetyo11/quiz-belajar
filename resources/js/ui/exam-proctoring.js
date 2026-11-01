/**
 * RuangUji – Exam Proctoring System
 * Handles fullscreen, tab-switch detection, keyboard blocking,
 * violation counting, question navigation, auto-save, and timer warnings.
 */

export function initializeExamProctoring() {
    const root = document.querySelector('[data-exam-proctoring]');
    if (!root) return;

    const examForm = document.querySelector('[data-exam-submit]');
    if (!examForm) return;

    document.body.classList.add('exam-active');

    // ── State ──────────────────────────────────────────────────────────────────
    const isProctoringEnabled = root.dataset.enableProctoring !== '0';
    const MAX_VIOLATIONS = parseInt(root.dataset.maxViolations || '3', 10);
    const logUrl = root.dataset.logViolationUrl;
    let violations = parseInt(root.dataset.currentViolations || '0', 10);
    let flaggedQuestions = new Set();
    let answeredQuestions = new Set();
    let isSubmitting = false;
    let lastViolationAt = 0;

    // Clipboard actions stay disabled for every active exam, even without proctoring.
    ['copy', 'cut', 'paste'].forEach((eventName) => {
        document.addEventListener(eventName, (event) => event.preventDefault(), true);
    });

    document.addEventListener('keydown', (event) => {
        if ((event.ctrlKey || event.metaKey) && ['c', 'x', 'v'].includes(event.key.toLowerCase())) {
            event.preventDefault();
            event.stopPropagation();
        }

        if ((event.ctrlKey || event.metaKey) && event.key.toLowerCase() === 'a' && !event.target.closest('input, textarea, [contenteditable="true"]')) {
            event.preventDefault();
            event.stopPropagation();
        }
    }, true);

    document.addEventListener('selectstart', (event) => {
        if (event.target.closest('input, textarea, [contenteditable="true"]')) return;

        event.preventDefault();
    }, true);

    // ── Elements ───────────────────────────────────────────────────────────────
    const violationCounter = document.querySelector('[data-violation-count]');
    const warningOverlay = document.querySelector('[data-warning-overlay]');
    const warningMessage = document.querySelector('[data-warning-message]');
    const warningDismiss = document.querySelector('[data-warning-dismiss]');
    const navItems = document.querySelectorAll('[data-nav-question]');
    const questionCards = document.querySelectorAll('[data-question-card]');
    const confirmDialog = document.querySelector('[data-confirm-dialog]');
    const confirmProceed = document.querySelector('[data-confirm-proceed]');
    const confirmCancel = document.querySelector('[data-confirm-cancel]');
    const unansweredCount = document.querySelector('[data-unanswered-count]');
    const fullscreenButton = document.querySelector('[data-request-fullscreen]');
    const fullscreenLock = document.querySelector('[data-fullscreen-lock]');
    const fullscreenResume = document.querySelector('[data-fullscreen-resume]');
    const answerSaveStatus = document.querySelector('[data-answer-save-status]');

    // ── Fullscreen ─────────────────────────────────────────────────────────────
    if (isProctoringEnabled) {
    function isFullscreen() {
        return !!(document.fullscreenElement || document.webkitFullscreenElement || document.mozFullScreenElement);
    }

    function requestFullscreen() {
        const el = document.documentElement;
        if (el.requestFullscreen) { return el.requestFullscreen(); }
        if (el.webkitRequestFullscreen) { return el.webkitRequestFullscreen(); }
        if (el.mozRequestFullScreen) { return el.mozRequestFullScreen(); }

        return Promise.reject(new Error('Browser tidak mendukung mode layar penuh.'));
    }

    let hasEnteredFullscreen = isFullscreen();
    let ignoreVisibilityUntil = Date.now() + 500;

    function setFullscreenLock(isVisible) {
        fullscreenLock?.classList.toggle('hidden', !isVisible);
        fullscreenLock?.classList.toggle('flex', isVisible);
    }

    function onFullscreenChange() {
        const fullscreenIsActive = isFullscreen();
        fullscreenButton?.classList.toggle('hidden', fullscreenIsActive);

        if (fullscreenIsActive) {
            hasEnteredFullscreen = true;
            ignoreVisibilityUntil = Date.now() + 500;
            setFullscreenLock(false);
        } else if (hasEnteredFullscreen) {
            hasEnteredFullscreen = false;
            setFullscreenLock(true);
            recordViolation('Anda keluar dari mode layar penuh. Aktivitas ini dicatat.');
            requestFullscreen().catch(() => setFullscreenLock(true));
        }
    }

    document.addEventListener('fullscreenchange', onFullscreenChange);
    document.addEventListener('webkitfullscreenchange', onFullscreenChange);
    document.addEventListener('mozfullscreenchange', onFullscreenChange);
    fullscreenButton?.classList.toggle('hidden', isFullscreen());
    setFullscreenLock(!isFullscreen());

    function enterFullscreenFromClick() {
        ignoreVisibilityUntil = Date.now() + 500;
        requestFullscreen().catch(() => {
            setFullscreenLock(true);
            showWarning('Mode layar penuh tidak dapat dibuka. Klik tombol layar penuh sekali lagi dan izinkan browser menampilkan halaman ini dalam layar penuh.');
        });
    }

    fullscreenButton?.addEventListener('click', enterFullscreenFromClick);
    fullscreenResume?.addEventListener('click', enterFullscreenFromClick);

    // ── Visibility / Tab switch ────────────────────────────────────────────────
    document.addEventListener('visibilitychange', () => {
        if (document.hidden && Date.now() > ignoreVisibilityUntil) {
            recordViolation('Peringatan: Anda berpindah tab atau meminimalkan jendela. Aktivitas ini dicatat.');
        }
    });

    window.addEventListener('blur', () => {
        if (Date.now() > ignoreVisibilityUntil) {
            recordViolation('Peringatan: jendela ujian kehilangan fokus. Aktivitas ini dicatat.');
        }
    });

    // ── Window blur ────────────────────────────────────────────────────────────
    // ── Keyboard blocking ──────────────────────────────────────────────────────
    document.addEventListener('keydown', (e) => {
        // Block F12, Ctrl+Shift+I/J/C/U, Alt+F4, PrintScreen
        if (
            e.key === 'F12' ||
            (e.ctrlKey && e.shiftKey && ['I', 'J', 'C', 'U'].includes(e.key.toUpperCase())) ||
            (e.altKey && e.key === 'F4') ||
            e.key === 'PrintScreen'
        ) {
            e.preventDefault();
            e.stopPropagation();
            return false;
        }
        // Block Alt+Tab (best-effort; browser may not prevent OS shortcut)
        if (e.altKey && e.key === 'Tab') {
            e.preventDefault();
        }
        // Block Ctrl+Tab
        if (e.ctrlKey && e.key === 'Tab') {
            e.preventDefault();
        }
    }, true);

    // ── Right-click disable ────────────────────────────────────────────────────
    document.addEventListener('contextmenu', (e) => e.preventDefault());
    }

    // ── Violation handler ──────────────────────────────────────────────────────
    function recordViolation(message) {
        if (isSubmitting) return;

        const now = Date.now();
        if (now - lastViolationAt < 1000) return;
        lastViolationAt = now;

        violations++;
        updateViolationUI();
        showWarning(message);

        // Send to backend via Ajax
        if (logUrl) {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            fetch(logUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken || '',
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ reason: message })
            }).then(async (response) => {
                const result = await response.json();
                if (Number.isInteger(result.violation_count)) {
                    violations = Math.max(violations, result.violation_count);
                    updateViolationUI();
                }
                if (result.auto_submitted && result.result_url) {
                    isSubmitting = true;
                    window.location.assign(result.result_url);
                } else if (response.status === 409 && result.result_url) {
                    isSubmitting = true;
                    window.location.assign(result.result_url);
                }
            }).catch(() => {});
        }

        if (violations >= MAX_VIOLATIONS) {
            isSubmitting = true;
            showWarning(`Batas pelanggaran tercapai (${violations}/${MAX_VIOLATIONS}). Ujian sedang dihentikan oleh sistem.`);
        }
    }

    function updateViolationUI() {
        if (violationCounter) {
            violationCounter.textContent = violations;
            if (violations >= MAX_VIOLATIONS - 1) {
                violationCounter.closest('[data-violation-badge]')?.classList.add('text-rose-700', 'bg-rose-100');
                violationCounter.closest('[data-violation-badge]')?.classList.remove('text-amber-700', 'bg-amber-100');
            }
        }
    }

    function showWarning(message) {
        if (!warningOverlay) return;
        if (warningMessage) warningMessage.textContent = message;
        warningOverlay.classList.remove('hidden');
        warningOverlay.classList.add('flex');
    }

    if (warningDismiss) {
        warningDismiss.addEventListener('click', () => {
            warningOverlay.classList.add('hidden');
            warningOverlay.classList.remove('flex');
        });
    }

    // ── Question navigation ────────────────────────────────────────────────────
    let currentQuestion = 0;

    function showQuestion(index) {
        currentQuestion = index;
        questionCards.forEach((card, i) => {
            card.classList.toggle('hidden', i !== index);
        });
        navItems.forEach((item, i) => {
            item.dataset.current = i === index ? 'true' : 'false';
            updateNavItemStyle(item, i);
        });
        // Scroll nav item into view
        navItems[index]?.scrollIntoView({ block: 'nearest' });
    }

    function updateNavItemStyle(item, index) {
        const isCurrent = parseInt(item.dataset.navQuestion) === currentQuestion;
        const isAnswered = answeredQuestions.has(index);
        const isFlagged = flaggedQuestions.has(index);

        item.classList.remove(
            'bg-slate-200', 'text-slate-700',
            'bg-green-500', 'text-white',
            'bg-amber-400', 'text-white',
            'ring-2', 'ring-indigo-500',
        );

        if (isFlagged) {
            item.classList.add('bg-amber-400', 'text-white');
        } else if (isAnswered) {
            item.classList.add('bg-green-500', 'text-white');
        } else {
            item.classList.add('bg-slate-200', 'text-slate-700');
        }

        if (isCurrent) {
            item.classList.add('ring-2', 'ring-indigo-500');
        }
    }

    navItems.forEach((item) => {
        item.addEventListener('click', () => {
            showQuestion(parseInt(item.dataset.navQuestion));
        });
    });

    // ── Answer tracking + auto-save ────────────────────────────────────────────
    const SESSION_KEY = `exam_answers_${window.location.pathname}`;
    const autosaveUrl = root.dataset.autosaveUrl;
    const pendingAnswers = new Map();
    let autosaveTimer = null;
    let autosaveQueue = Promise.resolve();
    let allowNativeSubmit = false;
    let isFinalizingSubmission = false;
    const elapsedInput = examForm.querySelector('[data-exam-elapsed]');
    const startedAtTimestamp = Number(root.dataset.startedAtTimestamp);

    function syncElapsedSeconds() {
        if (!elapsedInput || !Number.isFinite(startedAtTimestamp)) return;

        elapsedInput.value = String(Math.max(0, Math.floor(Date.now() / 1000 - startedAtTimestamp)));
    }

    function updateAnswerSaveStatus(message, isError = false) {
        if (!answerSaveStatus) return;

        answerSaveStatus.textContent = message;
        answerSaveStatus.classList.toggle('text-rose-700', isError);
        answerSaveStatus.classList.toggle('text-slate-500', !isError);
    }

    function restoreAnswers() {
        try {
            const saved = JSON.parse(sessionStorage.getItem(SESSION_KEY) || '{}');
            Object.entries(saved).forEach(([name, value]) => {
                const inputs = [...examForm.querySelectorAll('[name]')].filter((input) => input.name === name);
                const firstInput = inputs[0];
                if (firstInput?.type === 'radio') {
                    const savedRadio = inputs.some((input) => input.checked)
                        ? null
                        : inputs.find((input) => input.value === value);
                    if (savedRadio) savedRadio.checked = true;
                } else if (firstInput && firstInput.value === '') {
                    firstInput.value = value;
                }
            });
        } catch (_) { /* ignore */ }

        questionCards.forEach((card, index) => {
            const hasSelectedOption = card.querySelector('input[type="radio"]:checked') !== null;
            const hasEssayAnswer = (card.querySelector('textarea')?.value.trim() ?? '') !== '';
            if (hasSelectedOption || hasEssayAnswer) answeredQuestions.add(index);
        });

        navItems.forEach((item, i) => updateNavItemStyle(item, i));
    }

    function saveAnswer(input) {
        try {
            const saved = JSON.parse(sessionStorage.getItem(SESSION_KEY) || '{}');
            saved[input.name] = input.value;
            sessionStorage.setItem(SESSION_KEY, JSON.stringify(saved));
        } catch (_) { /* ignore */ }
    }

    function trackAnswer(input) {
        const card = input.closest('[data-question-card]');
        if (!card) return;

        saveAnswer(input);
        const index = parseInt(card.dataset.questionCard, 10);
        const isAnswered = input.type === 'radio'
            ? card.querySelector('input[type="radio"]:checked') !== null
            : input.value.trim() !== '';

        if (isAnswered) {
            answeredQuestions.add(index);
        } else {
            answeredQuestions.delete(index);
        }
        navItems.forEach((item, itemIndex) => updateNavItemStyle(item, itemIndex));

        const questionId = input.name.match(/^answers\[(\d+)\]$/)?.[1];
        if (questionId) pendingAnswers.set(questionId, input.value);
        updateAnswerSaveStatus('Menyimpan jawaban…');
        window.clearTimeout(autosaveTimer);
        autosaveTimer = window.setTimeout(flushPendingAnswers, 700);
    }

    async function flushPendingAnswers() {
        window.clearTimeout(autosaveTimer);
        autosaveTimer = null;

        if (pendingAnswers.size === 0) {
            await autosaveQueue;

            return;
        }

        const answersToSave = [...pendingAnswers.entries()];
        pendingAnswers.clear();
        const saveAnswers = async () => {
            for (const [questionId, answer] of answersToSave) {
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                const response = await fetch(autosaveUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken || '',
                        Accept: 'application/json',
                    },
                    body: JSON.stringify({ question_id: questionId, answer }),
                    credentials: 'same-origin',
                    keepalive: true,
                });
                const result = await response.json();

                if (result.result_url) window.location.assign(result.result_url);
                if (!response.ok) throw new Error(result.message || 'Jawaban belum tersimpan.');
            }
        };

        autosaveQueue = autosaveQueue.then(saveAnswers, saveAnswers).then(() => {
            if (pendingAnswers.size === 0) updateAnswerSaveStatus('Jawaban tersimpan');
        }).catch(() => {
            answersToSave.forEach(([questionId, answer]) => {
                if (!pendingAnswers.has(questionId)) pendingAnswers.set(questionId, answer);
            });
            updateAnswerSaveStatus('Jawaban belum tersimpan. Periksa koneksi internet.', true);
        });
        await autosaveQueue;
    }

    window.addEventListener('online', () => {
        if (pendingAnswers.size > 0) flushPendingAnswers();
    });
    window.addEventListener('pagehide', () => {
        if (pendingAnswers.size > 0) flushPendingAnswers();
    });

    examForm.addEventListener('change', (event) => {
        if (event.target.matches('input[type="radio"]')) trackAnswer(event.target);
    });
    examForm.addEventListener('input', (event) => {
        if (event.target.matches('textarea')) trackAnswer(event.target);
    });
    examForm.addEventListener('submit', async (event) => {
        if (allowNativeSubmit) return;

        event.preventDefault();
        if (isFinalizingSubmission) return;

        isFinalizingSubmission = true;
        isSubmitting = true;
        syncElapsedSeconds();
        await flushPendingAnswers();
        syncElapsedSeconds();
        allowNativeSubmit = true;
        HTMLFormElement.prototype.submit.call(examForm);
    });

    restoreAnswers();
    showQuestion(0);

    // ── Flag / Unflag ──────────────────────────────────────────────────────────
    document.querySelectorAll('[data-flag-question]').forEach((btn) => {
        btn.addEventListener('click', () => {
            const index = parseInt(btn.dataset.flagQuestion);
            if (flaggedQuestions.has(index)) {
                flaggedQuestions.delete(index);
                btn.textContent = 'Tandai';
                btn.classList.remove('bg-amber-100', 'text-amber-800');
                btn.classList.add('bg-slate-100', 'text-slate-700');
            } else {
                flaggedQuestions.add(index);
                btn.textContent = 'Ditandai ★';
                btn.classList.add('bg-amber-100', 'text-amber-800');
                btn.classList.remove('bg-slate-100', 'text-slate-700');
            }
            navItems.forEach((item, i) => updateNavItemStyle(item, i));
        });
    });

    // ── Previous / Next buttons ────────────────────────────────────────────────
    document.querySelectorAll('[data-prev-question]').forEach((button) => {
        button.addEventListener('click', () => {
            if (currentQuestion > 0) showQuestion(currentQuestion - 1);
        });
    });
    document.querySelectorAll('[data-next-question]').forEach((button) => {
        button.addEventListener('click', () => {
            if (currentQuestion < questionCards.length - 1) showQuestion(currentQuestion + 1);
        });
    });

    // ── Submit with confirmation ───────────────────────────────────────────────
    document.querySelectorAll('[data-submit-btn]').forEach((button) => {
        button.addEventListener('click', (event) => {
            event.preventDefault();
            const unanswered = questionCards.length - answeredQuestions.size;
            if (unanswered > 0 && confirmDialog) {
                if (unansweredCount) unansweredCount.textContent = unanswered;
                confirmDialog.classList.remove('hidden');
                confirmDialog.classList.add('flex');
            } else {
                examForm.requestSubmit();
            }
        });
    });

    confirmProceed?.addEventListener('click', () => {
        examForm.requestSubmit();
    });

    confirmCancel?.addEventListener('click', () => {
        confirmDialog.classList.add('hidden');
        confirmDialog.classList.remove('flex');
    });

    // ── Timer with visual warnings ─────────────────────────────────────────────
    const countdown = document.querySelector('[data-exam-countdown]');
    if (countdown) {
        const output = countdown.querySelector('[data-countdown-output]');
        const progress = countdown.querySelector('[data-countdown-progress]');
        const status = countdown.querySelector('[data-countdown-status]');
        const initialRemainingSeconds = Math.max(0, Number(countdown.dataset.remainingSeconds));
        const totalSeconds = Math.max(1, Number(countdown.dataset.totalSeconds));
        const timerStartedAt = Date.now();
        let hasAutoSubmitted = false;

        function formatTime(secondsLeft) {
            const hours = Math.floor(secondsLeft / 3600);
            const minutes = Math.floor((secondsLeft % 3600) / 60);
            const seconds = secondsLeft % 60;
            const twoDigits = (value) => value.toString().padStart(2, '0');

            return hours > 0
                ? `${twoDigits(hours)}:${twoDigits(minutes)}:${twoDigits(seconds)}`
                : `${twoDigits(Math.floor(secondsLeft / 60))}:${twoDigits(seconds)}`;
        }

        const tick = () => {
            const elapsedSeconds = Math.floor((Date.now() - timerStartedAt) / 1000);
            const secondsLeft = Math.max(0, initialRemainingSeconds - elapsedSeconds);
            syncElapsedSeconds();
            const remainingRatio = Math.min(1, secondsLeft / totalSeconds);
            if (output) output.textContent = formatTime(secondsLeft);
            if (progress) progress.style.width = `${remainingRatio * 100}%`;

            if (secondsLeft <= 60) {
                countdown.classList.add('border-rose-200', 'bg-rose-50', 'text-rose-950');
                countdown.classList.remove('border-amber-200', 'bg-amber-50', 'text-amber-950');
                progress?.classList.add('bg-rose-600');
                progress?.classList.remove('bg-amber-500', 'bg-amber-600');
                if (status) status.textContent = secondsLeft === 0 ? 'Waktu habis, jawaban dikirim…' : 'Kurang dari 1 menit';
            } else if (secondsLeft <= 300) {
                countdown.classList.add('border-amber-300', 'bg-amber-100', 'text-amber-950');
                countdown.classList.remove('border-amber-200', 'bg-amber-50');
                progress?.classList.add('bg-amber-600');
                progress?.classList.remove('bg-amber-500');
                if (status) status.textContent = 'Waktu hampir habis';
            }

            if (secondsLeft === 0 && !hasAutoSubmitted) {
                hasAutoSubmitted = true;
                isSubmitting = true;
                examForm.requestSubmit();
            }
        };

        tick();
        window.setInterval(tick, 250);
    }
}
