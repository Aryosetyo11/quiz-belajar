import { initializeNavigation } from './ui/navigation.js';

document.addEventListener('DOMContentLoaded', () => {
    initializeNavigation();

    // Portal Modal Logic
    const portalModal = document.getElementById('portal-modal');
    const openPortalBtns = document.querySelectorAll('.open-portal-btn');
    const closePortalBtns = document.querySelectorAll('.close-portal-btn');

    const openModal = () => {
        if (portalModal) {
            portalModal.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }
    };

    const closeModal = () => {
        if (portalModal) {
            portalModal.classList.add('hidden');
            document.body.style.overflow = '';
        }
    };

    openPortalBtns.forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            openModal();
        });
    });

    closePortalBtns.forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            closeModal();
        });
    });

    if (portalModal) {
        portalModal.addEventListener('click', (e) => {
            if (e.target === portalModal) {
                closeModal();
            }
        });

        window.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && !portalModal.classList.contains('hidden')) {
                closeModal();
            }
        });
    }
});
