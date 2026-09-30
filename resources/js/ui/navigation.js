export function initializeNavigation() {
    const mobileMenuBtn = document.getElementById('mobile-menu-btn');
    const mobileMenu = document.getElementById('mobile-menu');

    if (mobileMenuBtn && mobileMenu) {
        mobileMenuBtn.addEventListener('click', () => {
            const isHidden = mobileMenu.classList.toggle('hidden');
            mobileMenuBtn.setAttribute('aria-expanded', String(!isHidden));
        });

        // Close when clicking nav link on mobile
        mobileMenu.querySelectorAll('a').forEach(link => {
            link.addEventListener('click', () => {
                mobileMenu.classList.add('hidden');
                mobileMenuBtn.setAttribute('aria-expanded', 'false');
            });
        });
    }
}
