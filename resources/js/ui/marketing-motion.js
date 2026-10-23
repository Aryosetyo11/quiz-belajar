export function initializeMarketingMotion() {
    const revealItems = document.querySelectorAll('[data-reveal]');
    document.querySelectorAll('[data-demo-answer-group]').forEach((group) => {
        const options = group.querySelectorAll('[data-demo-answer]');
        const status = group.querySelector('[data-demo-answer-status]');

        options.forEach((option) => {
            option.addEventListener('click', () => {
                options.forEach((item) => {
                    const isSelected = item === option;

                    item.classList.toggle('is-selected', isSelected);
                    item.setAttribute('aria-pressed', String(isSelected));
                });

                if (status) {
                    const previewResult = document.querySelector('[data-demo-preview-result]');
                    const selectedAnswer = option.dataset.demoAnswer;
                    const isCorrect = selectedAnswer === 'A';
                    status.textContent = isCorrect
                        ? 'Jawaban benar. Nilai x adalah 3.'
                        : 'Jawaban belum tepat. Nilai x adalah 3.';
                    if (previewResult) {
                        previewResult.textContent = isCorrect
                            ? 'Tepat! 3x + 2 = 11, jadi x = 3.'
                            : 'Coba lagi: kurangi 2, lalu bagi dengan 3.';
                        previewResult.classList.toggle('text-emerald-700', isCorrect);
                        previewResult.classList.toggle('text-amber-800', !isCorrect);
                    }
                }
            });
        });
    });

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
