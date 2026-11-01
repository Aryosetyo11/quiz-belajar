import { initializeExamProctoring } from './exam-proctoring.js';

export function initializeExamStart() {
    const form = document.querySelector('[data-start-exam-form]');
    if (!form) return;

    const fullscreenError = document.querySelector('[data-fullscreen-error]');
    const submitButton = form.querySelector('button[type="submit"]');
    let isStarting = false;

    form.addEventListener('submit', async (event) => {
        event.preventDefault();

        if (isStarting) return;

        const requestFullscreen = document.documentElement.requestFullscreen
            ? document.documentElement.requestFullscreen.bind(document.documentElement)
            : document.documentElement.webkitRequestFullscreen?.bind(document.documentElement);

        if (!requestFullscreen) {
            fullscreenError?.classList.remove('hidden');
            return;
        }

        try {
            await requestFullscreen();
        } catch {
            fullscreenError?.classList.remove('hidden');
            return;
        }

        isStarting = true;
        if (submitButton) {
            submitButton.disabled = true;
            submitButton.textContent = 'Mempersiapkan ujian…';
        }

        try {
            const response = await fetch(form.action, {
                method: 'POST',
                body: new FormData(form),
                headers: {
                    Accept: 'text/html',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                credentials: 'same-origin',
            });
            const responseHtml = await response.text();
            const nextDocument = new DOMParser().parseFromString(responseHtml, 'text/html');
            const nextMain = nextDocument.querySelector('main');
            const currentMain = document.querySelector('main');

            if (!response.ok || !nextMain || !currentMain) {
                throw new Error('Halaman ujian tidak dapat dimuat.');
            }

            currentMain.innerHTML = nextMain.innerHTML;
            document.title = nextDocument.title;
            window.history.replaceState(null, '', response.url);

            if (currentMain.querySelector('[data-exam-submit]')) {
                document.body.classList.add('exam-active');
                initializeExamProctoring();

                return;
            }

            document.body.classList.remove('exam-active');
            if (document.fullscreenElement) {
                await document.exitFullscreen();
            }
        } catch {
            isStarting = false;
            if (submitButton) {
                submitButton.disabled = false;
                submitButton.textContent = 'Mulai mengerjakan';
            }
            if (document.fullscreenElement) {
                await document.exitFullscreen().catch(() => {});
            }
            if (fullscreenError) {
                fullscreenError.textContent = 'Ujian belum dapat dimuat. Muat ulang halaman; sesi yang sudah dimulai akan dilanjutkan.';
                fullscreenError.classList.remove('hidden');
            }
        }
    });
}
