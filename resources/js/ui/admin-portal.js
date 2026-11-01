export function initializeAdminPortal() {
    const materialType = document.querySelector('[data-material-type]');
    const fileWrap = document.querySelector('[data-material-file-wrap]');
    const videoWrap = document.querySelector('[data-material-video-wrap]');
    const linkWrap = document.querySelector('[data-material-link-wrap]');
    const fileInput = document.querySelector('[data-material-file]');
    const videoUrlInput = document.querySelector('[data-material-video-url]');
    const externalUrlInput = document.querySelector('[data-material-external-url]');

    const updateMaterialFields = () => {
        if (!materialType || !fileWrap || !videoWrap || !linkWrap || !fileInput || !videoUrlInput || !externalUrlInput) {
            return;
        }

        const isVideo = materialType.value === 'video';
        const isLink = materialType.value === 'link';
        fileWrap.classList.toggle('hidden', isVideo || isLink);
        videoWrap.classList.toggle('hidden', !isVideo);
        linkWrap.classList.toggle('hidden', !isLink);
        fileInput.required = !isVideo && !isLink;
        videoUrlInput.required = isVideo;
        externalUrlInput.required = isLink;
    };

    materialType?.addEventListener('change', updateMaterialFields);
    updateMaterialFields();

    const roleSelect = document.querySelector('[data-admin-role]');
    const emailWrap = document.querySelector('[data-admin-email-wrap]');
    const nisnWrap = document.querySelector('[data-admin-nisn-wrap]');
    const schoolWrap = document.querySelector('[data-admin-school-wrap]');
    const emailInput = document.querySelector('[data-admin-email]');
    const nisnInput = document.querySelector('[data-admin-nisn]');
    const schoolInput = document.querySelector('[data-admin-school]');
    const roleDescription = document.querySelector('[data-admin-role-description]');
    const roleDescriptions = {
        guru: 'Guru dapat mengelola mata pelajaran, materi, dan ujian. Login menggunakan email.',
        siswa: 'Siswa dapat membuka kelas, materi, ujian, dan hasil belajar. Login menggunakan NISN 10 digit.',
        admin_remote: 'Manajemen All Remote dapat mengelola akun dan melihat ringkasan portal. Login menggunakan email.',
        admin_yayasan: 'Manajemen Yayasan dapat melihat ringkasan aktivitas institusi. Login menggunakan email.',
    };

    const updateRoleFields = () => {
        if (!roleSelect || !emailWrap || !nisnWrap || !emailInput || !nisnInput) {
            return;
        }

        const isStudent = roleSelect.value === 'siswa';
        const needsSchool = ['guru', 'siswa'].includes(roleSelect.value);
        emailWrap.classList.toggle('hidden', isStudent);
        nisnWrap.classList.toggle('hidden', !isStudent);
        schoolWrap?.classList.toggle('hidden', !needsSchool);
        emailInput.required = !isStudent;
        nisnInput.required = isStudent;
        emailInput.disabled = isStudent;
        nisnInput.disabled = !isStudent;
        if (schoolInput) {
            schoolInput.required = needsSchool;
            schoolInput.disabled = !needsSchool;
        }
        if (roleDescription) {
            roleDescription.textContent = roleDescriptions[roleSelect.value] || roleDescriptions.guru;
        }
    };

    roleSelect?.addEventListener('change', updateRoleFields);
    updateRoleFields();

    document.querySelectorAll('[data-password-toggle]').forEach((button) => {
        const passwordInput = document.getElementById(button.dataset.passwordToggle);
        if (!passwordInput) {
            return;
        }

        button.addEventListener('click', () => {
            const isVisible = passwordInput.type === 'password';
            passwordInput.type = isVisible ? 'text' : 'password';
            button.textContent = isVisible ? 'Sembunyikan' : 'Tampilkan';
            button.setAttribute('aria-pressed', String(isVisible));
        });
    });

    const newPassword = document.querySelector('[data-new-password]');
    const passwordConfirmation = document.querySelector('[data-password-confirmation]');
    const passwordMatchStatus = document.querySelector('[data-password-match-status]');
    const passwordRequirements = {
        length: (value) => value.length >= 12,
        uppercase: (value) => /[A-Z]/.test(value),
        lowercase: (value) => /[a-z]/.test(value),
        number: (value) => /[0-9]/.test(value),
    };

    const updatePasswordFeedback = () => {
        const value = newPassword?.value ?? '';

        Object.entries(passwordRequirements).forEach(([requirement, isMet]) => {
            const item = document.querySelector(`[data-password-requirement="${requirement}"]`);
            const meetsRequirement = isMet(value);
            item?.classList.toggle('text-emerald-800', meetsRequirement);
            item?.classList.toggle('text-slate-600', !meetsRequirement);
            item?.querySelector('[data-requirement-check]')?.classList.toggle('opacity-0', !meetsRequirement);
        });

        if (passwordMatchStatus && passwordConfirmation) {
            const hasConfirmation = passwordConfirmation.value.length > 0;
            const passwordsMatch = hasConfirmation && passwordConfirmation.value === value;
            passwordMatchStatus.textContent = passwordsMatch
                ? 'Password cocok.'
                : hasConfirmation ? 'Password belum cocok.' : 'Ulangi password untuk memastikan keduanya sama.';
            passwordMatchStatus.classList.toggle('text-emerald-800', passwordsMatch);
            passwordMatchStatus.classList.toggle('text-rose-700', hasConfirmation && !passwordsMatch);
            passwordMatchStatus.classList.toggle('text-slate-500', !hasConfirmation);
        }
    };

    newPassword?.addEventListener('input', updatePasswordFeedback);
    passwordConfirmation?.addEventListener('input', updatePasswordFeedback);
    updatePasswordFeedback();

    const chartItems = [...document.querySelectorAll('[data-chart-item]')];
    const donut = document.querySelector('[data-chart-donut]');
    const chartTotal = document.querySelector('[data-chart-total]');
    const chartTotalLabel = document.querySelector('[data-chart-total-label]');
    const chartSummary = document.querySelector('[data-chart-summary]');
    let selectedChartLabel = null;
    let chartMode = 'count';

    const renderChart = () => {
        const visibleItems = selectedChartLabel
            ? chartItems.filter((item) => item.dataset.label === selectedChartLabel)
            : chartItems;
        const visibleTotal = visibleItems.reduce((total, item) => total + Number(item.dataset.count), 0);
        let segmentStart = 0;
        const allAccounts = chartItems.reduce((total, item) => total + Number(item.dataset.count), 0);
        const segments = [];

        chartItems.forEach((item) => {
            const isSelected = item.dataset.label === selectedChartLabel;
            const isDimmed = selectedChartLabel !== null && !isSelected;
            const count = Number(item.dataset.count);
            const share = visibleTotal > 0 && visibleItems.includes(item) ? (count / visibleTotal) * 100 : 0;
            const value = chartMode === 'count' ? count : Number(item.dataset.percentage);
            const bar = item.querySelector('[data-chart-bar]');
            const progress = item.querySelector('[role="progressbar"]');
            const labelButton = item.querySelector('[data-chart-filter]');

            item.classList.toggle('opacity-35', isDimmed);
            labelButton?.setAttribute('aria-pressed', String(isSelected));
            item.querySelector('[data-chart-value]').textContent = chartMode === 'count'
                ? new Intl.NumberFormat('id-ID').format(value)
                : String(value);
            item.querySelector('[data-chart-unit]').textContent = chartMode === 'count' ? 'akun' : '%';
            if (bar) {
                bar.style.width = `${selectedChartLabel ? share : chartMode === 'count' ? (visibleTotal > 0 ? count / visibleTotal * 100 : 0) : Number(item.dataset.percentage)}%`;
            }
            if (progress) {
                progress.setAttribute('aria-valuenow', String(value));
                progress.setAttribute('aria-valuemax', chartMode === 'count' ? String(visibleTotal) : '100');
            }

            if (count > 0 && allAccounts > 0) {
                const end = segmentStart + (count / allAccounts * 100);
                segments.push(`${item.dataset.color} ${segmentStart}% ${end}%`);
                segmentStart = end;
            }
        });

        if (donut) {
            donut.style.background = segments.length ? `conic-gradient(${segments.join(', ')})` : '#e2e8f0';
            donut.setAttribute('aria-label', selectedChartLabel
                ? `Diagram ${selectedChartLabel}: ${visibleTotal} akun`
                : `Diagram semua role: ${visibleTotal} akun`);
        }
        if (chartTotal) {
            chartTotal.textContent = new Intl.NumberFormat('id-ID').format(visibleTotal);
        }
        if (chartTotalLabel) {
            chartTotalLabel.textContent = selectedChartLabel ?? 'total akun';
        }
        if (chartSummary) {
            chartSummary.textContent = selectedChartLabel
                ? `Menampilkan ${selectedChartLabel}: ${visibleTotal} akun.`
                : 'Menampilkan semua role dalam grafik.';
        }
    };

    chartItems.forEach((item) => {
        const selectRole = () => {
            selectedChartLabel = selectedChartLabel === item.dataset.label ? null : item.dataset.label;
            renderChart();
        };

        item.querySelector('[data-chart-filter]')?.addEventListener('click', selectRole);
        item.querySelector('[data-chart-bar]')?.addEventListener('click', selectRole);
    });

    document.querySelector('[data-chart-reset]')?.addEventListener('click', () => {
        selectedChartLabel = null;
        renderChart();
    });

    donut?.addEventListener('click', (event) => {
        const bounds = donut.getBoundingClientRect();
        const x = event.clientX - bounds.left - bounds.width / 2;
        const y = event.clientY - bounds.top - bounds.height / 2;
        const radius = Math.min(bounds.width, bounds.height) * 0.5;
        if (Math.hypot(x, y) < radius * 0.4) {
            return;
        }

        let angle = (Math.atan2(y, x) * 180 / Math.PI + 90 + 360) % 360;
        const total = chartItems.reduce((sum, item) => sum + Number(item.dataset.count), 0);
        for (const item of chartItems) {
            const sweep = total > 0 ? Number(item.dataset.count) / total * 360 : 0;
            if (angle >= 0 && angle < sweep) {
                selectedChartLabel = selectedChartLabel === item.dataset.label ? null : item.dataset.label;
                renderChart();
                break;
            }
            angle -= sweep;
        }
    });

    donut?.addEventListener('click', (event) => {
        if (event.target.closest('[data-chart-total]')) {
            selectedChartLabel = null;
            renderChart();
        }
    });

    document.querySelectorAll('[data-chart-mode]').forEach((button) => {
        button.addEventListener('click', () => {
            chartMode = button.dataset.chartMode;

            document.querySelectorAll('[data-chart-mode]').forEach((item) => {
                const selected = item === button;
                item.setAttribute('aria-pressed', String(selected));
                item.classList.toggle('bg-white', selected);
                item.classList.toggle('shadow-sm', selected);
                item.classList.toggle('text-slate-800', selected);
                item.classList.toggle('text-slate-600', !selected);
            });

            renderChart();
        });
    });

    renderChart();

    document.querySelectorAll('[data-admin-delete-form]').forEach((form) => {
        form.addEventListener('submit', (event) => {
            const accountName = form.dataset.accountName;

            if (!window.confirm(`Hapus akun ${accountName}? Tindakan ini tidak dapat dibatalkan.`)) {
                event.preventDefault();
            }
        });
    });
}
