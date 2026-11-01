{{-- Reusable Global Feedback & Confirmation Modal System for Portal --}}

{{-- 1. Status / Flash Notification Modal (Center of Screen) --}}
@if (session('status') || $errors->any())
    <div id="global-feedback-modal" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/40 p-4 backdrop-blur-xs transition-opacity duration-200" role="dialog" aria-modal="true" aria-labelledby="feedback-modal-title">
        <div class="relative w-full max-w-md rounded-2xl border border-slate-200 bg-white p-6 shadow-2xl transition-all scale-100 animate-in fade-in zoom-in-95">
            <div class="flex flex-col items-center text-center">
                @if (session('status'))
                    <span class="flex h-14 w-14 items-center justify-center rounded-2xl bg-emerald-50 text-emerald-600 ring-8 ring-emerald-50/50">
                        <svg class="h-7 w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                    </span>
                    <h3 id="feedback-modal-title" class="mt-4 text-lg font-bold text-slate-900">Berhasil!</h3>
                    <p class="mt-2 text-xs sm:text-sm text-slate-600 leading-relaxed">{{ session('status') }}</p>
                @else
                    <span class="flex h-14 w-14 items-center justify-center rounded-2xl bg-rose-50 text-rose-600 ring-8 ring-rose-50/50">
                        <svg class="h-7 w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/></svg>
                    </span>
                    <h3 id="feedback-modal-title" class="mt-4 text-lg font-bold text-slate-900">Perhatian</h3>
                    <div class="mt-2 text-xs sm:text-sm text-slate-600 leading-relaxed text-left max-h-48 overflow-y-auto w-full">
                        <ul class="list-disc list-inside space-y-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="mt-6 w-full">
                    <button type="button" onclick="closeFeedbackModal()" class="w-full rounded-xl bg-slate-900 py-2.5 text-xs font-semibold text-white shadow-xs transition hover:bg-slate-800 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-900">
                        Mengerti
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        function closeFeedbackModal() {
            const modal = document.getElementById('global-feedback-modal');
            if (modal) {
                modal.classList.add('opacity-0', 'pointer-events-none');
                setTimeout(() => modal.remove(), 200);
            }
        }

        // Auto close feedback after 4.5 seconds if it's a success status
        @if (session('status'))
            setTimeout(() => {
                closeFeedbackModal();
            }, 4500);
        @endif
    </script>
@endif

{{-- 2. Universal Action Confirmation Modal (Replaces browser confirm) --}}
<div id="global-confirm-modal" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/40 p-4 backdrop-blur-xs hidden transition-opacity duration-200" role="dialog" aria-modal="true" aria-labelledby="confirm-modal-title">
    <div class="relative w-full max-w-md rounded-2xl border border-slate-200 bg-white p-6 shadow-2xl transition-all">
        <div class="flex items-start gap-4">
            <span id="confirm-modal-icon-wrap" class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-rose-50 text-rose-600 ring-4 ring-rose-50/50">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/></svg>
            </span>
            <div class="min-w-0 flex-1">
                <h3 id="confirm-modal-title" class="text-base font-bold text-slate-900">Konfirmasi Aksi</h3>
                <p id="confirm-modal-message" class="mt-1 text-xs text-slate-500 leading-relaxed">Apakah Anda yakin ingin melanjutkan tindakan ini?</p>
            </div>
        </div>

        <div class="mt-6 flex items-center justify-end gap-2.5 border-t border-slate-100 pt-4">
            <button type="button" onclick="closeConfirmModal()" class="rounded-xl border border-slate-200 px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-100 transition">
                Batal
            </button>
            <button type="button" id="confirm-modal-action-btn" class="rounded-xl bg-rose-600 px-4 py-2 text-xs font-semibold text-white shadow-xs hover:bg-rose-700 transition">
                Ya, Lanjutkan
            </button>
        </div>
    </div>
</div>

<script>
    let activeConfirmCallback = null;

    function openConfirmModal(options) {
        const modal = document.getElementById('global-confirm-modal');
        const titleEl = document.getElementById('confirm-modal-title');
        const messageEl = document.getElementById('confirm-modal-message');
        const actionBtn = document.getElementById('confirm-modal-action-btn');

        if (!modal) return;

        titleEl.textContent = options.title || 'Konfirmasi Tindakan';
        messageEl.textContent = options.message || 'Apakah Anda yakin ingin melanjutkan tindakan ini?';
        actionBtn.textContent = options.confirmText || 'Ya, Lanjutkan';

        if (options.variant === 'danger') {
            actionBtn.className = 'rounded-xl bg-rose-600 px-4 py-2 text-xs font-semibold text-white shadow-xs hover:bg-rose-700 transition';
        } else {
            actionBtn.className = 'rounded-xl bg-indigo-600 px-4 py-2 text-xs font-semibold text-white shadow-xs hover:bg-indigo-700 transition';
        }

        activeConfirmCallback = options.onConfirm || null;
        modal.classList.remove('hidden');
    }

    function closeConfirmModal() {
        const modal = document.getElementById('global-confirm-modal');
        if (modal) {
            modal.classList.add('hidden');
        }
        activeConfirmCallback = null;
    }

    document.getElementById('confirm-modal-action-btn')?.addEventListener('click', function() {
        if (typeof activeConfirmCallback === 'function') {
            activeConfirmCallback();
        }
        closeConfirmModal();
    });

    // Universal interceptor for forms with data-confirm
    document.addEventListener('submit', function(e) {
        const form = e.target;
        const confirmMsg = form.getAttribute('data-confirm');

        if (confirmMsg && !form.dataset.confirmed) {
            e.preventDefault();
            openConfirmModal({
                title: form.getAttribute('data-confirm-title') || 'Konfirmasi',
                message: confirmMsg,
                confirmText: form.getAttribute('data-confirm-btn') || 'Ya, Lanjutkan',
                variant: form.getAttribute('data-confirm-variant') || 'danger',
                onConfirm: function() {
                    form.dataset.confirmed = 'true';
                    form.submit();
                }
            });
        }
    });
</script>
