export function initializeMarketingMotion() {
    initializePortalReveals();
    initializeQuizDemo();
    initializeRoleFilters();

    const revealItems = document.querySelectorAll('[data-reveal]');
    const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    if (prefersReducedMotion || !('IntersectionObserver' in window)) {
        return;
    }

    document.documentElement.classList.add('motion-ready');

    revealItems.forEach((item, index) => {
        item.style.setProperty('--reveal-delay', `${Math.min((index % 4) * 90, 270)}ms`);
    });

    const observer = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
            entry.target.classList.toggle('is-visible', entry.isIntersecting);
        });
    }, {
        threshold: 0.12,
        rootMargin: '0px 0px -40px 0px',
    });

    revealItems.forEach((item) => observer.observe(item));
}

function initializeQuizDemo() {
    document.querySelectorAll('[data-demo-answer-group]').forEach((group) => {
        const options = group.querySelectorAll('[data-demo-answer]');
        const feedback = document.querySelector('[data-demo-preview-result]');
        const action = document.querySelector('[data-demo-action]');
        let selectedOption = null;
        let hasCheckedAnswer = false;

        if (!feedback || !action || options.length === 0) {
            return;
        }

        const resetAnswer = () => {
            selectedOption = null;
            hasCheckedAnswer = false;
            options.forEach((option) => {
                option.classList.remove('is-selected', 'is-correct', 'is-wrong');
                option.setAttribute('aria-pressed', 'false');
            });
            feedback.textContent = 'Pilih jawaban, lalu periksa hasilnya.';
            feedback.classList.remove('text-emerald-700', 'text-rose-700');
            feedback.classList.add('text-zinc-600');
            action.disabled = true;
            action.textContent = 'Periksa jawaban';
        };

        options.forEach((option) => {
            option.addEventListener('click', () => {
                selectedOption = option;
                hasCheckedAnswer = false;
                options.forEach((item) => {
                    const isSelected = item === option;

                    item.classList.toggle('is-selected', isSelected);
                    item.classList.remove('is-correct', 'is-wrong');
                    item.setAttribute('aria-pressed', String(isSelected));
                });
                feedback.textContent = 'Jawaban sudah dipilih. Klik “Periksa jawaban” untuk melihat pembahasan.';
                feedback.classList.remove('text-emerald-700', 'text-rose-700');
                feedback.classList.add('text-zinc-600');
                action.disabled = false;
                action.textContent = 'Periksa jawaban';
            });
        });

        action.addEventListener('click', () => {
            if (hasCheckedAnswer) {
                resetAnswer();

                return;
            }

            if (!selectedOption) {
                return;
            }

            hasCheckedAnswer = true;
            const isCorrect = selectedOption.dataset.demoAnswer === 'A';
            const correctOption = group.querySelector('[data-demo-answer="A"]');

            correctOption?.classList.add('is-correct');
            selectedOption.classList.toggle('is-correct', isCorrect);
            selectedOption.classList.toggle('is-wrong', !isCorrect);
            feedback.textContent = isCorrect
                ? 'Tepat! Kurangi 2 dari kedua sisi: 3x = 9, lalu bagi 3. Jadi, x = 3.'
                : 'Belum tepat. Jawaban yang benar adalah 3. Kurangi 2 dari kedua sisi, lalu bagi 3.';
            feedback.classList.toggle('text-emerald-700', isCorrect);
            feedback.classList.toggle('text-rose-700', !isCorrect);
            feedback.classList.remove('text-zinc-600');
            action.textContent = 'Ulangi simulasi';
        });
    });
}

function initializeRoleFilters() {
    document.querySelectorAll('[data-role-filter-group]').forEach((group) => {
        const filters = group.querySelectorAll('[data-role-filter]');
        const section = group.closest('#peran');
        const cards = section?.querySelectorAll('[data-role-card]') ?? [];
        const status = section?.querySelector('[data-role-filter-status]');

        filters.forEach((filterButton) => {
            filterButton.addEventListener('click', () => {
                const selectedFilter = filterButton.dataset.roleFilter;

                filters.forEach((button) => {
                    const isSelected = button === filterButton;

                    button.setAttribute('aria-pressed', String(isSelected));
                    button.classList.toggle('border-indigo-700', isSelected);
                    button.classList.toggle('bg-indigo-700', isSelected);
                    button.classList.toggle('text-white', isSelected);
                    button.classList.toggle('border-zinc-200', !isSelected);
                    button.classList.toggle('bg-white', !isSelected);
                    button.classList.toggle('text-zinc-600', !isSelected);
                });

                cards.forEach((card) => {
                    card.hidden = selectedFilter !== 'all' && card.dataset.roleCard !== selectedFilter;
                });

                if (status) {
                    const selectedLabel = filterButton.textContent.trim();
                    status.textContent = selectedFilter === 'all'
                        ? 'Menampilkan semua peran.'
                        : `Menampilkan peran ${selectedLabel.toLowerCase()}.`;
                }
            });
        });
    });
}

function initializePortalReveals() {
    const portalMain = document.querySelector('[data-portal-motion]');

    if (!portalMain || portalMain.querySelector('[data-exam-proctoring]') || portalMain.querySelector('[data-reveal]')) {
        return;
    }

    let revealScope = portalMain;

    while (
        revealScope.children.length === 1
        && revealScope.firstElementChild instanceof HTMLElement
        && revealScope.firstElementChild.tagName === 'DIV'
        && revealScope.firstElementChild.children.length > 0
    ) {
        revealScope = revealScope.firstElementChild;
    }

    Array.from(revealScope.children)
        .filter((item) => !item.matches('script, style, template, [hidden], [data-skip-reveal]'))
        .forEach((item) => item.setAttribute('data-reveal', ''));
}
