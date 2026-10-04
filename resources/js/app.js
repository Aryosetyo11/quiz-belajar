import { initializeNavigation } from './ui/navigation.js';
import { initializePortalModal } from './ui/portal-modal.js';

document.addEventListener('DOMContentLoaded', () => {
    initializeNavigation();
    initializePortalModal();
});
